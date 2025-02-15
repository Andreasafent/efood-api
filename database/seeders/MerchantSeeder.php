<?php

namespace Database\Seeders;

use App\Enum\RoleCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $afentoulidis = User::whereEmail('afentoulidis@test.com')->first();

        if($afentoulidis){
            $afentoulidis->roles()->attach(RoleCode::merchant);
        }
    }
}
