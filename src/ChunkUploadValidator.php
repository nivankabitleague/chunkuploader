<?php

/**
 * Created by PhpStorm.
 * User: Nivanka Fonseka
 * Date: 08/10/2017
 * Time: 05:23
 */
class ChunkUploadValidator extends Upload_Validator
{

    protected $chunkUpload;

    public function setChunkUpload($chunkUpload)
    {
        $this->chunkUpload = $chunkUpload;
        return $this;
    }

    public function isValidSize() {
        $pathInfo = pathinfo($this->chunkUpload['Filename']);
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : null;
        $maxSize = $this->getAllowedMaxFileSize($extension);
        return (!$this->chunkUpload['TotalSize'] || !$maxSize || (int) $this->chunkUpload['TotalSize'] < $maxSize);
    }

    public function isValidExtension() {
        $pathInfo = pathinfo($this->chunkUpload['Filename']);

        // Special case for filenames without an extension
        if(!isset($pathInfo['extension'])) {
            return in_array('', $this->allowedExtensions, true);
        } else {
            return (!count($this->allowedExtensions)
                || in_array(strtolower($pathInfo['extension']), $this->allowedExtensions));
        }
    }


    public function validate() {

        $pathInfo = pathinfo($this->chunkUpload['Filename']);
        // filesize validation
        if(!$this->isValidSize()) {
            $ext = (isset($pathInfo['extension'])) ? $pathInfo['extension'] : '';
            $arg = File::format_size($this->getAllowedMaxFileSize($ext));
            $this->errors[] = _t(
                'File.TOOLARGE',
                'File size is too large, maximum {size} allowed',
                'Argument 1: File size (e.g. 1MB)',
                array('size' => $arg)
            );
            return false;
        }

        // extension validation
        if(!$this->isValidExtension()) {
            $this->errors[] = _t(
                'File.INVALIDEXTENSION',
                'Extension is not allowed (valid: {extensions})',
                'Argument 1: Comma-separated list of valid extensions',
                array('extensions' => wordwrap(implode(', ', $this->allowedExtensions)))
            );
            return false;
        }

        return true;
    }


}