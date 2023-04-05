<?php

namespace App\Observers;

use App\Models\Device;
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

        if($device->wasChanged('status')){
            $is_auto = $settings->is_auto;
            $low_temperature = $settings->low_temperature ?? 0;
            $high_temperature = $settings->high_temperature ?? 0;
    
            $cmd = 'mosquitto_pub -t /node/0/' . $device->device_address . ' -m "{\"id\":\"' . $device->device_address . '\",\"auto\":' . $is_auto . ',\"cmd\":' . $device->status . ',\"low_temp\":' . $low_temperature . ',\"high_temp\":' . $high_temperature . '}"';
    
            Log::info("Run this command: " . $cmd);
            shell_exec($cmd);
        }
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
            if($user->city){
                $lat = $user->city->latitude;
                $long = $user->city->longitude;
                
            }
            else if($user->state){
                $lat = $user->state->latitude;
                $long = $user->state->longitude;
                
            }
            else if($user->country){
                $lat = $user->country->latitude;
                $long = $user->country->longitude;
                
            }
        }

        if($lat && $long){
            $response = $weatherService->get('current', ['q' => "$lat,$long"]);
            if ($response['success']) {
                $user->settings()->update(
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
