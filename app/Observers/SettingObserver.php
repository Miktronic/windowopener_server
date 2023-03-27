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
        $this->deviceStatusUpdate($setting);         
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

    protected function deviceStatusUpdate($setting){

        $user = $setting->user;
        $devices = $user->devices;

        $is_auto = $setting->is_auto;

        foreach ($devices as $device){
            $address = $device->device_address;
            $cmd = $device->status;
            $low_temperature = $setting->low_temperature ?? 0;
            $high_temperature = $setting->high_temperature ?? 0;

            $cmd = 'mosquitto_pub -t /node/0/' . $address . ' -m "{\"id\":\"' . $address . '\",\"auto\":' . $is_auto .'\"cmd\":' . $cmd . ',\"low_temp\":' . $low_temperature . ',\"high_temp\":' . $high_temperature . '}"';

            Log::info("Run this command: " . $cmd);
            shell_exec($cmd);
        }
    }
}
