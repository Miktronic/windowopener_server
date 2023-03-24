<?php

namespace App\Observers;

use App\Models\Device;
use App\Models\Setting;
use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Log;

class DeviceObserver
{
    /**
     * Handle the Device "created" event.
     *
     * @param  \App\Models\Device  $device
     * @return void
     */
    public function created(Device $device)
    {
        $this->syncDeviceMode($device);
        $this->getOutsideTemp($device->user);
    }

    /**
     * Handle the Device "updated" event.
     *
     * @param  \App\Models\Device  $device
     * @return void
     */
    public function updated(Device $device)
    {
        $this->syncDeviceMode($device);
        $this->getOutsideTemp($device->user);
    }

    /**
     * Handle the Device "deleted" event.
     *
     * @param  \App\Models\Device  $device
     * @return void
     */
    public function deleted(Device $device)
    {
        //
    }

    /**
     * Handle the Device "restored" event.
     *
     * @param  \App\Models\Device  $device
     * @return void
     */
    public function restored(Device $device)
    {
        //
    }

    /**
     * Handle the Device "force deleted" event.
     *
     * @param  \App\Models\Device  $device
     * @return void
     */
    public function forceDeleted(Device $device)
    {
        //
    }

    /**
     * @param Device $device
     * @return void
     */
    public function syncDeviceMode(Device $device): void
    {
        $user = $device->user;
        $settings = $user->settings;

        $is_auto = $settings->is_auto;
        $low_temperature = $settings->low_temperature;
        $high_temperature = $settings->high_temperature;

        $cmd = 'mosquitto_pub -t /node/0/' . $device->device_address . ' -m "{\"id\":\"' . $device->device_address . '\",\"auto\":' . $is_auto . ',\"cmd\":' . $device->status . ',\"low_temp\":' . $low_temperature . ',\"high_temp\":' . $high_temperature . '}"';

        Log::info("Run this command: " . $cmd);
        shell_exec($cmd);
    }

    /**
     * Outside Temperature Sync
     * @param user user data
     */
    protected function getOutsideTemp($user){
        $weatherService = new WeatherService();
        if($weatherService){
            Log::info("Weather Service Connected! \n");
        }

        Log::info("Start syncing user " . $user->name . "\n");

        // user lat, log
        $lat = $user->latitude;
        $long = $user->longitude;

        //check for city lat long if there are any device
        if(!$lat && !$long){
            $devices = $user->devices;
            foreach ($devices as $device ) {
                if($device->city){
                    $lat = $device->city->latitude;
                    $long = $device->city->longitude;
                    break;
                }
                else if($device->state){
                    $lat = $device->state->latitude;
                    $long = $device->state->longitude;
                    break;
                }
                else if($device->country){
                    $lat = $device->country->latitude;
                    $long = $device->country->longitude;
                    break;
                }
            }
        }

        if($lat && $long){
            $response = $weatherService->get('current', ['q' => "$lat,$long"]);
            if ($response['success']) {
                Setting::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                    ],
                    [
                        'outside_temperature' => $response['data']['current']['temp_f'] ?? null,
                    ]
                );
                Log::info('Outside Temperature : '.$response['data']['current']['temp_f']."\n");
            }
        }else{
            Log::info("lat, long not found!! \n");
        }
        Log::info("End syncing user " . $user->name . "\n");
    }
}
