<?php

namespace App\Helpers;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Upload the file to S3
 * Set aspect ratio to 1:1 by default
 */
class FileUploadHelper
{
    public static function imageUpload($path, $file, $resize = [1024, 1024])
    {
        $manager = new ImageManager(new Driver());

        // Get the uploaded file
        $image    = $file;
        $filePath = $path . $image->hashName();

        $optimizedImage = $manager->read($image)->scale($resize[0], $resize[1]);

        // Upload the avatar to S3
        $path = Storage::disk('s3')->put($filePath, $optimizedImage->toWebp(60));

        // Get the URL of the uploaded avatar
        $fileUrl = Storage::disk('s3')->url($filePath);

        return $fileUrl;
    }
}
