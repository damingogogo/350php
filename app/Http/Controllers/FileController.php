<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    // 上传图片到MinIO
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120', // 最大5MB
        ]);

        $path = $request->file('file')->store('images/' . date('Ym'), 'minio');

        $url = Storage::disk('minio')->url($path);

        return response()->json([
            'message' => '上传成功',
            'data' => [
                'path' => $path,
                'url' => $url,
            ]
        ]);
    }

    // 上传文件到MinIO
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 最大100MB
        ]);

        $path = $request->file('file')->store('files/' . date('Ym'), 'minio');

        $url = Storage::disk('minio')->url($path);

        return response()->json([
            'message' => '上传成功',
            'data' => [
                'path' => $path,
                'url' => $url,
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_size' => $request->file('file')->getSize(),
            ]
        ]);
    }
}
