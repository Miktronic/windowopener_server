<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    use HasFactory;

    protected $appends = ['creator', 'country', 'state', 'city'];

    public function getCreatorAttribute() {
        $user = User::find($this->user_id);
        return $user->name;
    }

    public function getCountryAttribute() {
        $country = Country::select('id', 'name','latitude','longitude')->find($this->country_id);
        return $country;
    }

    public function getStateAttribute() {
        $state = State::select('id', 'name','latitude','longitude')->find($this->state_id);
        return $state;
    }

    public function getCityAttribute() {
        $city = City::select('id', 'name','latitude','longitude')->find($this->city_id);
        return $city;
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
