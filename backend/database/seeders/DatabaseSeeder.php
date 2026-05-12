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
        // Create Services (Layanan MPP)
        $services = [
            [
                'name' => 'Pelayanan KTP & Kependudukan',
                'code' => 'ktp',
                'description' => 'Layanan pembuatan KTP, KK, Akta Kelahiran, Akta Kematian, dan dokumen kependudukan lainnya.',
                'keywords' => ['ktp', 'kartu tanda penduduk', 'kk', 'kartu keluarga', 'akta', 'kependudukan', 'e-ktp', 'identitas'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Pelayanan Perpajakan',
                'code' => 'pajak',
                'description' => 'Layanan pembayaran pajak daerah, PBB, BPHTB, dan konsultasi perpajakan.',
                'keywords' => ['pajak', 'pbb', 'bphtb', 'tax', 'retribusi', 'npwp', 'bayar pajak'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Pelayanan Kepegawaian',
                'code' => 'pegawai',
                'description' => 'Layanan administrasi kepegawaian, kenaikan pangkat, mutasi, dan pensiun.',
                'keywords' => ['pegawai', 'kepegawaian', 'asn', 'pns', 'pangkat', 'mutasi', 'pensiun', 'sk'],
                'sort_order' => 3,
            ],
            [
                'name' => 'Pelayanan Perizinan',
                'code' => 'izin',
                'description' => 'Layanan pengurusan izin usaha, IMB, SIUP, dan perizinan lainnya.',
                'keywords' => ['izin', 'perizinan', 'imb', 'siup', 'usaha', 'oss', 'nib'],
                'sort_order' => 4,
            ],
            [
                'name' => 'Pelayanan Kesehatan',
                'code' => 'kesehatan',
                'description' => 'Layanan informasi BPJS, rujukan, dan konsultasi kesehatan masyarakat.',
                'keywords' => ['kesehatan', 'bpjs', 'rumah sakit', 'puskesmas', 'rujukan', 'obat', 'berobat'],
                'sort_order' => 5,
            ],
            [
                'name' => 'Pelayanan Pendidikan',
                'code' => 'pendidikan',
                'description' => 'Layanan informasi pendaftaran sekolah, beasiswa, dan administrasi pendidikan.',
                'keywords' => ['pendidikan', 'sekolah', 'beasiswa', 'ppdb', 'daftar sekolah', 'ijazah'],
                'sort_order' => 6,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        // Create Admin User
        User::create([
            'name' => 'Admin MPP',
            'email' => 'admin@mpp-bengkayang.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_online' => false,
        ]);

        // Create Supervisor
        User::create([
            'name' => 'Supervisor MPP',
            'email' => 'supervisor@mpp-bengkayang.go.id',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'is_online' => false,
        ]);

        // Create Officers per service
        $officers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@mpp-bengkayang.go.id', 'service_code' => 'ktp'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@mpp-bengkayang.go.id', 'service_code' => 'ktp'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@mpp-bengkayang.go.id', 'service_code' => 'pajak'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@mpp-bengkayang.go.id', 'service_code' => 'pajak'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko@mpp-bengkayang.go.id', 'service_code' => 'pegawai'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri@mpp-bengkayang.go.id', 'service_code' => 'izin'],
            ['name' => 'Galih Prakoso', 'email' => 'galih@mpp-bengkayang.go.id', 'service_code' => 'kesehatan'],
            ['name' => 'Hani Sulistyowati', 'email' => 'hani@mpp-bengkayang.go.id', 'service_code' => 'pendidikan'],
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
            ['trigger_keyword' => 'jam', 'response_text' => "🕐 *Jam Operasional MPP Kab. Bengkayang:*\n\nSenin - Kamis: 08.00 - 15.00 WIB\nJumat: 08.00 - 11.30 WIB\nSabtu - Minggu: Tutup\n\nLayanan online chatbot tersedia 24 jam.", 'match_type' => 'contains', 'priority' => 5],
            ['trigger_keyword' => 'alamat', 'response_text' => "📍 *Alamat MPP Kab. Bengkayang:*\n\nJl. [Alamat MPP]\nKecamatan Bengkayang\nKabupaten Bengkayang\nKalimantan Barat\n\n📞 Telp: (0562) XXXXXX", 'match_type' => 'contains', 'priority' => 5],
            ['trigger_keyword' => 'syarat ktp', 'response_text' => "📋 *Syarat Pembuatan KTP:*\n\n1. Fotocopy KK\n2. Surat Pengantar RT/RW\n3. Pas foto 3x4 (2 lembar)\n4. Usia minimal 17 tahun / sudah menikah\n5. Formulir yang sudah diisi\n\n⏱ Estimasi waktu: 1-3 hari kerja\n💰 Biaya: GRATIS", 'match_type' => 'contains', 'priority' => 10, 'service_id' => 1],
            ['trigger_keyword' => 'syarat kk', 'response_text' => "📋 *Syarat Pembuatan Kartu Keluarga:*\n\n1. Surat Pengantar RT/RW\n2. Fotocopy Akta Nikah/Cerai\n3. Fotocopy KTP semua anggota keluarga\n4. Fotocopy Akta Kelahiran\n5. Formulir yang sudah diisi\n\n⏱ Estimasi waktu: 3-5 hari kerja\n💰 Biaya: GRATIS", 'match_type' => 'contains', 'priority' => 10, 'service_id' => 1],
            ['trigger_keyword' => 'terima kasih', 'response_text' => "🙏 Sama-sama! Senang bisa membantu.\n\nJika ada pertanyaan lain, jangan ragu untuk bertanya.\nKetik *menu* untuk kembali ke menu utama.", 'match_type' => 'contains', 'priority' => 1],
        ];

        foreach ($botResponses as $responseData) {
            BotResponse::create($responseData);
        }
    }
}
