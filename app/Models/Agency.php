<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'kana',
        'is_self',
        'address',
        'phone',
        'email',
        'logo_path',
    ];

    protected function casts(): array
    {
        return [
            'is_self' => 'boolean',
        ];
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
