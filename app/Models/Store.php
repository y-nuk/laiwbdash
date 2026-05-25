<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

    public const GBP_STATUS_UNSET = 'unset';
    public const GBP_STATUS_PENDING = 'pending';
    public const GBP_STATUS_CONFIRMED = 'confirmed';
    public const GBP_STATUS_ERROR = 'error';

    public const GBP_STATUSES = [
        self::GBP_STATUS_UNSET => '未設定',
        self::GBP_STATUS_PENDING => '確認中',
        self::GBP_STATUS_CONFIRMED => '連携済',
        self::GBP_STATUS_ERROR => 'エラー',
    ];

    public const BUSINESS_STATUSES = [
        'operational' => '営業中',
        'temporary_closed' => '臨時休業',
        'permanent_closed' => '閉業',
    ];

    public const WEEKDAYS = [
        'mon' => '月曜日',
        'tue' => '火曜日',
        'wed' => '水曜日',
        'thu' => '木曜日',
        'fri' => '金曜日',
        'sat' => '土曜日',
        'sun' => '日曜日',
    ];

    protected $fillable = [
        'company_id',
        'name',
        'business_name',
        'industry',
        'postal_code',
        'address',
        'phone',
        'gbp_place_id',
        'gbp_location_id',
        'has_gbp',
        'gbp_status',
        'has_yahoo',
        // GBP 基本情報
        'business_status',
        'primary_category',
        'additional_categories',
        'website_url',
        'reservation_url',
        'menu_url',
        'order_url',
        'service_areas',
        'business_hours',
        'special_hours',
        'description',
        'opening_date',
        'gbp_protected',
        'gbp_last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'has_gbp' => 'boolean',
            'has_yahoo' => 'boolean',
            'gbp_protected' => 'boolean',
            'additional_categories' => 'array',
            'service_areas' => 'array',
            'business_hours' => 'array',
            'special_hours' => 'array',
            'opening_date' => 'date',
            'gbp_last_synced_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function activeKeywords(): HasMany
    {
        return $this->hasMany(Keyword::class)->where('is_active', true);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }

    public function competitors(): HasMany
    {
        return $this->hasMany(Competitor::class)->orderBy('sort_order');
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function reportSchedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class);
    }

    /** 曜日別営業時間を取得（保存形式：['mon' => ['closed' => bool, 'open' => 'HH:MM', 'close' => 'HH:MM'], ...]）*/
    public function getHoursForDay(string $day): array
    {
        return $this->business_hours[$day] ?? ['closed' => false, 'open' => '09:00', 'close' => '18:00'];
    }
}
