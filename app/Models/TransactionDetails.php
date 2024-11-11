<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionDetails extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'price',
        'booking_transaction_id',
        'home_service_id',
    ];

    public function bookingTransactionId(): BelongsTo
    {
        return $this->belongsTo(BookingTransaction::class);
    }

    public function homeService(): BelongsTo
    {
        return $this->belongsTo(HomeService::class);
    }
}
