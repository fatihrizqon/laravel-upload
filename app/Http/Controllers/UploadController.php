<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TemporaryFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        # Validation Required #
        # Document: .docx, .xlsx, .pdf => pdf #
        # Image: .png, .jpg, .jpeg => jpeg #
        # Video: .avi, .mov, .mp4 => mp4 #

        /**
         * 1. Klasifikasikan MimeType berdasarkan jenis arsip.
         * 2. Pisahkan pada metode penyimpanan berdasarkan jenis media, tekstual sendiri, foto sendiri, video sendiri.
         * 3. Konversikan tiap media dengan format standar: .pdf untuk tekstual, .jpeg untuk foto, .mp4 untuk video.
         *    -> cari referensi FFMpeg untuk mengkonversikannya
         */
        try {
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $folder = uniqid() . '-' . now()->timestamp;
            if($ext == 'avi' || $ext ==  'mov' || $ext ==  'mp4'){
                $filename = 'video' . '.' . $ext;
                $file->storeAs("uploads/tmp/{$folder}", $filename);
                TemporaryFile::create([
                    'folder' => $folder,
                    'extension' => $ext,
                    'type' => 'video'
                ]);
                Session::put('folder', $folder);
                return response()->json([
                    'message' => 'temporary video successfully uploaded'
                ]);
            }elseif($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg'){
                $image = Image::make($request->file('file'))->stream('jpeg', 100);
                $path = "uploads/tmp/{$folder}/{$folder}.jpeg";
                Storage::disk('public')->put($path, $image, 'public');
                TemporaryFile::create([
                    'folder' => $folder,
                    'extension' => $ext,
                    'type' => 'image'
                ]);
                Session::put('folder', $folder);
                return response()->json([
                    'message' => 'temporary image successfully uploaded'
                ]);
            }elseif($ext == 'docx' || $ext == 'xlsx' || $ext ==  'pdf'){
                $filename = 'document' . '.' . $ext;
                $file->storeAs("uploads/tmp/{$folder}", $filename);
                TemporaryFile::create([
                    'folder' => $folder,
                    'extension' => $ext,
                    'type' => 'document'
                ]);
                Session::put('folder', $folder);
                return response()->json([
                    'message' => 'temporary document successfully uploaded'
                ]);
            }else{
                return response()->json([
                    'message' => 'unknown format'
                ]);
            }
        }catch (\Throwable $e) {
            // Session::forget('folder');
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destroy()
    {
        try {
            $temporaryFile = TemporaryFile::where('folder', Session::get('folder'))->first();
            Storage::delete("uploads/tmp/{$temporaryFile->folder}");
            Session::forget('folder');
            $temporaryFile->delete();
            return response()->json([
                'message' => 'temporary file has been destroyed'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
