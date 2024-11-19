<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_trx_id',
        'name',
        'phone',
        'email',
        'started_time',
        'schedule_at',
        'proof',
        'post_code',
        'city',
        'address',
        'sub_total',
        'total_amount',
        'total_tax_amount',
        'is_paid',
    ];

    public static function generateUniqueTrxId() {
        $prefix = 'SHUJIA';
        do {
            $randomString = $prefix . mt_rand(1000, 9999);
        } while (self::where('booking_trx_id', $randomString)->exists());
    }

    public function transactionDetails() : HasMany {
        return $this->hasMany(TransactionDetails::class);
    }
}
