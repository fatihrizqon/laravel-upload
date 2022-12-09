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

class FileController extends Controller
{
    public function store(Request $request)
    {
        $attributes = $request->validate([
            'title' => ['string', 'required'],
        ]);
        try {
            $temporaryFile = TemporaryFile::where('folder', Session::get('folder'))->first();
            if($temporaryFile){
                if($temporaryFile->type == 'video'){
                    $attributes['path'] = "uploads/{$temporaryFile->folder}.mp4";
                    # Running Job #
                    $this->dispatch(new VideoConverterJob($temporaryFile));
                }elseif($temporaryFile->type == 'image'){
                    $attributes['path'] = "uploads/{$temporaryFile->folder}.jpeg";
                    Storage::move("uploads/tmp/{$temporaryFile->folder}/{$temporaryFile->folder}.jpeg", "uploads/{$temporaryFile->folder}.jpeg");
                    Storage::deleteDirectory("uploads/tmp/{$temporaryFile->folder}");
                    $temporaryFile->delete();

                }elseif($temporaryFile->type == 'document'){
                    $attributes['path'] = "uploads/{$temporaryFile->folder}.pdf";
                    $this->dispatch(new DocumentConverterJob($temporaryFile));
                }else{
                    abort(403);
                }
            }
            
            $file = File::create($attributes);
            Session::forget('folder');
            return response()->json([
                'message' => 'a new file has been stored.',
                'data' => $file
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
