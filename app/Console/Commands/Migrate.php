<?php

namespace App\Console\Commands;

use App\Http\Controllers\BaseController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cookie;



class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:Migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate tables after 1 hour';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return int
     */

  
    public function handle()
    {
        \Artisan::call('migrate:fresh --force');
        Artisan::call('migrate' , ['--force' => true , '--path' => 'vendor/laravel/passport/database/migrations']);
        \Artisan::call('passport:install');
    }
}
