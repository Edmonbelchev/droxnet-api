<?php

namespace App\Http\Controllers\API;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;

class UploadFileController extends Controller
{
    public function upload(FileUploadRequest $request)
    {
        $file      = $request->file('file');
        $path      = $request->path;
        $dimension = $request->dimension;

        // Update the user's profile_image column with the URL
        $url = FileUploadHelper::imageUpload($path, $file, $dimension);

        return response()->json(['url' => $url]);
    }
}
