<?php

use Illuminate\Database\Seeder;

use App\User;
use Ramsey\Uuid\Uuid;

class UserImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = app()->make(User::class);
        $hasher = app()->make('hash');

        $user->fill([
            'guid' => Uuid::uuid4()->toString(),
            'name' => 'Pavan Kataria',
            'username' => 'pavankataria',
            'password' => $hasher->make('1234')
        ]);
        $user->save();
    }
}
