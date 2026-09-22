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
        // OPD (Organisasi Perangkat Daerah) - Sample Pengampu SPM
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
            [
                'name' => 'Dinas Sosial',
                'code' => 'sosial',
                'description' => 'Pengampu SPM Bidang Sosial dan layanan kesejahteraan sosial.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Dinas Komunikasi dan Informatika',
                'code' => 'kominfo',
                'description' => 'Layanan teknologi informasi dan komunikasi Pemerintah Kabupaten Bengkayang.',
                'sort_order' => 4,
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
                'name' => 'Data Dapodik',
                'code' => 'disdik_dapodik',
                'description' => 'Layanan terkait Data Pokok Pendidikan (Dapodik) untuk sekolah/operator.',
                'keywords' => ['dapodik', 'data pokok', 'npsn', 'data sekolah', 'operator dapodik'],
                'sort_order' => 1,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'Sertifikasi Guru',
                'code' => 'disdik_sertifikasi_guru',
                'description' => 'Layanan terkait sertifikasi guru (pendaftaran, informasi, tindak lanjut).',
                'keywords' => ['sertifikasi guru', 'sertifikasi', 'ppg', 'pendidikan profesi guru'],
                'sort_order' => 2,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'TPP Guru',
                'code' => 'disdik_tpp_guru',
                'description' => 'Layanan terkait Tunjangan Profesi/Penghasilan Pegawai (TPP) guru.',
                'keywords' => ['tpp', 'tunjangan guru', 'tunjangan profesi', 'tpp guru'],
                'sort_order' => 3,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'Revitalisasi Sekolah',
                'code' => 'disdik_revitalisasi',
                'description' => 'Layanan permohonan/informasi revitalisasi (rehabilitasi/pembangunan) sarana sekolah.',
                'keywords' => ['revitalisasi', 'rehab sekolah', 'renovasi sekolah', 'sarana prasarana'],
                'sort_order' => 4,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'Surat Keterangan Pengganti Ijazah (Hilang/Rusak)',
                'code' => 'disdik_ijazah',
                'description' => 'Layanan penerbitan surat keterangan pengganti ijazah yang hilang atau rusak.',
                'keywords' => ['ijazah', 'ijazah hilang', 'ijazah rusak', 'ganti ijazah', 'surat pengganti ijazah'],
                'sort_order' => 5,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'Surat Rekomendasi Pindah Sekolah',
                'code' => 'disdik_pindah_sekolah',
                'description' => 'Layanan penerbitan surat rekomendasi mutasi/pindah sekolah siswa.',
                'keywords' => ['pindah sekolah', 'mutasi siswa', 'rekomendasi pindah', 'mutasi sekolah'],
                'sort_order' => 6,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'Izin Pembangunan Sekolah Baru',
                'code' => 'disdik_izin_sekolah',
                'description' => 'Layanan permohonan izin pendirian/pembangunan sekolah baru.',
                'keywords' => ['izin sekolah', 'pendirian sekolah', 'sekolah baru', 'izin pembangunan sekolah'],
                'sort_order' => 7,
            ],
            [
                'opd_code' => 'pendidikan',
                'name' => 'Pengaduan Pelayanan Pendidikan',
                'code' => 'pendidikan_pengaduan',
                'description' => 'Kanal pengaduan atas pelayanan pendidikan di Kabupaten Bengkayang.',
                'keywords' => ['pengaduan pendidikan', 'keluhan sekolah', 'aduan pendidikan', 'lapor pendidikan'],
                'sort_order' => 8,
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

            // --- Dinas Sosial ---
            [
                'opd_code' => 'sosial',
                'name' => 'Pengaduan & Penanganan Kasus Perempuan dan Anak',
                'code' => 'sosial_perempuan_anak',
                'description' => 'Pengaduan dan penanganan kasus kekerasan/perlindungan terhadap perempuan dan anak.',
                'keywords' => ['perempuan', 'anak', 'kekerasan', 'kdrt', 'perlindungan anak', 'pengaduan perempuan'],
                'sort_order' => 1,
            ],
            [
                'opd_code' => 'sosial',
                'name' => 'Verifikasi & Validasi Data Kesejahteraan Sosial',
                'code' => 'sosial_verval_dtks',
                'description' => 'Layanan verifikasi dan validasi Data Terpadu Kesejahteraan Sosial (DTKS).',
                'keywords' => ['dtks', 'verifikasi data', 'validasi data', 'kesejahteraan sosial', 'data sosial'],
                'sort_order' => 2,
            ],
            [
                'opd_code' => 'sosial',
                'name' => 'Rekomendasi Bantuan Sosial & PBI-JKN',
                'code' => 'sosial_bansos_pbijkn',
                'description' => 'Rekomendasi bantuan sosial dan Penerima Bantuan Iuran Jaminan Kesehatan Nasional (PBI-JKN).',
                'keywords' => ['bantuan sosial', 'bansos', 'pbi', 'jkn', 'kis', 'bpjs gratis', 'rekomendasi bantuan'],
                'sort_order' => 3,
            ],
            [
                'opd_code' => 'sosial',
                'name' => 'Layanan Informasi Publik (PPID)',
                'code' => 'sosial_ppid',
                'description' => 'Layanan permohonan informasi publik melalui PPID Dinas Sosial.',
                'keywords' => ['ppid', 'informasi publik', 'permohonan informasi', 'keterbukaan informasi'],
                'sort_order' => 4,
            ],

            // --- Dinas Komunikasi dan Informatika ---
            [
                'opd_code' => 'kominfo',
                'name' => 'Domain Bengkayang.go.id',
                'code' => 'domain',
                'description' => 'Layanan pengajuan dan pengelolaan subdomain bengkayang.go.id untuk OPD.',
                'keywords' => ['domain', 'subdomain', 'bengkayang.go.id', 'website', 'hosting', 'dns'],
                'sort_order' => 1,
            ],
            [
                'opd_code' => 'kominfo',
                'name' => 'Zoom Meeting/Video Conference',
                'code' => 'zoom',
                'description' => 'Layanan peminjaman akun Zoom Meeting dan Video Conference untuk kegiatan dinas.',
                'keywords' => ['zoom', 'meeting', 'video conference', 'vicon', 'webinar', 'rapat online'],
                'sort_order' => 2,
            ],
            [
                'opd_code' => 'kominfo',
                'name' => 'Fasilitasi Dokumentasi Kegiatan',
                'code' => 'dokumentasi',
                'description' => 'Layanan pengajuan fasilitasi dokumentasi kegiatan OPD (foto, video, liputan).',
                'keywords' => ['dokumentasi', 'foto', 'video', 'liputan', 'fasilitasi', 'kegiatan'],
                'sort_order' => 3,
            ],
            [
                'opd_code' => 'kominfo',
                'name' => 'Tanda Tangan Elektronik (TTE)',
                'code' => 'tte',
                'description' => 'Layanan pengajuan dan penerbitan Tanda Tangan Elektronik untuk ASN.',
                'keywords' => ['tte', 'tanda tangan elektronik', 'digital signature', 'sertifikat elektronik', 'bsre'],
                'sort_order' => 4,
            ],
            [
                'opd_code' => 'kominfo',
                'name' => 'Alat dan Operator Kegiatan',
                'code' => 'alat',
                'description' => 'Layanan peminjaman alat dokumentasi, multimedia, dan operator untuk kegiatan dinas.',
                'keywords' => ['alat', 'operator', 'kamera', 'multimedia', 'sound system', 'peminjaman'],
                'sort_order' => 5,
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
            ['name' => 'Rina Kartika', 'email' => 'rina@siap-bengkayang.go.id', 'service_code' => 'disdik_dapodik'],
            ['name' => 'Bayu Nugroho', 'email' => 'bayu@siap-bengkayang.go.id', 'service_code' => 'disdik_sertifikasi_guru'],
            ['name' => 'Sari Wulandari', 'email' => 'sari@siap-bengkayang.go.id', 'service_code' => 'disdik_tpp_guru'],
            ['name' => 'Dedi Kurniawan', 'email' => 'dedi@siap-bengkayang.go.id', 'service_code' => 'disdik_revitalisasi'],
            ['name' => 'Wahyu Pratama', 'email' => 'wahyu@siap-bengkayang.go.id', 'service_code' => 'disdik_ijazah'],
            ['name' => 'Indah Permata', 'email' => 'indah@siap-bengkayang.go.id', 'service_code' => 'disdik_pindah_sekolah'],
            ['name' => 'Yoga Saputra', 'email' => 'yoga@siap-bengkayang.go.id', 'service_code' => 'disdik_izin_sekolah'],
            ['name' => 'Citra Dewanti', 'email' => 'citra@siap-bengkayang.go.id', 'service_code' => 'pendidikan_pengaduan'],
            ['name' => 'Agus Salim', 'email' => 'agus@siap-bengkayang.go.id', 'service_code' => 'satpolpp_pengaduan_trantibum'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra@siap-bengkayang.go.id', 'service_code' => 'satpolpp_pengamanan'],
            ['name' => 'Lestari Ningsih', 'email' => 'lestari@siap-bengkayang.go.id', 'service_code' => 'satpolpp_linmas'],
            ['name' => 'Fajar Ramadhan', 'email' => 'fajar@siap-bengkayang.go.id', 'service_code' => 'satpolpp_pengaduan'],
            ['name' => 'Maya Anggraini', 'email' => 'maya@siap-bengkayang.go.id', 'service_code' => 'sosial_perempuan_anak'],
            ['name' => 'Rudi Hartono', 'email' => 'rudi@siap-bengkayang.go.id', 'service_code' => 'sosial_verval_dtks'],
            ['name' => 'Nia Ramadhani', 'email' => 'nia@siap-bengkayang.go.id', 'service_code' => 'sosial_bansos_pbijkn'],
            ['name' => 'Tono Sucipto', 'email' => 'tono@siap-bengkayang.go.id', 'service_code' => 'sosial_ppid'],
            ['name' => 'Budi Santoso', 'email' => 'budi@siap-bengkayang.go.id', 'service_code' => 'domain'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@siap-bengkayang.go.id', 'service_code' => 'zoom'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@siap-bengkayang.go.id', 'service_code' => 'dokumentasi'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@siap-bengkayang.go.id', 'service_code' => 'tte'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko@siap-bengkayang.go.id', 'service_code' => 'alat'],
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
        // BOT RESPONSES: konten sub-menu tiap layanan.
        //   - trigger_keyword 'syarat'   => Informasi Layanan & Persyaratan
        //   - trigger_keyword 'prosedur' => Prosedur / Alur Pengajuan
        //   - trigger_keyword 'formulir' => Link Google Form
        //   - trigger_keyword 'info'     => Informasi (layanan pengaduan/linmas)
        //
        // CATATAN: Teks di bawah adalah DRAFT dan dapat diedit sewaktu-waktu
        // dari dashboard menu "Respons Bot". Ganti [LINK_GFORM_xxx] dengan
        // link Google Form yang sebenarnya.
        // =====================================================
        $botResponses = [
            // ---------- Dinas Pendidikan: Data Dapodik ----------
            [
                'service_code' => 'disdik_dapodik',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Data Dapodik — Informasi & Persyaratan*\n\nLayanan terkait Data Pokok Pendidikan (Dapodik) untuk sekolah/operator (pemutakhiran data, kendala sinkronisasi, dsb).\n\n*Persyaratan umum:*\n1. NPSN sekolah\n2. Nama & data operator sekolah\n3. Surat tugas operator (bila diperlukan)\n\n_Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'disdik_dapodik',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Data Dapodik — Prosedur / Alur*\n\n1. Siapkan data sekolah & operator.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Petugas memverifikasi & menindaklanjuti kendala Dapodik.\n4. Petugas menghubungi Anda untuk proses selanjutnya.",
            ],
            [
                'service_code' => 'disdik_dapodik',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan Data Dapodik*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_DAPODIK]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Pendidikan: Sertifikasi Guru ----------
            [
                'service_code' => 'disdik_sertifikasi_guru',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Sertifikasi Guru — Informasi & Persyaratan*\n\nLayanan informasi & tindak lanjut sertifikasi guru (termasuk PPG).\n\n*Persyaratan umum:*\n1. Fotokopi KTP\n2. NUPTK / NIP (bila ada)\n3. SK mengajar / surat tugas\n4. Ijazah terakhir\n\n_Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'disdik_sertifikasi_guru',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Sertifikasi Guru — Prosedur / Alur*\n\n1. Siapkan berkas persyaratan.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Petugas memverifikasi kelengkapan & kelayakan.\n4. Petugas menghubungi Anda untuk proses selanjutnya.",
            ],
            [
                'service_code' => 'disdik_sertifikasi_guru',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan Sertifikasi Guru*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_SERTIFIKASI_GURU]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Pendidikan: TPP Guru ----------
            [
                'service_code' => 'disdik_tpp_guru',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *TPP Guru — Informasi & Persyaratan*\n\nLayanan terkait Tunjangan Profesi/Penghasilan Pegawai (TPP) guru.\n\n*Persyaratan umum:*\n1. Fotokopi KTP\n2. NIP/NUPTK\n3. SK & data kepegawaian\n4. Dokumen pendukung tunjangan\n\n_Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'disdik_tpp_guru',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *TPP Guru — Prosedur / Alur*\n\n1. Siapkan berkas persyaratan.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Petugas memverifikasi data kepegawaian & kelayakan.\n4. Petugas menghubungi Anda untuk proses selanjutnya.",
            ],
            [
                'service_code' => 'disdik_tpp_guru',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan TPP Guru*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_TPP_GURU]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Pendidikan: Revitalisasi Sekolah ----------
            [
                'service_code' => 'disdik_revitalisasi',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Revitalisasi Sekolah — Informasi & Persyaratan*\n\nLayanan permohonan/informasi revitalisasi (rehabilitasi/pembangunan) sarana & prasarana sekolah.\n\n*Persyaratan umum:*\n1. Data sekolah (NPSN)\n2. Proposal/usulan revitalisasi\n3. Foto kondisi sarana yang diusulkan\n4. Surat pengantar dari sekolah\n\n_Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'disdik_revitalisasi',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Revitalisasi Sekolah — Prosedur / Alur*\n\n1. Siapkan proposal & data pendukung.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Petugas melakukan verifikasi/peninjauan.\n4. Petugas menghubungi Anda untuk proses selanjutnya.",
            ],
            [
                'service_code' => 'disdik_revitalisasi',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan Revitalisasi Sekolah*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_REVITALISASI]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Pendidikan: Surat Pengganti Ijazah ----------
            [
                'service_code' => 'disdik_ijazah',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Surat Keterangan Pengganti Ijazah — Informasi & Persyaratan*\n\nLayanan penerbitan surat keterangan pengganti ijazah yang hilang atau rusak.\n\n*Persyaratan umum:*\n1. Fotokopi KTP pemohon\n2. Surat keterangan kehilangan dari Kepolisian (untuk ijazah hilang)\n3. Ijazah yang rusak (untuk ijazah rusak)\n4. Fotokopi ijazah lama bila masih ada\n5. Pasfoto terbaru\n\n_Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'disdik_ijazah',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Surat Keterangan Pengganti Ijazah — Prosedur / Alur*\n\n1. Siapkan berkas persyaratan (termasuk surat kehilangan bila hilang).\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Petugas memverifikasi data ke arsip/sekolah asal.\n4. Surat keterangan pengganti diterbitkan.\n5. Petugas menghubungi Anda untuk pengambilan.",
            ],
            [
                'service_code' => 'disdik_ijazah',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Surat Keterangan Pengganti Ijazah*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_IJAZAH]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Pendidikan: Rekomendasi Pindah Sekolah ----------
            [
                'service_code' => 'disdik_pindah_sekolah',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Surat Rekomendasi Pindah Sekolah — Informasi & Persyaratan*\n\nLayanan penerbitan surat rekomendasi mutasi/pindah sekolah siswa.\n\n*Persyaratan umum:*\n1. Fotokopi KK & KTP orang tua\n2. Surat keterangan pindah dari sekolah asal\n3. Rapor terakhir siswa\n4. Data sekolah tujuan\n\n_Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'disdik_pindah_sekolah',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Surat Rekomendasi Pindah Sekolah — Prosedur / Alur*\n\n1. Siapkan berkas persyaratan.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Petugas memverifikasi data siswa & sekolah tujuan.\n4. Surat rekomendasi diterbitkan.\n5. Petugas menghubungi Anda untuk proses selanjutnya.",
            ],
            [
                'service_code' => 'disdik_pindah_sekolah',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Surat Rekomendasi Pindah Sekolah*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_PINDAH_SEKOLAH]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Pendidikan: Izin Pembangunan Sekolah Baru ----------
            [
                'service_code' => 'disdik_izin_sekolah',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Izin Pembangunan Sekolah Baru — Informasi & Persyaratan*\n\nLayanan permohonan izin pendirian/pembangunan sekolah baru.\n\n*Persyaratan umum:*\n1. Proposal pendirian sekolah\n2. Data yayasan/penyelenggara\n3. Bukti kepemilikan/penguasaan lahan\n4. Studi kelayakan & dokumen pendukung\n\n_Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'disdik_izin_sekolah',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Izin Pembangunan Sekolah Baru — Prosedur / Alur*\n\n1. Siapkan proposal & dokumen pendukung.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Petugas melakukan verifikasi & peninjauan lapangan.\n4. Petugas menghubungi Anda untuk proses selanjutnya.",
            ],
            [
                'service_code' => 'disdik_izin_sekolah',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Izin Pembangunan Sekolah Baru*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_IZIN_SEKOLAH]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Pendidikan: Pengaduan ----------
            [
                'service_code' => 'pendidikan_pengaduan',
                'trigger_keyword' => 'info',
                'response_text' => "ℹ️ *Pengaduan Pelayanan Pendidikan — Informasi*\n\nKanal ini untuk menyampaikan keluhan/pengaduan terkait pelayanan pendidikan di Kabupaten Bengkayang.\n\n*Agar pengaduan cepat ditindaklanjuti, siapkan:*\n1. Uraian singkat masalah\n2. Nama sekolah/lokasi kejadian\n3. Waktu kejadian\n4. Bukti pendukung bila ada (foto/dokumen)\n\n_Setelah siap, pilih menu Sampaikan Pengaduan ke Petugas._",
            ],

            // ---------- Satpol PP: Pengaduan Ketertiban Umum ----------
            [
                'service_code' => 'satpolpp_pengaduan_trantibum',
                'trigger_keyword' => 'info',
                'response_text' => "ℹ️ *Pengaduan Gangguan Ketertiban Umum — Informasi*\n\nKanal ini untuk melaporkan gangguan ketertiban umum, seperti kebisingan, PKL liar, atau bangunan tanpa izin.\n\n*Agar cepat ditindaklanjuti, siapkan:*\n1. Jenis gangguan\n2. Lokasi/alamat kejadian\n3. Waktu kejadian\n4. Bukti pendukung bila ada (foto/video)\n\n_Untuk kejadian darurat, tetap hubungi aparat keamanan terdekat. Setelah siap, pilih menu Sampaikan Pengaduan ke Petugas._",
            ],

            // ---------- Satpol PP: Bantuan Pengamanan ----------
            [
                'service_code' => 'satpolpp_pengamanan',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Bantuan Pengamanan Kegiatan — Informasi & Persyaratan*\n\nLayanan bantuan pengamanan Satpol PP untuk kegiatan/keramaian resmi.\n\n*Persyaratan umum:*\n1. Surat permohonan resmi dari penyelenggara\n2. Detail kegiatan (nama, tanggal, lokasi, estimasi peserta)\n3. Data narahubung penanggung jawab\n4. Izin keramaian bila diperlukan\n\n_Ajukan minimal H-3 hari kerja sebelum kegiatan._",
            ],
            [
                'service_code' => 'satpolpp_pengamanan',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Bantuan Pengamanan Kegiatan — Prosedur / Alur*\n\n1. Siapkan surat permohonan & detail kegiatan.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Petugas menelaah permohonan dan ketersediaan personel.\n4. Petugas mengonfirmasi kesiapan pengamanan.\n5. Pelaksanaan pengamanan pada hari kegiatan.\n\n_Ajukan minimal H-3 hari kerja sebelum kegiatan._",
            ],
            [
                'service_code' => 'satpolpp_pengamanan',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Permohonan Bantuan Pengamanan Kegiatan*\n\nSilakan isi formulir permohonan pengamanan melalui link berikut:\n\n🔗 [LINK_GFORM_PENGAMANAN]\n\nPastikan mengajukan minimal H-3 hari kerja sebelum kegiatan.\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Satpol PP: Linmas ----------
            [
                'service_code' => 'satpolpp_linmas',
                'trigger_keyword' => 'info',
                'response_text' => "ℹ️ *Perlindungan Masyarakat (Linmas) — Informasi*\n\nSatuan Perlindungan Masyarakat (Satlinmas) membantu penanganan ketentraman, ketertiban, serta penanggulangan bencana dan kegiatan sosial kemasyarakatan.\n\n*Informasi yang dapat ditanyakan:*\n1. Pendaftaran/keanggotaan anggota Linmas\n2. Permintaan bantuan Linmas untuk kegiatan\n3. Informasi pembinaan Linmas\n\n_Untuk pertanyaan lebih lanjut, pilih menu Hubungi Petugas._",
            ],

            // ---------- Satpol PP: Pengaduan Pelayanan ----------
            [
                'service_code' => 'satpolpp_pengaduan',
                'trigger_keyword' => 'info',
                'response_text' => "ℹ️ *Pengaduan Pelayanan Satpol PP — Informasi*\n\nKanal ini untuk menyampaikan keluhan/pengaduan terkait pelayanan Satuan Polisi Pamong Praja.\n\n*Agar pengaduan cepat ditindaklanjuti, siapkan:*\n1. Uraian singkat masalah\n2. Lokasi/waktu kejadian\n3. Bukti pendukung bila ada\n\n_Setelah siap, pilih menu Sampaikan Pengaduan ke Petugas._",
            ],

            // ---------- Dinas Sosial: Perempuan & Anak (sensitif → escalate) ----------
            [
                'service_code' => 'sosial_perempuan_anak',
                'trigger_keyword' => 'info',
                'response_text' => "ℹ️ *Pengaduan & Penanganan Kasus Perempuan dan Anak*\n\nLayanan ini untuk pengaduan dan penanganan kasus kekerasan atau yang membutuhkan perlindungan terhadap perempuan dan anak. Kerahasiaan Anda kami jaga. 🤝\n\n*Jika dalam kondisi darurat/mengancam jiwa, segera hubungi Kepolisian (110).*\n\nUntuk penanganan lebih lanjut, petugas kami siap membantu. Pilih menu *Hubungi Petugas* untuk terhubung langsung.",
            ],

            // ---------- Dinas Sosial: Verval DTKS ----------
            [
                'service_code' => 'sosial_verval_dtks',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Verifikasi & Validasi Data Kesejahteraan Sosial (DTKS) — Informasi & Persyaratan*\n\nLayanan untuk mendaftar/memperbarui data pada Data Terpadu Kesejahteraan Sosial (DTKS).\n\n*Persyaratan umum:*\n1. Fotokopi KTP\n2. Fotokopi Kartu Keluarga (KK)\n3. Surat keterangan dari desa/kelurahan\n\n_Untuk detail, pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'sosial_verval_dtks',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Verifikasi & Validasi DTKS — Prosedur / Alur*\n\n1. Siapkan berkas persyaratan.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Data Anda diverifikasi oleh petugas & pihak desa/kelurahan.\n4. Hasil verval dimutakhirkan ke dalam DTKS.\n5. Petugas menghubungi Anda untuk proses selanjutnya.",
            ],
            [
                'service_code' => 'sosial_verval_dtks',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Verifikasi & Validasi DTKS*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_VERVAL_DTKS]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Sosial: Bansos & PBI-JKN ----------
            [
                'service_code' => 'sosial_bansos_pbijkn',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Rekomendasi Bantuan Sosial & PBI-JKN — Informasi & Persyaratan*\n\nLayanan rekomendasi bantuan sosial dan pendaftaran Penerima Bantuan Iuran Jaminan Kesehatan Nasional (PBI-JKN).\n\n*Persyaratan umum:*\n1. Fotokopi KTP\n2. Fotokopi Kartu Keluarga (KK)\n3. Terdaftar dalam DTKS\n4. Surat keterangan tidak mampu (bila diperlukan)\n\n_Untuk detail, pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'sosial_bansos_pbijkn',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Rekomendasi Bantuan Sosial & PBI-JKN — Prosedur / Alur*\n\n1. Pastikan Anda terdaftar dalam DTKS.\n2. Siapkan berkas persyaratan.\n3. Isi formulir permohonan (menu Formulir Permohonan).\n4. Petugas memverifikasi kelayakan penerima.\n5. Petugas menerbitkan rekomendasi & menghubungi Anda.",
            ],
            [
                'service_code' => 'sosial_bansos_pbijkn',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Rekomendasi Bantuan Sosial & PBI-JKN*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_BANSOS_PBIJKN]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Sosial: PPID ----------
            [
                'service_code' => 'sosial_ppid',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Layanan Informasi Publik (PPID) — Informasi & Persyaratan*\n\nLayanan permohonan informasi publik melalui Pejabat Pengelola Informasi dan Dokumentasi (PPID) Dinas Sosial.\n\n*Persyaratan umum:*\n1. Fotokopi KTP pemohon\n2. Rincian informasi yang dimohon\n3. Tujuan penggunaan informasi\n\n_Untuk detail, pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'sosial_ppid',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Layanan Informasi Publik (PPID) — Prosedur / Alur*\n\n1. Siapkan identitas & rincian informasi yang dimohon.\n2. Isi formulir permohonan informasi (menu Formulir Permohonan).\n3. PPID memproses permohonan sesuai ketentuan (maks. 10 hari kerja, dapat diperpanjang).\n4. Informasi diberikan atau disertai penjelasan bila dikecualikan.\n5. Petugas menghubungi Anda untuk proses selanjutnya.",
            ],
            [
                'service_code' => 'sosial_ppid',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Permohonan Informasi Publik (PPID)*\n\nSilakan isi formulir melalui link berikut:\n\n🔗 [LINK_GFORM_PPID]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Kominfo ----------
            [
                'service_code' => 'domain',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Pengajuan Domain*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_DOMAIN]\n\nSetelah mengisi formulir, petugas akan memproses pengajuan Anda dalam 3-5 hari kerja.\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],
            [
                'service_code' => 'zoom',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Pengajuan Zoom Meeting*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_ZOOM]\n\nPastikan mengajukan minimal H-2 hari kerja sebelum kegiatan.\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],
            [
                'service_code' => 'dokumentasi',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Pengajuan Fasilitasi Dokumentasi*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_DOKUMENTASI]\n\nPastikan mengajukan minimal H-3 hari kerja sebelum kegiatan.\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],
            [
                'service_code' => 'tte',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Pengajuan TTE*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_TTE]\n\nPastikan melengkapi persyaratan dokumen yang diperlukan.\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],
            [
                'service_code' => 'alat',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Pengajuan Peminjaman Alat & Operator*\n\nSilakan isi formulir pengajuan melalui link berikut:\n\n🔗 [LINK_GFORM_ALAT]\n\nPastikan mengajukan minimal H-3 hari kerja sebelum kegiatan.\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
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
