<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Builder;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'store_name',
        'store_address',
        'store_phone',
        'cover_image',
    ];

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'integer';


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

     public function scopeEligibleForPayout(Builder $query) : Builder
    {
        return $query->where('status', VendorStatusEnum::Approved);
    }
}
