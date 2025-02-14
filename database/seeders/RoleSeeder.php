<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => [
                'en'=>'Admin',
                'el'=>'Διαχειριστής'
            ]
        ]);

        Role::create([
            'name' => [
                'en'=>'Merchant',
                'el'=>'Έμπορος'
            ]
        ]);

        Role::create([
            'name' => [
                'en'=>'Driver',
                'el'=>'Οδηγός'
            ]
        ]);
    }
}
