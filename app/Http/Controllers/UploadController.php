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
        # Gimana caranya, kalau reload setelah upload tmp juga ilang?
        $unsaved = Session::get('folder') ?? false;
        if($unsaved){
            $unsavedFile = TemporaryFile::where('folder', $unsaved)->first();
            Storage::deleteDirectory("uploads/tmp/{$unsavedFile->folder}");
            Session::forget('folder');
            $unsavedFile->delete();
        }
        
        # Document: .docx, .xlsx, .pdf => pdf #
        # Image: .png, .jpg, .jpeg => jpeg #
        # Video: .avi, .mov, .mp4 => mp4 #
        $request->validate([
            'file' => ['required', 'mimes:pdf,doc,docx,png,jpg,jpeg,avi,mov,mp4']
        ]);
        
        try { 
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $folder = uniqid() . '-' . now()->timestamp;
            if($ext == 'avi' || $ext ==  'mov' || $ext ==  'mp4'){
                $filename = "video.{$ext}";
                $file->storeAs("uploads/tmp/{$folder}", $filename);
                TemporaryFile::create([
                    'folder' => $folder,
                    'extension' => $ext,
                    'type' => 'video'
                ]);
            }elseif($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg'){
                $image = Image::make($request->file('file'))->stream('jpeg', 50);
                $path = "uploads/tmp/{$folder}/{$folder}.jpeg";
                Storage::disk('public')->put($path, $image, 'public');
                TemporaryFile::create([
                    'folder' => $folder,
                    'extension' => $ext,
                    'type' => 'image'
                ]);
            }elseif($ext == 'docx' || $ext == 'xlsx' || $ext ==  'pdf'){
                $filename = 'document' . '.' . $ext;
                $file->storeAs("uploads/tmp/{$folder}", $filename);
                TemporaryFile::create([
                    'folder' => $folder,
                    'extension' => $ext,
                    'type' => 'document'
                ]);
            }else{
                return response()->json([
                    'message' => 'unknown format'
                ]);
            }
            Session::put('folder', $folder);
            return response()->json([
                'message' => 'temporary file successfully uploaded'
            ]);

        }catch (\Throwable $e) {
            Session::forget('folder');
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destroy()
    {
        try { 
            $temporaryFile = TemporaryFile::where('folder', Session::get('folder'))->first();
            Storage::deleteDirectory("uploads/tmp/{$temporaryFile->folder}");
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
