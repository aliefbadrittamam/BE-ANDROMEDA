<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\User;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\Kuis;

class DatabaseSeeder extends Seeder
{
    public function run()
    {


        $this->call([
            AdminSeeder::class
        ]);
        // Create Admin
        $admin = Admin::create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'email' => 'admin@example.com',
            'nama_lengkap' => 'Administrator'
        ]);

        // Create Users
        $user1 = User::create([
            'username' => 'user1',
            'password' => Hash::make('password'),
            'email' => 'user1@example.com',
            'nama_lengkap' => 'User Satu',
            'status' => 'active'
        ]);

        $user2 = User::create([
            'username' => 'user2',
            'password' => Hash::make('password'),
            'email' => 'user2@example.com',
            'nama_lengkap' => 'User Dua',
            'status' => 'active'
        ]);

        // Create Materi
        $materi1 = Materi::create([
            'judul' => 'Pengenalan PHP',
            'konten_materi' => 'PHP adalah bahasa pemrograman server-side yang populer untuk pengembangan web. PHP mudah dipelajari dan memiliki sintaks yang sederhana.',
            'admin_id' => $admin->id
        ]);

        $materi2 = Materi::create([
            'judul' => 'Laravel Framework',
            'konten_materi' => 'Laravel adalah framework PHP yang elegant dan powerful. Laravel menyediakan sintaks yang ekspresif dan fitur-fitur yang memudahkan pengembangan aplikasi web.',
            'admin_id' => $admin->id
        ]);

        // Create Soal
        $soal1 = Soal::create([
            'judul' => 'PHP Dasar - Variabel',
            'pertanyaan' => 'Manakah cara yang benar untuk mendeklarasikan variabel dalam PHP?',
            'pilihan_a' => '$nama = "John";',
            'pilihan_b' => 'var nama = "John";',
            'pilihan_c' => 'string nama = "John";',
            'pilihan_d' => 'nama = "John";',
            'jawaban_benar' => 'A',
            'admin_id' => $admin->id
        ]);

        $soal2 = Soal::create([
            'judul' => 'PHP Dasar - Echo',
            'pertanyaan' => 'Fungsi manakah yang digunakan untuk menampilkan output dalam PHP?',
            'pilihan_a' => 'print()',
            'pilihan_b' => 'echo',
            'pilihan_c' => 'display()',
            'pilihan_d' => 'show()',
            'jawaban_benar' => 'B',
            'admin_id' => $admin->id
        ]);

        $soal3 = Soal::create([
            'judul' => 'Laravel - Routing',
            'pertanyaan' => 'File mana yang digunakan untuk mendefinisikan routes dalam Laravel?',
            'pilihan_a' => 'routes/api.php',
            'pilihan_b' => 'routes/web.php',
            'pilihan_c' => 'config/routes.php',
            'pilihan_d' => 'app/routes.php',
            'jawaban_benar' => 'B',
            'admin_id' => $admin->id
        ]);

        $soal4 = Soal::create([
            'judul' => 'Laravel - Eloquent',
            'pertanyaan' => 'Apa itu Eloquent dalam Laravel?',
            'pilihan_a' => 'Template engine',
            'pilihan_b' => 'ORM (Object Relational Mapping)',
            'pilihan_c' => 'Caching system',
            'pilihan_d' => 'Authentication system',
            'jawaban_benar' => 'B',
            'admin_id' => $admin->id
        ]);

        // Create Kuis
        $kuis1 = Kuis::create([
            'nama_kuis' => 'Kuis PHP Dasar',
            'deskripsi' => 'Kuis untuk menguji pemahaman dasar PHP',
            'deadline' => now()->addDays(7),
            'durasi_menit' => 30,
            'status' => 'published',
            'admin_id' => $admin->id
        ]);

        $kuis2 = Kuis::create([
            'nama_kuis' => 'Kuis Laravel Framework',
            'deskripsi' => 'Kuis untuk menguji pemahaman Laravel',
            'deadline' => now()->addDays(10),
            'durasi_menit' => 45,
            'status' => 'published',
            'admin_id' => $admin->id
        ]);

        // Attach soal to kuis
        $kuis1->soal()->attach([
            $soal1->id => ['urutan' => 1],
            $soal2->id => ['urutan' => 2]
        ]);

        $kuis2->soal()->attach([
            $soal3->id => ['urutan' => 1],
            $soal4->id => ['urutan' => 2]
        ]);
    }
}