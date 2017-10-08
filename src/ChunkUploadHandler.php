<?php

/**
 * Created by PhpStorm.
 * User: Nivanka Fonseka
 * Date: 06/10/2017
 * Time: 20:59
 */
class ChunkUploadHandler extends Controller
{
    
    private static $allowed_actions = array(
        'handle',
        'delete'
    );

    private static $tmp_folder = 'tmp-chunk-uploads';

    protected $errors = array();
    
    
    public function handle()
    {
        $this->initTempFolders();

        $response = new SS_HTTPResponse();
        $config = new \Flow\Config();
        $config->setTempDir($this->getTempFolderPath());
        $request = new \Flow\Request();
        $file = new \Flow\File($config);
        $flowFileName = $this->request->requestVar('flowFilename');
        $validator = Injector::inst()->create('ChunkUploadValidator');


        $validator->setChunkUpload(array(
            'ChunkNumber'       => $this->request->requestVar('flowChunkNumber'),
            'TotalChunks'       => $this->request->requestVar('flowTotalChunks'),
            'ChunkSize'         => $this->request->requestVar('flowChunkSize'),
            'TotalSize'         => $this->request->requestVar('flowTotalSize'),
            'Filename'          => $this->request->requestVar('flowFilename'),
            'RelativePath'      => $this->request->requestVar('flowRelativePath')
        ));

        $validator->setAllowedExtensions(
            array_filter(Config::inst()->get('File', 'allowed_extensions'))
        );

        $validator->validate();
        if($validator->getErrors()) {
            $this->errors = array_merge($this->errors, $validator->getErrors());
            $response->setStatusCode('501');
            $response->setBody('<ul><li>' . implode('</li><li>', $this->errors) . '</li></ul>');
            return $response;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($file->checkChunk()) {
                $response->setStatusCode('204');
            } else {
                $response->setStatusCode('204');
                return $response;
            }
        } else {
            if ($file->validateChunk()) {
                $file->saveChunk();
            } else {
                $response->setStatusCode('400');
                return $response;
            }

        }

        if($file->validateFile()) {

            $separator = '/';
            $nameFilter = FileNameFilter::create();
            $extension = pathinfo($flowFileName, PATHINFO_EXTENSION);
            $fileName = pathinfo($flowFileName, PATHINFO_FILENAME);

            $relativeFilePath = $fileName . '.' . $extension;

            while(file_exists(ASSETS_PATH . $separator . $relativeFilePath)) {
                $i = isset($i) ? ($i+1) : 2;
                $relativeFilePath = $fileName . '-' . $i . '.' . $extension;
            }


            if($file->save(ASSETS_PATH . $separator . $relativeFilePath)) {
                $fileClass = File::get_class_for_file_extension(pathinfo($fileName, PATHINFO_EXTENSION));
                $ssFile = new $fileClass(array(
                    'Title'     => $fileName,
                    'Name'      => basename($relativeFilePath)
                ));
                $ssFile->write();
                $ssFile->onAfterUpload();

                $hash = md5($ssFile->ID . '/' . $ssFile->Created);

                $response->setBody(Convert::array2json(array(
                    'FileName'      => $fileName,
                    'Path'          => $ssFile->Filename,
                    'ID'            => $ssFile->ID,
                    'Link'          => $ssFile->Link(),
                    'Hash'          => $hash,
                    'DeleteLink'    => Director::baseURL() . 'chunkupload/delete/' . $ssFile->ID . '/' . $hash
                )));
                return $response;
            }



        }

        $response->setBody(Convert::array2json(array(
            'More'          => 1
        )));
        return $response;
    }

    public function initTempFolders()
    {
        $path = $this->getTempFolderPath();
        if(!file_exists($path)) {
            Filesystem::makeFolder($path);
        }
    }

    public function getTempFolderPath()
    {
        return TEMP_FOLDER . DIRECTORY_SEPARATOR . Config::inst()->get('ChunkUploadHandler', 'tmp_folder');
    }

    public function delete()
    {
        if($file = File::get()->byID($this->request->param('ID'))){
            $hash = md5($file->ID . '/' . $file->Created);
            if($hash == $this->request->param('OtherID')) {
                $file->delete();
            }
        }
        return 'OK';
    }

}
