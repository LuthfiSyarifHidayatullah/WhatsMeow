<?php

namespace Database\Seeders;

use App\Models\BotResponse;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Services (Layanan Diskominfo)
        $services = [
            [
                'name' => 'Pelayanan Jaringan & Infrastruktur TIK',
                'code' => 'jaringan',
                'description' => 'Layanan pengelolaan jaringan internet, infrastruktur TIK, dan konektivitas pemerintah daerah.',
                'keywords' => ['jaringan', 'internet', 'wifi', 'koneksi', 'infrastruktur', 'tik', 'server', 'bandwidth'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Pelayanan Informasi Publik',
                'code' => 'informasi',
                'description' => 'Layanan permintaan informasi publik, PPID, dan keterbukaan informasi.',
                'keywords' => ['informasi', 'ppid', 'keterbukaan', 'data publik', 'transparansi', 'info publik'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Pelayanan Aplikasi & E-Government',
                'code' => 'aplikasi',
                'description' => 'Layanan pengembangan, pemeliharaan aplikasi, dan sistem e-government.',
                'keywords' => ['aplikasi', 'e-government', 'sistem', 'website', 'portal', 'software', 'egov'],
                'sort_order' => 3,
            ],
            [
                'name' => 'Pelayanan Persandian & Keamanan Informasi',
                'code' => 'sandi',
                'description' => 'Layanan persandian, keamanan informasi, dan perlindungan data.',
                'keywords' => ['sandi', 'persandian', 'keamanan', 'security', 'data', 'enkripsi', 'cyber'],
                'sort_order' => 4,
            ],
            [
                'name' => 'Pelayanan Statistik & Data',
                'code' => 'statistik',
                'description' => 'Layanan pengelolaan data statistik sektoral, integrasi data, dan satu data.',
                'keywords' => ['statistik', 'data', 'satu data', 'integrasi', 'laporan', 'sensus'],
                'sort_order' => 5,
            ],
            [
                'name' => 'Pelayanan Media & Komunikasi',
                'code' => 'media',
                'description' => 'Layanan pengelolaan media sosial pemerintah, hubungan media, dan publikasi.',
                'keywords' => ['media', 'sosial', 'komunikasi', 'publikasi', 'pers', 'berita', 'humas'],
                'sort_order' => 6,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        // Create Admin User
        User::create([
            'name' => 'Admin Diskominfo',
            'email' => 'admin@mpp-bengkayang.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_online' => false,
        ]);

        // Create Supervisor
        User::create([
            'name' => 'Supervisor Diskominfo',
            'email' => 'supervisor@mpp-bengkayang.go.id',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'is_online' => false,
        ]);

        // Create Officers per service
        $officers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@mpp-bengkayang.go.id', 'service_code' => 'jaringan'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@mpp-bengkayang.go.id', 'service_code' => 'jaringan'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@mpp-bengkayang.go.id', 'service_code' => 'informasi'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@mpp-bengkayang.go.id', 'service_code' => 'aplikasi'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko@mpp-bengkayang.go.id', 'service_code' => 'sandi'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri@mpp-bengkayang.go.id', 'service_code' => 'statistik'],
            ['name' => 'Galih Prakoso', 'email' => 'galih@mpp-bengkayang.go.id', 'service_code' => 'media'],
            ['name' => 'Hani Sulistyowati', 'email' => 'hani@mpp-bengkayang.go.id', 'service_code' => 'media'],
        ];

        foreach ($officers as $officerData) {
            $service = Service::where('code', $officerData['service_code'])->first();
            User::create([
                'name' => $officerData['name'],
                'email' => $officerData['email'],
                'password' => Hash::make('password123'),
                'role' => 'officer',
                'service_id' => $service->id,
                'is_online' => false,
                'is_available' => true,
                'max_concurrent_chats' => 5,
            ]);
        }

        // Create Bot Responses
        $botResponses = [
            ['trigger_keyword' => 'jam', 'response_text' => "🕐 *Jam Operasional Diskominfo Kab. Bengkayang:*\n\nSenin - Kamis: 08.00 - 15.00 WIB\nJumat: 08.00 - 11.30 WIB\nSabtu - Minggu: Tutup\n\nLayanan online chatbot tersedia 24 jam.", 'match_type' => 'contains', 'priority' => 5],
            ['trigger_keyword' => 'alamat', 'response_text' => "📍 *Alamat Diskominfo Kab. Bengkayang:*\n\nJl. [Alamat Kantor Diskominfo]\nKecamatan Bengkayang\nKabupaten Bengkayang\nKalimantan Barat\n\n📞 Telp: (0562) XXXXXX", 'match_type' => 'contains', 'priority' => 5],
            ['trigger_keyword' => 'layanan internet', 'response_text' => "📋 *Layanan Jaringan & Internet:*\n\n1. Pengajuan koneksi internet OPD\n2. Pelaporan gangguan jaringan\n3. Permintaan bandwidth\n4. Konsultasi infrastruktur TIK\n\n⏱ Estimasi penanganan: 1-3 hari kerja\n💰 Biaya: Sesuai anggaran OPD", 'match_type' => 'contains', 'priority' => 10, 'service_id' => 1],
            ['trigger_keyword' => 'permohonan data', 'response_text' => "📋 *Permohonan Informasi Publik (PPID):*\n\n1. Mengisi formulir permohonan\n2. Menyertakan identitas pemohon\n3. Menjelaskan tujuan permohonan\n4. Menunggu verifikasi\n\n⏱ Estimasi waktu: 10 hari kerja\n💰 Biaya: GRATIS", 'match_type' => 'contains', 'priority' => 10, 'service_id' => 2],
            ['trigger_keyword' => 'terima kasih', 'response_text' => "🙏 Sama-sama! Senang bisa membantu.\n\nJika ada pertanyaan lain, jangan ragu untuk bertanya.\nKetik *menu* untuk kembali ke menu utama.", 'match_type' => 'contains', 'priority' => 1],
        ];

        foreach ($botResponses as $responseData) {
            BotResponse::create($responseData);
        }
    }
}
