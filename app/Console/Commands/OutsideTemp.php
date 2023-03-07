<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OutsideTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outsideTemp:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the weather temperature from weather api';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $weatherService = new WeatherService();
        $users = User::all();
        foreach ($users as $user){
            Log::info("Syncing user " . $user->name . "\n");
            // user lat, log
            $lat = $user->city?->latitude;
            $lang = $user->city?->longitude;
            if($lat && $lang){
                $response = $weatherService->get('current', ['q' => "$lat,$lang"]);
                if ($response['success']) {
                    dd($response['data']['current']['temp_c'],$response['data']['current']['temp_f']);
                }
            }else{
                Log::info("Syncing user " . $user->name . "\n");
            }
        }
    }
}
