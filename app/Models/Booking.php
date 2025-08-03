<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    /**
    * Получить пользователя, которому принадлежит бронирование
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
    * Получить все слоты бронирования
    */
    public function slots()
    {
        return $this->hasMany(BookingSlot::class);
    }
}
