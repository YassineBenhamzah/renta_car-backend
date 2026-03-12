<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;
    protected $guarded = []; // Allows mass assignment for all fields
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}
