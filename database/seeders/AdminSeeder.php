<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Create the single, permanent Super Admin account.
     *
     * The email is hardcoded in config/auth.php (config('auth.super_admin.email')).
     * The default password is only ever applied at first creation; afterwards
     * the account owner controls it through the normal profile flow.
     */
    public function run(): void
    {
        $email = config('auth.super_admin.email');
        $defaultPassword = config('auth.super_admin.default_password');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => $defaultPassword, // hashed automatically by the model cast
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
