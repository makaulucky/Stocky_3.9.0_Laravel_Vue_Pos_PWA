<?php



namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;



class DatabaseBackUp extends Command

{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'database:backup';



    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Command description';



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
        foreach (glob(storage_path().'/app/public/backup/*') as $filename) {
			$path = storage_path().'/app/public/backup/'.basename($filename);
            @unlink($path);
        } 

        $db_pass=env('DB_PASSWORD');
        $filename = "backup-" . Carbon::now()->format('Y-m-d') . ".sql"; 
        if($db_pass !='') {
            $command = "".env('DUMP_PATH')." --user=" . env('DB_USERNAME') . " --password='$db_pass'" ." --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . "  > " . storage_path() . "/app/public/backup/" . $filename;
        }else{
            $command = "".env('DUMP_PATH')." --user=" . env('DB_USERNAME') . " --password=$db_pass" ." --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . "  > " . storage_path() . "/app/public/backup/" . $filename;

        }
        $returnVar = NULL;
        $output = NULL;
        exec($command, $output, $returnVar);
    }

}