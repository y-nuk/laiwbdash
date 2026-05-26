<?php

namespace App\Services\Gbp;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Google Business Profile API の生クライアント。
 *
 * 各エンドポイントは段階的に増やしていく。Phase 4 承認後に下記から順次有効化：
 *  - accounts.list           （My Business Account Management API）
 *  - locations.list          （My Business Business Information API、accountId 必要）
 *  - locations.get / patch
 *  - reviews.list / reviews.reply
 *  - localPosts.list / create / patch
 *  - media.list / upload
 *  - performance（インサイト）
 *
 * 認証はユーザーごとに保存された OAuth トークンを使用。
 * トークン期限切れ時は refresh_token で自動更新する。
 */
class GbpClient
{
    private const ACCOUNTS_BASE = 'https://mybusinessaccountmanagement.googleapis.com/v1';

    private const BUSINESS_INFO_BASE = 'https://mybusinessbusinessinformation.googleapis.com/v1';

    private const REVIEWS_BASE = 'https://mybusiness.googleapis.com/v4'; // v4 のみ reviews 残存

    public function __construct(
        private readonly User $user,
    ) {}

    /**
     * GBP アカウント一覧（My Business Account Management API）
     *
     * @return array<int, array{name:string, accountName:string, type:string}>
     */
    public function listAccounts(): array
    {
        $res = $this->get(self::ACCOUNTS_BASE . '/accounts');
        return $res->json('accounts', []);
    }

    /**
     * 指定アカウント配下のロケーション一覧（My Business Business Information API）
     *
     * @param  string  $accountName  例: "accounts/12345"
     * @param  string  $readMask  取得フィールド（カンマ区切り）
     * @return array<int, array>
     */
    public function listLocations(string $accountName, string $readMask = 'name,title,storeCode,categories,phoneNumbers,storefrontAddress,websiteUri,regularHours'): array
    {
        $url = self::BUSINESS_INFO_BASE . '/' . trim($accountName, '/') . '/locations';
        $res = $this->get($url, ['readMask' => $readMask, 'pageSize' => 100]);
        return $res->json('locations', []);
    }

    /**
     * 単一ロケーションの取得。
     *
     * @param  string  $locationName  例: "locations/12345"
     */
    public function getLocation(string $locationName, string $readMask = '*'): array
    {
        $url = self::BUSINESS_INFO_BASE . '/' . trim($locationName, '/');
        $res = $this->get($url, ['readMask' => $readMask]);
        return $res->json();
    }

    /**
     * ロケーション情報を部分更新。
     *
     * @param  string  $locationName  例: "locations/12345"
     * @param  array  $data  更新内容
     * @param  string  $updateMask  更新するフィールド（カンマ区切り）
     */
    public function patchLocation(string $locationName, array $data, string $updateMask): array
    {
        $url = self::BUSINESS_INFO_BASE . '/' . trim($locationName, '/') . '?updateMask=' . urlencode($updateMask);
        return $this->request('PATCH', $url, $data)->json();
    }

    /**
     * クチコミ一覧取得（v4 のみ提供されているため特別な base url）。
     *
     * @param  string  $accountName  "accounts/12345"
     * @param  string  $locationName  "locations/67890"
     */
    public function listReviews(string $accountName, string $locationName, int $pageSize = 50): array
    {
        $url = self::REVIEWS_BASE . '/' . trim($accountName, '/') . '/' . trim($locationName, '/') . '/reviews';
        $res = $this->get($url, ['pageSize' => $pageSize, 'orderBy' => 'updateTime desc']);
        return $res->json('reviews', []);
    }

    /**
     * クチコミに返信。
     */
    public function replyReview(string $reviewName, string $comment): array
    {
        $url = self::REVIEWS_BASE . '/' . trim($reviewName, '/') . '/reply';
        return $this->request('PUT', $url, ['comment' => $comment])->json();
    }

    // ---------- 内部実装 ----------

    private function get(string $url, array $query = []): Response
    {
        return $this->request('GET', $url, [], $query);
    }

    private function request(string $method, string $url, array $body = [], array $query = []): Response
    {
        $token = $this->getValidAccessToken();
        $req = Http::withToken($token)
            ->acceptJson()
            ->withHeaders(['Content-Type' => 'application/json']);

        if ($query) {
            $req = $req->withQueryParameters($query);
        }

        $res = match (strtoupper($method)) {
            'GET' => $req->get($url),
            'POST' => $req->post($url, $body),
            'PUT' => $req->put($url, $body),
            'PATCH' => $req->patch($url, $body),
            'DELETE' => $req->delete($url),
            default => throw new RuntimeException("Unsupported method: {$method}"),
        };

        if ($res->failed()) {
            throw new RuntimeException(
                "GBP API call failed ({$res->status()}): " . $res->body(),
            );
        }

        return $res;
    }

    /**
     * 有効なアクセストークンを返す。期限切れなら自動リフレッシュ。
     */
    private function getValidAccessToken(): string
    {
        if (! $this->user->gbp_access_token) {
            throw new RuntimeException('ユーザーが GBP 連携していません。');
        }

        if ($this->user->isGbpTokenExpired() && $this->user->gbp_refresh_token) {
            $this->refreshAccessToken();
        }

        return $this->user->gbp_access_token;
    }

    private function refreshAccessToken(): void
    {
        $res = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $this->user->gbp_refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($res->failed()) {
            throw new RuntimeException('Token refresh failed: ' . $res->body());
        }

        $data = $res->json();
        $this->user->forceFill([
            'gbp_access_token' => $data['access_token'],
            'gbp_token_expires_at' => CarbonImmutable::now()->addSeconds($data['expires_in'] ?? 3600),
        ])->save();
    }
}
