<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['country', 'state', 'city'];

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

    public function city(){
        return $this->belongsTo(City::class, 'city_id');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class, 'user_id', 'id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'user_id');
    }

    public function insideTemp(){
        return $this->devices()
            ->where('is_temp_include', 1)
            ->get()
            ->map(fn($device) => $device->logs()->exists() ? $device->logs()->orderBy('id', 'desc')->first()->temperature : 0)
            ->avg();
    }
}
