<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'outside_temperature',
        'status',
        'is_auto',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
