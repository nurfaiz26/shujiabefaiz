<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ServiceTestimonial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'message',
        'photo',
        'home_service_id',
    ];

    public function homeService() : BelongsTo {
        return $this->belongsTo(HomeService::class);
    }
}
