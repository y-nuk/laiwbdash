<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Gbp\GbpClient;
use Illuminate\Console\Command;
use Throwable;

class GbpTest extends Command
{
    protected $signature = 'gbp:test
                            {--user= : テストする user_id（省略時は admin@laiweb-dash.com）}
                            {--list-locations : 最初のアカウントの locations 一覧も取得}';

    protected $description = '連携済みユーザーで GBP API を叩いて疎通確認（accounts.list / locations.list）';

    public function handle(): int
    {
        $userId = $this->option('user');
        $user = $userId
            ? User::findOrFail($userId)
            : User::where('email', config('mail.from.address') ?: 'admin@laiweb-dash.com')
                ->orWhere('role', 'admin')
                ->first();

        if (! $user) {
            $this->error('ユーザーが見つかりません。--user=ID で指定してください。');
            return self::FAILURE;
        }

        $this->info("ユーザー: {$user->email} (ID {$user->id})");

        if (! $user->hasGbpConnected()) {
            $this->error('このユーザーは GBP 連携していません。/admin/gbp/connect から連携してください。');
            return self::FAILURE;
        }

        $this->info("連携先: {$user->gbp_account_email}");
        $this->info("トークン期限: " . ($user->gbp_token_expires_at?->format('Y-m-d H:i') ?? '無期限'));

        $client = new GbpClient($user);

        try {
            $this->info('--- accounts.list ---');
            $accounts = $client->listAccounts();
            $this->info('accounts: ' . count($accounts));
            foreach ($accounts as $a) {
                $this->line("  - {$a['name']} : " . ($a['accountName'] ?? '?') . " ({$a['type']})");
            }

            if ($this->option('list-locations') && count($accounts) > 0) {
                $first = $accounts[0]['name'];
                $this->info("--- locations.list ({$first}) ---");
                $locations = $client->listLocations($first);
                $this->info('locations: ' . count($locations));
                foreach (array_slice($locations, 0, 5) as $l) {
                    $this->line("  - {$l['name']} : " . ($l['title'] ?? '?'));
                }
                if (count($locations) > 5) {
                    $this->line('  ... 残り ' . (count($locations) - 5) . ' 件');
                }
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('API エラー: ' . $e->getMessage());
            $this->line('（GBP API アクセス申請がまだ承認されていない場合は 403 が返ります）');
            return self::FAILURE;
        }
    }
}
