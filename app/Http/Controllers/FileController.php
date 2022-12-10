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
        // $attributes = $request->validate([
        //     'title' => ['string', 'required'],
        //     'file' => ['required']
        // ]);
        
        $validators = Validator::make($request->all(), [
            'title' => ['string', 'required'],
            'file' => ['required']
        ]);
        
        $temporaryFile = TemporaryFile::where('folder', Session::get('folder'))->first();
        
        if($validators->fails()){
            Storage::deleteDirectory("uploads/tmp/{$temporaryFile->folder}");
            Session::forget('folder');
            $temporaryFile->delete();
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
                    $attributes['path'] = "uploads/{$temporaryFile->folder}.pdf";
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
