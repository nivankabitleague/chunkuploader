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
        'handle'
    );

    private static $tmp_folder = 'tmp-chunk-uploads';

    public function handle()
    {
        $this->initTempFolders();

        $config = new \Flow\Config();
        $config->setTempDir($this->getTempFolderPath());
        $request = new \Flow\Request();
        $file = new \Flow\File($config);

        $response = new SS_HTTPResponse();

        echo '1';
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($file->checkChunk()) {
                $response->setStatusCode('204');
            } else {
                $response->setStatusCode('204');
                return $response;
            }
        } else {
            if ($file->validateChunk()) {
                echo '2';
                $file->saveChunk();
            } else {
                $response->setStatusCode('400');
                return $response;
            }

        }

        if($file->validateFile()) {
            $flowFileName = $this->request->requestVar('flowFilename');
            $extension = pathinfo($flowFileName, PATHINFO_EXTENSION);

            $nameFilter = FileNameFilter::create();
            $fileName = basename($nameFilter->filter($flowFileName));


            $relativeFilePath = DIRECTORY_SEPARATOR . $fileName . '.' . $extension;

            while(file_exists(ASSETS_PATH . DIRECTORY_SEPARATOR . $relativeFilePath)) {
                $i = isset($i) ? ($i+1) : 2;
                $relativeFilePath = DIRECTORY_SEPARATOR . $fileName . '-' . $i . '.' . $extension;
            }

            if($file->save(ASSETS_PATH . DIRECTORY_SEPARATOR . $relativeFilePath)) {
                $fileClass = File::get_class_for_file_extension(pathinfo($fileName, PATHINFO_EXTENSION));
                $ssFile = new $fileClass(array(
                    'Filename'  => ASSETS_DIR . DIRECTORY_SEPARATOR . $relativeFilePath
                ));
                $ssFile->write();
                $response->setBody(Convert::array2json(array(
                    'FileName'      => $fileName,
                    'Path'          => $ssFile->Filename,
                    'ID'            => $ssFile->ID
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

}
