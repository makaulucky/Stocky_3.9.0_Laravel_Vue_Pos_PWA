<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       	// Insert some stuff
        DB::table('currencies')->insert(
            array(
                'id'     => 1,
                'code'   => 'USD',
                'name'   => 'US Dollar',
                'symbol' => '$',
            )
            
        );
    }
}
