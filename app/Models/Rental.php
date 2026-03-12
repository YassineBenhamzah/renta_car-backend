<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user() // The client
    {
        return $this->belongsTo(User::class);
    }
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function approver() // The agent/admin
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
