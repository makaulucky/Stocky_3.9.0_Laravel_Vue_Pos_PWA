<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PDO;
use PDOException;

class TestDbController extends Controller
{
    protected $mydb;

    public function testDB(Request $request)
    {

        $request->session()->put('env.DB_CONNECTION', $request->db_connection);
        $request->session()->put('env.DB_HOST', $request->db_host);
        $request->session()->put('env.DB_PORT', $request->db_port);
        $request->session()->put('env.DB_DATABASE', $request->db_database);
        $request->session()->put('env.DB_USERNAME', $request->db_username);
        $request->session()->put('env.DB_PASSWORD', $request->db_password);

        if ($request->db_connection == 'mysql') {
            return $this->testMySql();
        }

        return response()->json([
            'Error' => 'DB Type not Supported for testing',
            'State' => '999',
        ]);

    }

    public function testMySql()
    {
        $db_type = session('env.DB_CONNECTION');
        $db_host = session('env.DB_HOST');
        $db_name = session('env.DB_DATABASE');
        $db_user = session('env.DB_USERNAME');
        $db_pass = session('env.DB_PASSWORD');
        $db_port = session('env.DB_PORT');

        if (!$db_host) {
            return response()->json([
                'Error' => 'No Host',
                'State' => '999',
            ]);
        }

        try {
            $db = new PDO($db_type . ':host=' . $db_host . ';port=' . $db_port . ';dbname=' . $db_name, $db_user, $db_pass, array(PDO::ATTR_TIMEOUT => '5', PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_LOCAL_INFILE => true));
        } catch (PDOException $e) {

            if ($e->getCode() == '1049' && !$db_name == '') {
                $db = new PDO($db_type . ':host=' . $db_host . ';port=' . $db_port . ';dbname=' . '', $db_user, $db_pass, array(PDO::ATTR_TIMEOUT => '5', PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_LOCAL_INFILE => true));
                $db->query("CREATE DATABASE IF NOT EXISTS $db_name");
                return response()->json([
                    'State' => '200',
                    'Success' => 'Database ' . $db_name . ' created',
                ]);

            }

            return response()->json([
                'Error' => $e->getMessage(),
                'State' => $e->getCode(),
            ]);

        }

        return response()->json([
            'State' => '200',
            'Success' => 'Seems okay',
        ]);

    }

}
