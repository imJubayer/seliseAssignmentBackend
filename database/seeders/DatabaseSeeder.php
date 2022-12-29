<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Book;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        User::truncate();
        Book::truncate();

        $this->call(RoleSeeder::class);

        //for Admin
        $admin = \App\Models\User::factory()->create([
            'firstname' => 'Admin',
            'lastname' => 'Bhai',
            'email' => 'admin@mail.com',
            'password' => bcrypt('Asdf!234'),
            'status' => 1
        ]);
        $admin->assignRole('superadmin');

        $users = User::factory(5)->create();

        $users->each(function ($user, $key){
            $user->assignRole('member');
        });

        Book::factory(10)->create();
    }
}
