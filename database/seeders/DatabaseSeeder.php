<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo credentials and sample data must never reach production: the fixed
        // password would be re-hashed onto a real admin account on every seed,
        // and the sample invitation would be published. Keep it to local and tests.
        if (! app()->environment('local', 'testing')) {
            return;
        }

        User::updateOrCreate(
            ['email' => 'admin@jall.com'],
            [
                'name' => 'Super Admin JALL',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->call(DemoInvitationSeeder::class);
    }
}
