<?php

namespace App\Service\PictureService;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Psr\Log\LoggerInterface;

class PictureService
{
    private ParameterBagInterface $params;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }

    public function add(UploadedFile $picture, ?string $folder = '', ?int $width = 250, ?int $height = 250): string
    {
        try {
            $filename = md5(uniqid(rand(), true)) . '.webp';
            $picturePath = $picture->getPathname();
            $pictureInfo = getimagesize($picturePath);

            if ($pictureInfo === false) {
                throw new Exception('Format d\'image incorrect');
            }

            $pictureSource = $this->createImageFromFile($picturePath, $pictureInfo['mime']);
            $imageWidth = $pictureInfo[0];
            $imageHeight = $pictureInfo[1];

            list($srcX, $srcY, $squareSize) = $this->calculateCropParameters($imageWidth, $imageHeight);

            $resizedPicture = imagecreatetruecolor($width, $height);
            imagecopyresampled($resizedPicture, $pictureSource, 0, 0, $srcX, $srcY, $width, $height, $squareSize, $squareSize);

            $path = $this->params->get('images_directory') . '/' . $folder;
            if (!file_exists($path . '/mini/')) {
                mkdir($path . '/mini/', 0755, true);
            }

            imagewebp($resizedPicture, $path . '/mini/' . $width . 'x' . $height . '-' . $filename);
            $picture->move($path . '/', $filename);

            imagedestroy($pictureSource);
            imagedestroy($resizedPicture);

            return $filename;
        } catch (Exception $e) {
            $this->logger->error('Error adding picture: ' . $e->getMessage());
            throw $e; // Re-throw the exception after logging it
        }
    }

    public function delete(string $filename, ?string $folder = '', ?int $width = 250, ?int $height = 250): bool
    {
        if ($filename === 'default.webp') {
            return false;
        }

        $success = false;
        $path = $this->params->get('images_directory') . '/' . $folder;
        $miniPath = $path . '/mini/' . $width . 'x' . $height . '-' . $filename;
        $originalPath = $path . '/' . $filename;

        if (file_exists($miniPath)) {
            unlink($miniPath);
            $success = true;
        }

        if (file_exists($originalPath)) {
            unlink($originalPath);
            $success = true;
        }

        return $success;
    }

    private function createImageFromFile(string $path, string $mime)
    {
        switch ($mime) {
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/jpeg':
                return imagecreatefromjpeg($path);
            case 'image/webp':
                return imagecreatefromwebp($path);
            default:
                throw new Exception('Format d\'image incorrect');
        }
    }

    private function calculateCropParameters(int $imageWidth, int $imageHeight): array
    {
        switch ($imageWidth <=> $imageHeight) {
            case -1:
                $squareSize = $imageWidth;
                return [0, ($imageHeight - $squareSize) / 2, $squareSize];
            case 0:
                $squareSize = $imageWidth;
                return [0, 0, $squareSize];
            case 1:
                $squareSize = $imageWidth;
                return [($imageWidth - $squareSize) / 2, 0, $squareSize];
        }

        throw new Exception('Erreur de calcul des paramètres de recadrage');
    }
}
