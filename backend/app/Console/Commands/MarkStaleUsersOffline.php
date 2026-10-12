<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MarkStaleUsersOffline extends Command
{
    protected $signature = 'users:mark-stale-offline';
    protected $description = 'Tandai petugas/admin yang is_online=true namun tidak mengirim heartbeat (last_seen_at usang) menjadi offline';

    /**
     * Ambang (menit) tanpa heartbeat sebelum pengguna dianggap offline.
     * Dibuat sedikit lebih longgar dari PRESENCE_FRESHNESS_MINUTES agar
     * perubahan status tidak berkedip-kedip saat jaringan sesaat tersendat.
     */
    private const STALE_AFTER_MINUTES = 5;

    public function handle(): void
    {
        $threshold = now()->subMinutes(self::STALE_AFTER_MINUTES);

        $staleUsers = User::where('is_online', true)
            ->where(function ($q) use ($threshold) {
                $q->whereNull('last_seen_at')
                  ->orWhere('last_seen_at', '<', $threshold);
            })
            ->get();

        foreach ($staleUsers as $user) {
            $user->update(['is_online' => false]);
            $this->info("Offline (stale): {$user->name} (#{$user->id})");
        }

        if ($staleUsers->isEmpty()) {
            $this->info('Tidak ada pengguna usang.');
        }
    }
}
