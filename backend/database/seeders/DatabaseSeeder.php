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
        // Create Services (5 Pelayanan Diskominfo)
        $services = [
            [
                'name' => 'Domain Bengkayang.go.id',
                'code' => 'domain',
                'description' => 'Layanan pengajuan dan pengelolaan subdomain bengkayang.go.id untuk OPD.',
                'keywords' => ['domain', 'subdomain', 'bengkayang.go.id', 'website', 'hosting', 'dns'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Zoom Meeting/Video Conference',
                'code' => 'zoom',
                'description' => 'Layanan peminjaman akun Zoom Meeting dan Video Conference untuk kegiatan dinas.',
                'keywords' => ['zoom', 'meeting', 'video conference', 'vicon', 'webinar', 'rapat online'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Fasilitasi Dokumentasi Kegiatan',
                'code' => 'dokumentasi',
                'description' => 'Layanan pengajuan fasilitasi dokumentasi kegiatan OPD (foto, video, liputan).',
                'keywords' => ['dokumentasi', 'foto', 'video', 'liputan', 'fasilitasi', 'kegiatan'],
                'sort_order' => 3,
            ],
            [
                'name' => 'Tanda Tangan Elektronik (TTE)',
                'code' => 'tte',
                'description' => 'Layanan pengajuan dan penerbitan Tanda Tangan Elektronik untuk ASN.',
                'keywords' => ['tte', 'tanda tangan elektronik', 'digital signature', 'sertifikat elektronik', 'bsre'],
                'sort_order' => 4,
            ],
            [
                'name' => 'Alat dan Operator Kegiatan',
                'code' => 'alat',
                'description' => 'Layanan peminjaman alat dokumentasi, multimedia, dan operator untuk kegiatan dinas.',
                'keywords' => ['alat', 'operator', 'kamera', 'multimedia', 'sound system', 'peminjaman'],
                'sort_order' => 5,
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
            ['name' => 'Budi Santoso', 'email' => 'budi@mpp-bengkayang.go.id', 'service_code' => 'domain'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@mpp-bengkayang.go.id', 'service_code' => 'zoom'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@mpp-bengkayang.go.id', 'service_code' => 'dokumentasi'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@mpp-bengkayang.go.id', 'service_code' => 'tte'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko@mpp-bengkayang.go.id', 'service_code' => 'alat'],
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

        // Bot Responses - hanya formulir (link GForm) per layanan
        // Ganti [LINK_GFORM_xxx] dengan link Google Form yang sebenarnya
        $botResponses = [
            ['trigger_keyword' => 'formulir', 'service_id' => 1, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📝 *Formulir Pengajuan Domain*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_DOMAIN]\n\nSetelah mengisi formulir, petugas akan memproses pengajuan Anda dalam 3-5 hari kerja."],

            ['trigger_keyword' => 'formulir', 'service_id' => 2, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📝 *Formulir Pengajuan Zoom Meeting*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_ZOOM]\n\nPastikan mengajukan minimal H-2 hari kerja sebelum kegiatan."],

            ['trigger_keyword' => 'formulir', 'service_id' => 3, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📝 *Formulir Pengajuan Fasilitasi Dokumentasi*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_DOKUMENTASI]\n\nPastikan mengajukan minimal H-3 hari kerja sebelum kegiatan."],

            ['trigger_keyword' => 'formulir', 'service_id' => 4, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📝 *Formulir Pengajuan TTE*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_TTE]\n\nPastikan melengkapi persyaratan dokumen yang diperlukan."],

            ['trigger_keyword' => 'formulir', 'service_id' => 5, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📝 *Formulir Pengajuan Peminjaman Alat & Operator*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_ALAT]\n\nPastikan mengajukan minimal H-3 hari kerja sebelum kegiatan."],
        ];

        foreach ($botResponses as $responseData) {
            BotResponse::create($responseData);
        }
    }
}
