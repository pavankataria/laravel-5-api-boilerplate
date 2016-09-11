<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OAuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('oauth_clients')->insert(
            [
                'id' => 'oorvasiiosapp',
                'secret' => env('OAUTH_CLIENT_SECRET'),
                'name' => 'iOS oorvasi app',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
