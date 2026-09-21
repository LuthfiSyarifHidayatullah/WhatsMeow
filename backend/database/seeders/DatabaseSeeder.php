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
            ['name' => 'Maya Anggraini', 'email' => 'maya@siap-bengkayang.go.id', 'service_code' => 'sosial_perempuan_anak'],
            ['name' => 'Rudi Hartono', 'email' => 'rudi@siap-bengkayang.go.id', 'service_code' => 'sosial_verval_dtks'],
            ['name' => 'Nia Ramadhani', 'email' => 'nia@siap-bengkayang.go.id', 'service_code' => 'sosial_bansos_pbijkn'],
            ['name' => 'Tono Sucipto', 'email' => 'tono@siap-bengkayang.go.id', 'service_code' => 'sosial_ppid'],
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
            // ---------- Dinas Pendidikan: PAUD ----------
            [
                'service_code' => 'pendidikan_paud',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Layanan PAUD — Informasi & Persyaratan*\n\nLayanan Pendidikan Anak Usia Dini (PAUD) diperuntukkan bagi anak usia dini (umumnya 2–6 tahun).\n\n*Persyaratan umum:*\n1. Fotokopi Akta Kelahiran anak\n2. Fotokopi Kartu Keluarga (KK)\n3. Fotokopi KTP orang tua/wali\n4. Pasfoto anak terbaru\n\n_Persyaratan dapat berbeda pada tiap satuan PAUD. Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'pendidikan_paud',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Layanan PAUD — Prosedur / Alur*\n\n1. Siapkan berkas persyaratan.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Unggah/serahkan berkas sesuai petunjuk pada formulir.\n4. Petugas memverifikasi berkas Anda.\n5. Petugas menghubungi Anda untuk proses selanjutnya.\n\n_Estimasi verifikasi: 3–5 hari kerja._",
            ],
            [
                'service_code' => 'pendidikan_paud',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan PAUD*\n\nSilakan isi formulir permohonan layanan Pendidikan Anak Usia Dini melalui link berikut:\n\n🔗 [LINK_GFORM_PAUD]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Pendidikan: Pendidikan Dasar ----------
            [
                'service_code' => 'pendidikan_dasar',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Pendidikan Dasar (SD/SMP) — Informasi & Persyaratan*\n\nLayanan pendidikan dasar diperuntukkan bagi anak usia wajib belajar (umumnya 7–15 tahun) jenjang SD dan SMP.\n\n*Persyaratan umum:*\n1. Fotokopi Akta Kelahiran\n2. Fotokopi Kartu Keluarga (KK)\n3. Fotokopi KTP orang tua/wali\n4. Ijazah/rapor jenjang sebelumnya (untuk perpindahan)\n\n_Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'pendidikan_dasar',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Pendidikan Dasar (SD/SMP) — Prosedur / Alur*\n\n1. Siapkan berkas persyaratan.\n2. Isi formulir permohonan (menu Formulir Permohonan).\n3. Serahkan berkas sesuai petunjuk pada formulir.\n4. Petugas memverifikasi berkas dan ketersediaan.\n5. Petugas menghubungi Anda untuk proses selanjutnya.\n\n_Estimasi verifikasi: 3–5 hari kerja._",
            ],
            [
                'service_code' => 'pendidikan_dasar',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan Pendidikan Dasar (SD/SMP)*\n\nSilakan isi formulir permohonan melalui link berikut:\n\n🔗 [LINK_GFORM_DIKDAS]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
            ],

            // ---------- Dinas Pendidikan: Kesetaraan ----------
            [
                'service_code' => 'pendidikan_kesetaraan',
                'trigger_keyword' => 'syarat',
                'response_text' => "ℹ️ *Pendidikan Kesetaraan — Informasi & Persyaratan*\n\nProgram kesetaraan (Paket A setara SD, Paket B setara SMP, Paket C setara SMA) bagi warga yang tidak menempuh pendidikan formal.\n\n*Persyaratan umum:*\n1. Fotokopi Kartu Keluarga (KK)\n2. Fotokopi KTP (bagi yang sudah memiliki)\n3. Ijazah terakhir yang dimiliki (bila ada)\n4. Pasfoto terbaru\n\n_Untuk detail, silakan pilih Prosedur atau hubungi petugas._",
            ],
            [
                'service_code' => 'pendidikan_kesetaraan',
                'trigger_keyword' => 'prosedur',
                'response_text' => "🧭 *Pendidikan Kesetaraan — Prosedur / Alur*\n\n1. Tentukan program yang dituju (Paket A/B/C).\n2. Siapkan berkas persyaratan.\n3. Isi formulir permohonan (menu Formulir Permohonan).\n4. Petugas memverifikasi dan mengarahkan ke PKBM/satuan terdekat.\n5. Petugas menghubungi Anda untuk proses selanjutnya.\n\n_Estimasi verifikasi: 3–5 hari kerja._",
            ],
            [
                'service_code' => 'pendidikan_kesetaraan',
                'trigger_keyword' => 'formulir',
                'response_text' => "📝 *Formulir Layanan Pendidikan Kesetaraan*\n\nSilakan isi formulir permohonan (Paket A/B/C) melalui link berikut:\n\n🔗 [LINK_GFORM_KESETARAAN]\n\n✅ *Setelah mengisi formulir, ketik konfirmasi untuk terhubung ke petugas.*",
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
