<?php
namespace Bibliotek\Utility;

use Laminas\Diactoros\UploadedFile;

class Assets {
    private const BASE_PATH_ASSETS = "dist/";
    protected $uploadDirectory;

    public function __construct($uploadDirectory) {
        $this->uploadDirectory = rtrim($uploadDirectory, '/') . '/';
    }

    public function getRelativeUrl($fileName) {
        return '/' . self::BASE_PATH_ASSETS . $this->uploadDirectory . $fileName;
    }

    public function uploadFile(UploadedFile $file, $allowedExtensions = ['jpg', 'png', 'gif']) {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Failed to upload file.');
        }

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $ext = array_search(
            $fileInfo->file($file->getStream()->getMetadata('uri')),
            array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ),
            true
        );

        if (false === $ext || !in_array($ext, $allowedExtensions)) {
            throw new \RuntimeException('Invalid file format.');
        }

        $fileName = sprintf('%s.%s', sha1_file($file->getStream()->getMetadata('uri')), $ext);

        $file->moveTo(self::BASE_PATH_ASSETS . $this->uploadDirectory . $fileName);

        return $fileName;
    }

    public function deleteFile($fileName) {
        $filePath = substr($fileName, 1);

        if (!file_exists($filePath)) {
            throw new \RuntimeException('File not found.');
        }

        if (!unlink($filePath)) {
            throw new \RuntimeException('Failed to delete file.');
        }

        return true;
    }
}
