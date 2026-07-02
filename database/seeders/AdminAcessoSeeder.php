<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminAcessoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::withoutGlobalScopes()->where('username', 'admin')->first();
        
        if ($user) {
            $user->update([
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 1
            ]);
        } else {
            User::create([
                'name' => 'Super Admin',
                'username' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 1,
                'site_id' => 1
            ]);
        }
    }
}
