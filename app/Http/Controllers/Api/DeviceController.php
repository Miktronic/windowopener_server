<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\DeviceLog;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    public function get(Request $request) {
        $devices = Device::all();
        return response()->json(['data' => $devices]);
    }

    public function getDevices(Request $request) {
        $devices = Device::where('user_id', $request->user()->id)->get();
        return response()->json(['data' => $devices->makeHidden(['created_at', 'updated_at', 'type', 'creator', 'user_id', 'location', 'country_id', 'state_id', 'city_id'])]);
    }

    public function getDeviceByApp($id) {
        $device = Device::find($id);
        return response()->json(['data' => $device->makeHidden(['created_at', 'updated_at', 'type', 'creator', 'user_id', 'location', 'country_id', 'state_id', 'city_id'])]);
    }

    public function create(Request $request)
    {
        $attrs = $request->validate([
            'alias' => ['nullable', 'string'],
            'device_address' => ['required', 'string'],
            'type' => ['required', 'integer'],
            'location' => ['nullable', 'string'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'integer'],
            'is_temp_include' => ['required', 'boolean'],
            'is_hum_include' => ['required', 'boolean'],
        ]);

        foreach($attrs as $key => $value){
            if($value === null) unset($attrs[$key]);
        }

        return Device::create($attrs);
    }

    public function update(Device $device, Request $request)
    {
        $attrs = $request->validate([
           'alias' => ['sometimes', 'string'],
           'device_address' => ['sometimes', 'string'],
           'type' => ['sometimes', 'integer'],
           'location' => ['sometimes', 'string'],
           'country_id' => ['sometimes', 'exists:countries,id'],
           'state_id' => ['sometimes', 'exists:states,id'],
           'city_id' => ['sometimes', 'exists:cities,id'],
           'user_id' => ['sometimes', 'exists:users,id'],
           'status' => ['sometimes', 'integer'],
           'is_temp_include' => ['sometimes', 'boolean'],
           'is_hum_include' => ['sometimes', 'boolean'],
        ]);

        foreach($attrs as $key => $value){
            if($value === null) unset($attrs[$key]);
        }

        $device->update($attrs);

        return $device;
    }

    public function delete($id) {
        Device::destroy($id);
        DeviceLog::where('device_id', $id)->delete();
        return response()->json(['data' => $id]);
    }
}
