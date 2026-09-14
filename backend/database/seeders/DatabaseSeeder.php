<?php

namespace Database\Seeders;

use App\Models\BotResponse;
use App\Models\Opd;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // OPD (Organisasi Perangkat Daerah) - Sample 2 Pengampu SPM
        // =====================================================
        $opds = [
            [
                'name' => 'Dinas Pendidikan',
                'code' => 'pendidikan',
                'description' => 'Pengampu SPM Bidang Pendidikan (Perbup Bengkayang No. 45 Tahun 2021).',
                'sort_order' => 1,
            ],
            [
                'name' => 'Satuan Polisi Pamong Praja',
                'code' => 'satpolpp',
                'description' => 'Pengampu SPM Sub-Urusan Ketentraman dan Ketertiban Umum.',
                'sort_order' => 2,
            ],
        ];

        $opdModels = [];
        foreach ($opds as $opdData) {
            $opd = Opd::create($opdData);
            $opdModels[$opd->code] = $opd;
        }

        // =====================================================
        // SERVICE (Layanan) per OPD
        // =====================================================
        $services = [
            // --- Dinas Pendidikan ---
            [
                'opd_code' => 'pendidikan',
                'name' => 'Pendidikan Anak Usia Dini (PAUD)',
                'code' => 'pendidikan_paud',
                'description' => 'Layanan pendidikan anak usia dini (PAUD) sesuai SPM Bidang Pendidikan.',
                'keywords' => ['paud', 'anak usia dini', 'tk', 'taman kanak', 'kelompok bermain'],
                'sort_order' => 1,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'Pendidikan Dasar (SD/SMP)',
                'code' => 'pendidikan_dasar',
                'description' => 'Layanan pendidikan dasar jenjang SD dan SMP sesuai SPM Bidang Pendidikan.',
                'keywords' => ['pendidikan dasar', 'sd', 'smp', 'sekolah dasar', 'sekolah menengah pertama'],
                'sort_order' => 2,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'Pendidikan Kesetaraan',
                'code' => 'pendidikan_kesetaraan',
                'description' => 'Layanan pendidikan kesetaraan (Paket A/B/C) sesuai SPM Bidang Pendidikan.',
                'keywords' => ['kesetaraan', 'paket a', 'paket b', 'paket c', 'kejar paket'],
                'sort_order' => 3,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'Pengaduan Pelayanan Pendidikan',
                'code' => 'pendidikan_pengaduan',
                'description' => 'Kanal pengaduan atas pelayanan pendidikan di Kabupaten Bengkayang.',
                'keywords' => ['pengaduan pendidikan', 'keluhan sekolah', 'aduan pendidikan', 'lapor pendidikan'],
                'sort_order' => 4,
            ],

            // --- Satuan Polisi Pamong Praja ---
            [
                'opd_code' => 'satpolpp',
                'name' => 'Pengaduan Gangguan Ketertiban Umum',
                'code' => 'satpolpp_pengaduan_trantibum',
                'description' => 'Pengaduan gangguan ketertiban umum (kebisingan, PKL liar, bangunan tanpa izin, dll).',
                'keywords' => ['ketertiban', 'trantibum', 'pkl', 'kebisingan', 'gangguan', 'ketentraman'],
                'sort_order' => 1,
            ],
            [
                'opd_code' => 'satpolpp',
                'name' => 'Permohonan Bantuan Pengamanan Kegiatan',
                'code' => 'satpolpp_pengamanan',
                'description' => 'Permohonan bantuan pengamanan Satpol PP untuk kegiatan/keramaian.',
                'keywords' => ['pengamanan', 'bantuan pengamanan', 'pengawalan', 'keramaian', 'acara'],
                'sort_order' => 2,
            ],
            [
                'opd_code' => 'satpolpp',
                'name' => 'Informasi Perlindungan Masyarakat (Linmas)',
                'code' => 'satpolpp_linmas',
                'description' => 'Informasi terkait Perlindungan Masyarakat (Linmas).',
                'keywords' => ['linmas', 'perlindungan masyarakat', 'hansip', 'satlinmas'],
                'sort_order' => 3,
            ],
            [
                'opd_code' => 'satpolpp',
                'name' => 'Pengaduan Pelayanan Satpol PP',
                'code' => 'satpolpp_pengaduan',
                'description' => 'Kanal pengaduan atas pelayanan Satuan Polisi Pamong Praja.',
                'keywords' => ['pengaduan satpol', 'keluhan satpol', 'aduan satpol', 'lapor satpol'],
                'sort_order' => 4,
            ],
        ];

        $serviceModels = [];
        foreach ($services as $serviceData) {
            $opdCode = $serviceData['opd_code'];
            unset($serviceData['opd_code']);
            $serviceData['opd_id'] = $opdModels[$opdCode]->id;
            $service = Service::create($serviceData);
            $serviceModels[$service->code] = $service;
        }

        // =====================================================
        // USERS: Admin, Supervisor, Officers per Service
        // =====================================================
        User::create([
            'name' => 'Admin Bengkayang',
            'email' => 'admin@siap-bengkayang.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_online' => false,
        ]);

        User::create([
            'name' => 'Supervisor Bengkayang',
            'email' => 'supervisor@siap-bengkayang.go.id',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'is_online' => false,
        ]);

        // Officer per layanan (1 officer tiap service)
        $officers = [
            ['name' => 'Rina Kartika', 'email' => 'rina@siap-bengkayang.go.id', 'service_code' => 'pendidikan_paud'],
            ['name' => 'Bayu Nugroho', 'email' => 'bayu@siap-bengkayang.go.id', 'service_code' => 'pendidikan_dasar'],
            ['name' => 'Sari Wulandari', 'email' => 'sari@siap-bengkayang.go.id', 'service_code' => 'pendidikan_kesetaraan'],
            ['name' => 'Dedi Kurniawan', 'email' => 'dedi@siap-bengkayang.go.id', 'service_code' => 'pendidikan_pengaduan'],
            ['name' => 'Agus Salim', 'email' => 'agus@siap-bengkayang.go.id', 'service_code' => 'satpolpp_pengaduan_trantibum'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra@siap-bengkayang.go.id', 'service_code' => 'satpolpp_pengamanan'],
            ['name' => 'Lestari Ningsih', 'email' => 'lestari@siap-bengkayang.go.id', 'service_code' => 'satpolpp_linmas'],
            ['name' => 'Fajar Ramadhan', 'email' => 'fajar@siap-bengkayang.go.id', 'service_code' => 'satpolpp_pengaduan'],
        ];

        foreach ($officers as $officerData) {
            User::create([
                'name' => $officerData['name'],
                'email' => $officerData['email'],
                'password' => Hash::make('password123'),
                'role' => 'officer',
                'service_id' => $serviceModels[$officerData['service_code']]->id,
                'is_online' => false,
                'is_available' => true,
                'max_concurrent_chats' => 5,
            ]);
        }

        // =====================================================
        // BOT RESPONSES: Formulir (link GForm) untuk layanan
        // bertipe formulir_then_escalate.
        // Ganti [LINK_GFORM_xxx] dengan link Google Form sebenarnya.
        // =====================================================
        $botResponses = [
            [
                'service_code' => 'pendidikan_paud',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan PAUD*\n\nSilakan isi formulir permohonan layanan Pendidikan Anak Usia Dini melalui link berikut:\n\n🔗 [LINK_GFORM_PAUD]\n\n✅ *Setelah mengisi formulir, ketik 3 untuk konfirmasi ke petugas bahwa Anda sudah mengajukan.*",
            ],
            [
                'service_code' => 'pendidikan_dasar',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan Pendidikan Dasar (SD/SMP)*\n\nSilakan isi formulir permohonan melalui link berikut:\n\n🔗 [LINK_GFORM_DIKDAS]\n\n✅ *Setelah mengisi formulir, ketik 3 untuk konfirmasi ke petugas bahwa Anda sudah mengajukan.*",
            ],
            [
                'service_code' => 'pendidikan_kesetaraan',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan Pendidikan Kesetaraan*\n\nSilakan isi formulir permohonan (Paket A/B/C) melalui link berikut:\n\n🔗 [LINK_GFORM_KESETARAAN]\n\n✅ *Setelah mengisi formulir, ketik 3 untuk konfirmasi ke petugas bahwa Anda sudah mengajukan.*",
            ],
            [
                'service_code' => 'satpolpp_pengamanan',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Permohonan Bantuan Pengamanan Kegiatan*\n\nSilakan isi formulir permohonan pengamanan melalui link berikut:\n\n🔗 [LINK_GFORM_PENGAMANAN]\n\nPastikan mengajukan minimal H-3 hari kerja sebelum kegiatan.\n\n✅ *Setelah mengisi formulir, ketik 3 untuk konfirmasi ke petugas bahwa Anda sudah mengajukan.*",
            ],
        ];

        foreach ($botResponses as $responseData) {
            $serviceCode = $responseData['service_code'];
            unset($responseData['service_code']);
            $responseData['service_id'] = $serviceModels[$serviceCode]->id;
            $responseData['match_type'] = 'exact';
            $responseData['priority'] = 10;
            BotResponse::create($responseData);
        }
    }
}
