<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    use HasFactory;

    protected $appends = ['creator'];

    public function getCreatorAttribute() {
        $user = User::find($this->user_id);
        return $user->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->with('settings');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DeviceLog::class, 'device_id');
    }
}
