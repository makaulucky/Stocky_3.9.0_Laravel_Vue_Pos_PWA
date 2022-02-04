<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TestDbController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class UpdateController extends Controller
{

    public function viewStep1(Request $request)
    {
        return view('update.viewStep1');
    }
    
    public function lastStep(Request $request)
    {
        ini_set('max_execution_time', 600); //600 seconds = 10 minutes 

        try {
           
            Artisan::call('config:cache');
            Artisan::call('config:clear');

            Artisan::call('migrate --force');
            
        } catch (\Exception $e) {
            
            return $e->getMessage();
            
            return 'Something went wrong';
        }
        
        return view('update.finishedUpdate');
    }

    

}
