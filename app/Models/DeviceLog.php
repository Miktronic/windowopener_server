<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    use HasFactory;

    protected $appends = ['alias', 'device_address', 'status_label', 'content'];

    public function getDeviceAddressAttribute() {
        $device = Device::find($this->device_id);
        return $device->device_address;
    }

    public function getAliasAttribute() {
        $device = Device::find($this->device_id);
        return $device->alias;
    }

    public function getStatusLabelAttribute() {
        return $this->status . ' at ' . date('Y-m-d H:i', strtotime($this->timestamp));
    }

    public function getContentAttribute() {
        $msg = "";
        if($this->status == 0){
            $msg = 'The window is closed';
        }else if($this->status == 25){
            $msg = 'The window is quarterly open.';
        }else if($this->status == 50){
            $msg = 'The window is half open.';
        }else if($this->status == 75){
            $msg = 'The window is three quarterly open.';
        }else if($this->status == 100){
            $msg = 'The window is fully open.';
        }

        return $msg;
    }
}
