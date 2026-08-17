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
                'name' => 'Informasi Publik',
                'code' => 'informasi',
                'description' => 'Layanan permohonan informasi publik melalui PPID Kab. Bengkayang.',
                'keywords' => ['informasi', 'ppid', 'keterbukaan', 'data publik', 'transparansi', 'info publik'],
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
                'keywords' => ['alat', 'operator', 'kamera', 'dokumentasi', 'multimedia', 'sound system', 'peminjaman'],
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
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@mpp-bengkayang.go.id', 'service_code' => 'informasi'],
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

        // Create Bot Responses (sub-menu info per service)
        // Key format matches serviceMenus in ChatbotService
        $botResponses = [
            // === DOMAIN ===
            ['trigger_keyword' => 'persyaratan', 'service_id' => 1, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Persyaratan Pengajuan Domain bengkayang.go.id:*\n\n1. Surat permohonan resmi dari Kepala OPD\n2. Nama domain yang diajukan\n3. IP Address atau hosting yang akan digunakan\n4. Penanggung jawab teknis (nama, NIP, kontak)\n5. Deskripsi singkat tujuan website"],
            ['trigger_keyword' => 'prosedur', 'service_id' => 1, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Prosedur Pengajuan Domain:*\n\n1. Mengisi formulir permohonan domain\n2. Melampirkan surat resmi dari Kepala OPD\n3. Menyerahkan ke Diskominfo\n4. Verifikasi oleh tim teknis (1-3 hari kerja)\n5. Konfigurasi DNS\n6. Domain aktif dan siap digunakan\n\n⏱ Estimasi: 3-5 hari kerja"],
            ['trigger_keyword' => 'info_domain', 'service_id' => 1, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Informasi Domain Tersedia:*\n\nFormat domain: [nama].bengkayang.go.id\n\nUntuk mengecek ketersediaan domain, silakan hubungi petugas kami.\n\nDomain yang sudah terdaftar tidak dapat digunakan oleh OPD lain."],
            ['trigger_keyword' => 'formulir', 'service_id' => 1, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Formulir Pengajuan Domain:*\n\nFormulir dapat diunduh di website resmi Diskominfo atau langsung menghubungi petugas untuk mendapatkan formulir.\n\nKetik *6* untuk menghubungi petugas."],
            ['trigger_keyword' => 'bantuan', 'service_id' => 1, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Bantuan/Gangguan Domain:*\n\nJika mengalami kendala terkait domain:\n• Domain tidak bisa diakses\n• Error DNS\n• Perlu perpanjangan/perubahan\n\nSilakan ketik *6* untuk langsung menghubungi petugas teknis kami."],

            // === ZOOM ===
            ['trigger_keyword' => 'persyaratan', 'service_id' => 2, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Persyaratan Peminjaman Zoom Meeting:*\n\n1. Surat permohonan dari OPD/instansi\n2. Nama kegiatan\n3. Tanggal dan waktu pelaksanaan\n4. Estimasi jumlah peserta\n5. Penanggung jawab kegiatan (nama & kontak)\n6. Pengajuan minimal H-2 hari kerja"],
            ['trigger_keyword' => 'prosedur', 'service_id' => 2, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Prosedur Peminjaman Zoom:*\n\n1. Ajukan permohonan via formulir/surat\n2. Diskominfo cek ketersediaan jadwal\n3. Konfirmasi persetujuan\n4. Link Zoom dikirimkan H-1 kegiatan\n5. Pelaksanaan kegiatan\n6. Pelaporan pasca kegiatan (opsional)\n\n⏱ Konfirmasi: 1-2 hari kerja"],
            ['trigger_keyword' => 'jadwal', 'service_id' => 2, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Informasi Jadwal & Ketersediaan:*\n\nUntuk mengecek ketersediaan jadwal Zoom Meeting, silakan hubungi petugas kami.\n\nJam operasional:\nSenin - Kamis: 08.00 - 15.00 WIB\nJumat: 08.00 - 11.30 WIB\n\nKetik *6* untuk menghubungi petugas."],
            ['trigger_keyword' => 'formulir', 'service_id' => 2, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Formulir Pengajuan Jadwal Zoom:*\n\nFormulir pengajuan jadwal Zoom Meeting dapat diminta langsung ke petugas Diskominfo.\n\nKetik *6* untuk menghubungi petugas."],
            ['trigger_keyword' => 'bantuan', 'service_id' => 2, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Bantuan/Gangguan Zoom:*\n\nKendala yang sering terjadi:\n• Tidak bisa join meeting\n• Audio/video bermasalah\n• Kapasitas peserta penuh\n• Link meeting error\n\nSilakan ketik *6* untuk bantuan langsung dari petugas."],

            // === INFORMASI PUBLIK ===
            ['trigger_keyword' => 'persyaratan', 'service_id' => 3, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Persyaratan Permohonan Informasi Publik:*\n\n1. KTP/identitas pemohon\n2. Alasan/tujuan permohonan informasi\n3. Informasi yang diminta (spesifik)\n4. Mengisi formulir permohonan PPID\n\n💰 Biaya: GRATIS"],
            ['trigger_keyword' => 'prosedur', 'service_id' => 3, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Prosedur Permohonan Informasi:*\n\n1. Mengisi formulir permohonan informasi\n2. Menyerahkan ke PPID Kab. Bengkayang\n3. Verifikasi permohonan (3 hari kerja)\n4. Pemberitahuan tertulis diterima/ditolak\n5. Jika diterima, informasi diberikan\n\n⏱ Estimasi: 10 hari kerja"],
            ['trigger_keyword' => 'daftar_info', 'service_id' => 3, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Daftar Informasi Publik:*\n\nInformasi yang dapat dimohonkan:\n• Profil dan struktur organisasi\n• Program dan kegiatan\n• Anggaran dan realisasi\n• Laporan keuangan\n• Peraturan daerah\n• Data statistik daerah\n\nUntuk detail, hubungi petugas PPID."],
            ['trigger_keyword' => 'formulir', 'service_id' => 3, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Formulir Permohonan Informasi:*\n\nFormulir permohonan informasi publik tersedia di:\n• Kantor PPID Kab. Bengkayang\n• Website ppid.bengkayang.go.id\n\nAtau hubungi petugas untuk mendapatkan formulir.\nKetik *6* untuk menghubungi petugas."],
            ['trigger_keyword' => 'bantuan', 'service_id' => 3, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Bantuan Informasi Publik:*\n\nJika ada kendala:\n• Status permohonan belum direspon\n• Keberatan atas penolakan informasi\n• Butuh panduan pengisian formulir\n\nKetik *6* untuk menghubungi petugas PPID."],

            // === TTE ===
            ['trigger_keyword' => 'persyaratan', 'service_id' => 4, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Persyaratan Pengajuan TTE:*\n\n1. ASN aktif di lingkungan Pemkab Bengkayang\n2. SK Jabatan terakhir\n3. KTP elektronik\n4. Email dinas aktif (@bengkayang.go.id)\n5. Pas foto digital\n6. Surat permohonan dari atasan langsung"],
            ['trigger_keyword' => 'prosedur', 'service_id' => 4, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Prosedur Pengajuan TTE:*\n\n1. Mengisi formulir pengajuan TTE\n2. Melengkapi persyaratan dokumen\n3. Verifikasi oleh Diskominfo\n4. Pendaftaran ke BSrE (Badan Siber)\n5. Penerbitan sertifikat elektronik\n6. Aktivasi TTE\n\n⏱ Estimasi: 5-14 hari kerja"],
            ['trigger_keyword' => 'status', 'service_id' => 4, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Cek Status Pengajuan TTE:*\n\nUntuk mengecek status pengajuan TTE Anda, silakan hubungi petugas dengan menyebutkan:\n• Nama lengkap\n• NIP\n• Tanggal pengajuan\n\nKetik *6* untuk menghubungi petugas."],
            ['trigger_keyword' => 'formulir', 'service_id' => 4, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Formulir Pengajuan TTE:*\n\nFormulir pengajuan TTE dapat diperoleh di:\n• Kantor Diskominfo Kab. Bengkayang\n• Melalui petugas langsung\n\nKetik *6* untuk menghubungi petugas."],
            ['trigger_keyword' => 'bantuan', 'service_id' => 4, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Bantuan/Gangguan TTE:*\n\nKendala umum:\n• TTE tidak bisa digunakan\n• Sertifikat expired\n• Error saat tanda tangan\n• Lupa passphrase\n\nKetik *6* untuk bantuan dari petugas teknis."],

            // === ALAT DAN OPERATOR ===
            ['trigger_keyword' => 'persyaratan', 'service_id' => 5, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Persyaratan Peminjaman Alat & Operator:*\n\n1. Surat permohonan resmi dari OPD\n2. Nama kegiatan dan deskripsi\n3. Tanggal, waktu, dan lokasi kegiatan\n4. Jenis alat yang dibutuhkan\n5. Penanggung jawab (nama & kontak)\n6. Pengajuan minimal H-3 hari kerja"],
            ['trigger_keyword' => 'prosedur', 'service_id' => 5, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Prosedur Peminjaman Alat:*\n\n1. Ajukan surat permohonan ke Diskominfo\n2. Cek ketersediaan alat dan operator\n3. Konfirmasi persetujuan\n4. Serah terima alat (H-1 atau hari H)\n5. Penggunaan sesuai ketentuan\n6. Pengembalian alat dalam kondisi baik\n\n⏱ Konfirmasi: 1-3 hari kerja"],
            ['trigger_keyword' => 'daftar_alat', 'service_id' => 5, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Daftar Alat yang Tersedia:*\n\n• Kamera DSLR + Lensa\n• Tripod & Stabilizer\n• Sound System (portable)\n• Microphone Wireless\n• Proyektor & Layar\n• Drone (untuk dokumentasi)\n• Laptop cadangan\n• Backdrop & Lighting\n\nKetersediaan tergantung jadwal. Ketik *6* untuk konfirmasi."],
            ['trigger_keyword' => 'formulir', 'service_id' => 5, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Formulir Pengajuan Peminjaman:*\n\nFormulir peminjaman alat dan operator dapat diminta langsung ke petugas Diskominfo.\n\nKetik *6* untuk menghubungi petugas."],
            ['trigger_keyword' => 'bantuan', 'service_id' => 5, 'match_type' => 'exact', 'priority' => 10,
             'response_text' => "📋 *Bantuan/Gangguan Alat:*\n\nJika mengalami kendala:\n• Alat rusak/bermasalah saat kegiatan\n• Operator tidak hadir\n• Perubahan jadwal mendadak\n\nKetik *6* untuk menghubungi petugas langsung."],
        ];

        foreach ($botResponses as $responseData) {
            BotResponse::create($responseData);
        }
    }
}
