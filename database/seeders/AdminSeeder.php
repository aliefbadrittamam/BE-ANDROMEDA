<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'username' => 'admin123',
            'password' => Hash::make('admin123'),
            'email' => 'admin@andromeda.com',
            'nama_lengkap' => 'Administrator Andromeda',
        ]);
    }
}