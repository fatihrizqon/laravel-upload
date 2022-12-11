<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use App\Models\TemporaryFile;
use App\Jobs\ImageConverterJob;
use App\Jobs\VideoConverterJob;
use App\Jobs\DocumentConverterJob;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function store(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'title' => ['string', 'required'],
            'file' => ['required']
        ]);
        
        $temporaryFile = TemporaryFile::where('folder', Session::get('folder'))->first();
        
        if($validators->fails()){
            $temporaryFile ? Storage::deleteDirectory("uploads/tmp/{$temporaryFile->folder}") : null;
            $temporaryFile ? $temporaryFile->delete() : null;
            Session::forget('folder');
            return redirect()->route('/')->with('warning', 'failed to store a new file.');
        }
        
        try {
            $attributes = [
                'title' => $request->title,
            ];

            if($temporaryFile){
                if($temporaryFile->type == 'video'){
                    $attributes['path'] = "uploads/videos/{$temporaryFile->folder}.mp4";
                    $this->dispatch(new VideoConverterJob($temporaryFile));
                }elseif($temporaryFile->type == 'image'){
                    $attributes['path'] = "uploads/images/{$temporaryFile->folder}.jpeg";
                    Storage::move("uploads/tmp/{$temporaryFile->folder}/{$temporaryFile->folder}.jpeg", "uploads/images/{$temporaryFile->folder}.jpeg");
                    Storage::deleteDirectory("uploads/tmp/{$temporaryFile->folder}");
                    $temporaryFile->delete();
                }elseif($temporaryFile->type == 'document'){
                    $attributes['path'] = "uploads/documents/{$temporaryFile->folder}.{$temporaryFile->extension}";
                    Storage::move("uploads/tmp/{$temporaryFile->folder}/document.{$temporaryFile->extension}", "uploads/documents/{$temporaryFile->folder}.{$temporaryFile->extension}");
                    Storage::deleteDirectory("uploads/tmp/{$temporaryFile->folder}");
                    $temporaryFile->delete();
                }else{
                    abort(403);
                }
            }
            File::create($attributes);
            Session::forget('folder');
            return redirect()->route('/')->with('success', 'a new file has been stored.');
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
