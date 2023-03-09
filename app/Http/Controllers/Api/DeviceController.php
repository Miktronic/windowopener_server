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

    public function create(Request $request) {
        $request->validate([
            'device_address' => ['required', 'string', 'max:255', 'unique:devices'],
        ]);

        $device = new Device();
        if($request->has('alias'))
            $device->alias = $request->alias;
        $device->device_address = $request->device_address;
        if($request->has('type'))
            $device->type = $request->type;
        if($request->has('location'))
            $device->location = $request->location;
        $device->user_id = $request->user()->id;
        $device->status = 0;
        $device->save();
        return response()->json(['data' => $device]);
    }

    public function createByApp(Request $request)
    {
        Log::info($request);
        $request->validate([
            'device_address' => ['required', 'string', 'max:255', 'unique:devices'],
        ]);

        $device = new Device();
        if($request->has('alias'))
            $device->alias = $request->alias;
        $device->device_address = $request->device_address;
        if($request->has('country'))
            $device->country_id = $request->country["id"];
        if($request->has('state'))
            $device->state_id = $request->state["id"];
        if($request->has('city') && isset($request->city["id"]))
            $device->city_id = $request->city["id"];
        $device->user_id = $request->user()->id;
        if($request->has('status'))
            $device->status = $request->status;
        $device->save();

        $cmd = 'mosquitto_pub -t /node/0/' . $device->device_address . ' -m "{\"id\":\"' . $device->device_address . '\",\"auto\":1,\"low_temp\":' . $device->low_temperature . ',\"high_temp\":' . $device->high_temperature . '}"';
        if($device->is_auto == 'No')
            $cmd = 'mosquitto_pub -t /node/0/' . $device->device_address . ' -m "{\"id\":\"' . $device->device_address . '\",\"auto\":0,\"low_temp\":' . $device->low_temperature . ',\"high_temp\":' . $device->high_temperature . '}"';
        Log::info("Run this command: " . $cmd);
        shell_exec($cmd);

        $device = Device::find($device->id);
        return response()->json(['data' => $device->makeHidden(['created_at', 'updated_at', 'type', 'creator', 'user_id', 'location', 'country_id', 'state_id', 'city_id'])]);
    }

    public function update(Device $device, Request $request)
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
           'status' => ['required', 'numeric'],
           'is_temp_include' => ['required', 'boolean'],
           'is_hum_include' => ['required', 'boolean'],
        ]);

        foreach($attrs as $key => $value){
            if($value === null) unset($attrs[$key]);
        }

        $device->update($attrs);

        return $device;
    }

    public function setOpenStatus(Request $request, $id)
    {
        $request->validate([
            'value' => ['required', 'numeric|min:0|max:100'],
        ]);

        $device = Device::find($id);
        $device->status = $request->value;
        $device->save();

        $cmd = 'mosquitto_pub -t /node/0/' . $device->device_address . ' -m "{\"id\":\"' . $device->device_address . '\",\"cmd\":' . $device->status .'}"';
        Log::info("Run this command: " . $cmd);
        shell_exec($cmd);

        return response()->json(['data' => $device->makeHidden(['created_at', 'updated_at', 'type', 'creator', 'user_id', 'location', 'country_id', 'state_id', 'city_id'])]);
    }

    public function delete($id) {
        Device::destroy($id);
        DeviceLog::where('device_id', $id)->delete();
        return response()->json(['data' => $id]);
    }
}
