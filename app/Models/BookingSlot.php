<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingSlot extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'start_time', 'end_time'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
