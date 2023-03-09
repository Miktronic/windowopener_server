<?php

namespace App\Observers;

use App\Models\Device;
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
        //
    }

    /**
     * Handle the Device "updated" event.
     *
     * @param  \App\Models\Device  $device
     * @return void
     */
    public function updated(Device $device)
    {
        $user = $device->user;
        $settings = $user->settings;

        $is_auto =$settings->is_auto;
        $low_temperature = $settings->low_temperature;
        $high_temperature = $settings->high_temperature;

        $cmd = 'mosquitto_pub -t /node/0/' . $device->device_address . ' -m "{\"id\":\"' . $device->device_address . '\",\"auto\":' . $is_auto . ',\"low_temp\":' . $low_temperature . ',\"high_temp\":' . $high_temperature . '}"';

        Log::info("Run this command: " . $cmd);
        shell_exec($cmd);
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
}
