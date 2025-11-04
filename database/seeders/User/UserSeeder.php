<?php

namespace Database\Seeders\User;

use App\Enums\User\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->command->info('Creating Admin User...');

        try {

            $user = new User();
            $user->username = 'admin';
            $user->name = 'Ahmed Gamal';
            $user->email = 'admin@admin.com';
            $user->password = '123456';
            $user->is_active = UserStatus::ACTIVE;
            $user->email_verified_at = now();
            $user->phone = '12345678';
            $user->address = 'Admin Address';
            $user->save();

            $role = Role::where('name', 'super admin')->first();

            $user->assignRole($role);

        } catch (\Exception $e) {
            $this->command->error('Error creating user: ' . $e->getMessage());
            return;
        }

    }
}
