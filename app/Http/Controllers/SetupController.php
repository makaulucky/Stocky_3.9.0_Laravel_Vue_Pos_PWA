<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TestDbController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SetupController extends Controller
{

    public function changeEnv($data = array())
    {
        if (count($data) > 0) {

            // Read .env-file
            $env = file_get_contents(base_path() . '/.env');

            // Split string on every " " and write into array
            $env = preg_split('/(\r\n|\n|\r)/', $env);

            // Loop through given data
            foreach ((array) $data as $key => $value) {

                // Loop through .env-data
                foreach ($env as $env_key => $env_value) {

                    // Turn the value into an array and stop after the first split
                    // So it's not possible to split e.g. the App-Key by accident
                    $entry = explode("=", $env_value, 2);

                    // Check, if new key fits the actual .env-key
                    if ($entry[0] == $key) {
                        // If yes, overwrite it with the new one
                        if ($value !== null) {

                            $env[$env_key] = $key . "=" . $value;
                        }
                    } else {
                        // If not, keep the old one
                        $env[$env_key] = $env_value;
                    }
                }
            }

            // Turn the array back to an String
            $env = implode("\n", $env);

            // And overwrite the .env with the new data
            file_put_contents(base_path() . '/.env', $env);

            return true;
        } else {
            return false;
        }
    }


    public function viewStep1()
    {

        $data = array(
            "APP_NAME" => session('env.APP_NAME') ? str_replace('"', '', session('env.APP_NAME')) : str_replace('"', '', config('app.name')),
            "APP_ENV" => session('env.APP_ENV') ? session('env.APP_ENV') : config('app.env'),
            "APP_DEBUG" => session('env.APP_DEBUG') ? session('env.APP_DEBUG') : config('app.debug'),
            "APP_KEY" => session('env.APP_KEY') ? session('env.APP_KEY') : config('app.key'),
        );

        return view('setup.step1', compact('data'));
    }

    public function viewCheck()
    {
        return view('setup.check');
    }

    public function viewStep2()
    {

        if (config("database.default") == 'mysql') {
            $db = config('database.connections.mysql');

        }

        $data = array(
            "DB_CONNECTION" => session('env.DB_CONNECTION') ? session('env.DB_CONNECTION') : config("database.default"),
            "DB_HOST" => session('env.DB_HOST') ? session('env.DB_HOST') : (isset($db['host']) ? $db['host'] : ''),
            "DB_PORT" => session('env.DB_PORT') ? session('env.DB_PORT') : (isset($db['port']) ? $db['port'] : ''),
            "DB_DATABASE" => session('env.DB_DATABASE') ? session('env.DB_DATABASE') : (isset($db['database']) ? $db['database'] : ''),
            "DB_USERNAME" => session('env.DB_USERNAME') ? session('env.DB_USERNAME') : (isset($db['username']) ? $db['username'] : ''),
            "DB_PASSWORD" => session('env.DB_PASSWORD') ? str_replace('"', '', session('env.DB_PASSWORD')) : (isset($db['password']) ? str_replace('"', '', $db['password']) : ''),
        );

        return view('setup.step2', ["data" => $data]);
    }

    public function viewStep3()
    {
        $dbtype = null;

        if (session('env.DB_CONNECTION') == null) {
            $dbtype = config("database.default");
        } else {
            $dbtype = session('env.DB_CONNECTION');
        }

        if ($dbtype == 'mysql') {
            $db = config('database.connections.mysql');

        }

        $dbDatabase = session('env.DB_DATABASE');

        $data = array(

            "APP_NAME" => str_replace('"', '', session('env.APP_NAME')) == str_replace('"', '', config('app.name')) ? 'old' : str_replace('"', '', session('env.APP_NAME')),
            "APP_ENV" => session('env.APP_ENV') == config('app.env') ? 'old' : session('APP_ENV'),
            "APP_DEBUG" => session('env.APP_DEBUG') == config('app.debug') ? 'old' : session('env.APP_DEBUG'),
            "APP_KEY" => session('env.APP_KEY') == config('app.key') ? 'old' : session('env.APP_KEY'),
            "DB_CONNECTION" => session('env.DB_CONNECTION') == config("database.default") ? 'old' : session('env.DB_CONNECTION'),
            "DB_HOST" => session('env.DB_HOST') == (isset($db['host']) ? $db['host'] : '') ? 'old' : session('env.DB_HOST'),
            "DB_PORT" => session('env.DB_PORT') == (isset($db['port']) ? $db['port'] : '') ? 'old' : session('env.DB_PORT'),
            "DB_DATABASE" => $dbDatabase == (isset($db['database']) ? $db['database'] : '') ? 'old' : session('env.DB_DATABASE'),
            "DB_USERNAME" => session('env.DB_USERNAME') == (isset($db['username']) ? $db['username'] : '') ? 'old' : session('env.DB_USERNAME'),
            "DB_PASSWORD" => str_replace('"', '', session('env.DB_PASSWORD')) == (isset($db['password']) ? str_replace('"', '', $db['password']) : '') ? 'old' : str_replace('"', '', session('env.DB_PASSWORD')),

        );

        $count = 0;

        foreach ($data as $mydata) {

            $mydata !== 'old' ? $count++ : false;
        }

        $view = view('setup.step3', compact('data'));

        return $view;
    }

    public function lastStep(Request $request)
    {
        ini_set('max_execution_time', 600); //600 seconds = 10 minutes 

        try {
            $this->changeEnv([
                'APP_NAME' => session('env.APP_NAME'),
                'APP_ENV' => session('env.APP_ENV'),
                'APP_KEY' => session('env.APP_KEY'),
                'APP_DEBUG' => session('env.APP_DEBUG'),
                'APP_URL' => session('env.APP_URL'),
                'LOG_CHANNEL' => session('env.LOG_CHANNEL'),

                'DB_CONNECTION' => session('env.DB_CONNECTION'),
                'DB_HOST' => session('env.DB_HOST'),
                'DB_PORT' => session('env.DB_PORT'),
                'DB_DATABASE' => session('env.DB_DATABASE'),
                'DB_USERNAME' => session('env.DB_USERNAME'),
                'DB_PASSWORD' => session('env.DB_PASSWORD'),
            ]);

            Artisan::call('config:cache');
            Artisan::call('config:clear');

            Artisan::call('migrate:fresh --force --seed');

            Artisan::call('migrate', ['--force' => true, '--path' => 'vendor/laravel/passport/database/migrations']);
            Artisan::call('passport:install --force');
            Storage::disk('public')->put('installed', 'Contents');

            
        } catch (\Exception $e) {
            
            return $e->getMessage();
            
            return 'Something went wrong';
        }
        
        return view('setup.finishedSetup');
    }

    public function getNewAppKey()
    {

        Artisan::call('key:generate', ['--show' => true]);
        $output = (Artisan::output());
        $output = substr($output, 0, -2);
        return $output;
    }

    public function setupStep1(Request $request)
    {
        $allow = 'false';

        $request->session()->put('env.APP_ENV', $request->app_env);
        $request->session()->put('env.APP_DEBUG', $request->app_debug);

        if (strlen($request->app_name) > 0) {
            $request->session()->put('env.APP_NAME', '"' . $request->app_name . '"');
        }

        if (strlen($request->app_key) > 0) {
            $request->session()->put('env.APP_KEY', $request->app_key);
        }
        

        return $this->viewStep2();
    }

    public function setupStep2(Request $request)
    {

        if (strlen($request->db_password) > 0) {
            $request->session()->put('env.DB_PASSWORD', '"' . $request->db_password . '"');
        }
        $request->session()->put('env.DB_CONNECTION', $request->db_connection);
        $request->session()->put('env.DB_HOST', $request->db_host);
        $request->session()->put('env.DB_PORT', $request->db_port);
        $request->session()->put('env.DB_DATABASE', $request->db_database);
        $request->session()->put('env.DB_USERNAME', $request->db_username);
        // $request->session()->put('env.DB_PASSWORD', $request->db_password);

        if ($request->db_connection == 'sqlite') {
            TestDbController::testSqLite();
        }

        return $this->viewStep3();
    }

}
