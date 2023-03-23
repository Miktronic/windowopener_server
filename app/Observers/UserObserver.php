<?php

namespace App\Observers;

use App\Models\Setting;
use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        // create a settings
        $user->settings()->create([
            //
        ]);

        $this->getOutsideTemp($user);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
        $this->getOutsideTemp($user);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
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
