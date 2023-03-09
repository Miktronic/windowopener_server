<?php

namespace App\Observers;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SettingObserver
{
    /**
     * Handle the Setting "created" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function created(Setting $setting)
    {
        //
    }

    /**
     * Handle the Setting "updated" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function updated(Setting $setting)
    {
        $user = $setting->user;
        $devices = $user->devices;

        $is_auto = $user->settings->is_auto;

        foreach ($devices as $device){
            $address = $device->address;
            $low_temperature = $device->low_temperature;
            $high_temperature = $device->high_temperature;

            $cmd = 'mosquitto_pub -t /node/0/' . $address . ' -m "{\"id\":\"' . $address . '\",\"auto\":' . $is_auto . ',\"low_temp\":' . $low_temperature . ',\"high_temp\":' . $high_temperature . '}"';

            Log::info("Run this command: " . $cmd);
            shell_exec($cmd);
        }
    }

    /**
     * Handle the Setting "deleted" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function deleted(Setting $setting)
    {
        //
    }

    /**
     * Handle the Setting "restored" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function restored(Setting $setting)
    {
        //
    }

    /**
     * Handle the Setting "force deleted" event.
     *
     * @param  \App\Models\Setting  $setting
     * @return void
     */
    public function forceDeleted(Setting $setting)
    {
        //
    }
}
