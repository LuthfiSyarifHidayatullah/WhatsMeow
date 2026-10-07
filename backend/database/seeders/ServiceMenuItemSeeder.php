<?php

namespace Database\Seeders;

use App\Models\BotResponse;
use App\Models\Service;
use App\Models\ServiceMenuItem;
use Illuminate\Database\Seeder;

/**
 * Memindahkan definisi sub-menu yang dulu hard-coded di
 * ChatbotService::$serviceMenus ke tabel service_menu_items, agar dapat
 * dikelola dari dashboard.
 *
 * Idempotent: aman dijalankan berulang. Untuk tiap layanan yang BELUM punya
 * item sub-menu, seeder ini membuatnya. Layanan yang sudah punya item (mis.
 * dikelola manual dari dashboard) dilewati agar tidak menimpa.
 *
 * Teks untuk item info/formulir diambil dari tabel bot_responses (yang sudah
 * di-seed DatabaseSeeder) berdasarkan 'key'. Jika tidak ada, response_text
 * dibiarkan null dan chatbot akan memakai teks default.
 */
class ServiceMenuItemSeeder extends Seeder
{
    /**
     * Struktur menu lama, disalin dari ChatbotService::$serviceMenus.
     * key => dipakai untuk mencari teks di bot_responses.
     */
    private array $serviceMenus = [
        // === Dinas Pendidikan ===
        'disdik_dapodik' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'disdik_sertifikasi_guru' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'disdik_tpp_guru' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'disdik_revitalisasi' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'disdik_ijazah' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'disdik_pindah_sekolah' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'disdik_izin_sekolah' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'pendidikan_pengaduan' => [
            1 => ['label' => 'Informasi Cara Pengaduan', 'action' => 'info', 'key' => 'info'],
            2 => ['label' => 'Sampaikan Pengaduan ke Petugas', 'action' => 'escalate'],
        ],

        // === Satuan Polisi Pamong Praja ===
        'satpolpp_pengaduan_trantibum' => [
            1 => ['label' => 'Informasi Cara Pengaduan', 'action' => 'info', 'key' => 'info'],
            2 => ['label' => 'Sampaikan Pengaduan ke Petugas', 'action' => 'escalate'],
        ],
        'satpolpp_pengamanan' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'satpolpp_linmas' => [
            1 => ['label' => 'Informasi Linmas', 'action' => 'info', 'key' => 'info'],
            2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'satpolpp_pengaduan' => [
            1 => ['label' => 'Informasi Cara Pengaduan', 'action' => 'info', 'key' => 'info'],
            2 => ['label' => 'Sampaikan Pengaduan ke Petugas', 'action' => 'escalate'],
        ],

        // === Dinas Sosial ===
        'sosial_perempuan_anak' => [
            1 => ['label' => 'Informasi Layanan', 'action' => 'info', 'key' => 'info'],
            2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'sosial_verval_dtks' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'sosial_bansos_pbijkn' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'sosial_ppid' => [
            1 => ['label' => 'Informasi Layanan & Persyaratan', 'action' => 'info', 'key' => 'syarat'],
            2 => ['label' => 'Prosedur / Alur Pengajuan', 'action' => 'info', 'key' => 'prosedur'],
            3 => ['label' => 'Formulir Permohonan', 'action' => 'formulir_then_escalate', 'key' => 'formulir'],
            4 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],

        // === Dinas Komunikasi dan Informatika ===
        'domain' => [
            1 => ['label' => 'Informasi Pengajuan', 'action' => 'info', 'key' => 'info'],
            2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'kominfo_permohonan_informasi' => [
            1 => ['label' => 'Informasi Pengajuan', 'action' => 'info', 'key' => 'info'],
            2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'tte' => [
            1 => ['label' => 'Informasi Pengajuan', 'action' => 'info', 'key' => 'info'],
            2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
        'kominfo_sp4n' => [
            1 => ['label' => 'Informasi Pengajuan', 'action' => 'info', 'key' => 'info'],
            2 => ['label' => 'Hubungi Petugas', 'action' => 'escalate'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->serviceMenus as $serviceCode => $items) {
            $service = Service::where('code', $serviceCode)->first();
            if (!$service) {
                continue; // layanan belum ada di DB → lewati
            }

            // Lewati jika layanan sudah punya item (jangan timpa data dashboard).
            if (ServiceMenuItem::where('service_id', $service->id)->exists()) {
                continue;
            }

            foreach ($items as $position => $item) {
                $responseText = null;
                if (!empty($item['key'])) {
                    $responseText = BotResponse::where('service_id', $service->id)
                        ->where('trigger_keyword', $item['key'])
                        ->where('is_active', true)
                        ->value('response_text');
                }

                ServiceMenuItem::create([
                    'service_id' => $service->id,
                    'position' => $position,
                    'label' => $item['label'],
                    'action' => $item['action'],
                    'response_text' => $responseText,
                    'is_active' => true,
                    'sort_order' => $position,
                ]);
            }
        }
    }
}
