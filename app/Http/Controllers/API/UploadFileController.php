<?php

namespace App\Http\Controllers\API;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\ImageUploadRequest;

class UploadFileController extends Controller
{
    public function imageUpload(ImageUploadRequest $request)
    {
        $file      = $request->file('file');
        $path      = $request->path;
        $dimension = $request->dimension;

        // Update the user's profile_image column with the URL
        $url = FileUploadHelper::imageUpload($path, $file, $dimension);

        return response()->json(['url' => $url]);
    }

    public function fileUpload(FileUploadRequest $request)
    {
        $file = $request->file('file');
        $path = $request->path;

        // Update the user's profile_image column with the URL
        $url = FileUploadHelper::fileUpload($path, $file);

        return response()->json(['url' => $url]);
    }
}
