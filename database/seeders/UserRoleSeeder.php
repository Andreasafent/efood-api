<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Searching for user with email 'andreas@test.com'...");
        $user = User::whereEmail('andreas@test.com')->first();
        if($user){
            $this->command->info("User found! With name {$user->name}");
        }else{
            $this->command->info("User not found.");
            return;
        }
        
        $this->command->info("Searching for role with name 'Admin'...");
        $role = Role::where('name->en', 'Admin')->first();
        if($role){
            $this->command->info("User found! With name {$role->name}");
        }else{
            $this->command->info("User not found.");
            return;
        }


        $this->command->info("Attaching role to user...");
        $user->roles()->attach($role->id);

        $this->command->info("Role attached to user");
    }
}
