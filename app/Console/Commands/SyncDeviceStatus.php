<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDeviceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deviceStatus:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $users = User::all()->filter(fn($user) => $user->settings->is_auto === 1);
        foreach ($users as $user){
            $devices = $user->devices;
            foreach ($devices as $device){
                $this->info("device: " . $device->device_address . "\n");
                $cmd = 'mosquitto_pub -t /node/0/' . $device->device_address . ' -m "{\"id\":\"' . $device->device_address . '\",\"auto\":' . $user->settings->is_auto . '\",\"status\":' . $device->status . ',\"low_temp\":' . $user->settings->low_temperature . ',\"high_temp\":' . $user->settings->high_temperature . '}"';
                $this->info("Run this command: " . $cmd . "\n");
                $res = shell_exec($cmd);
                $this->info("response: " . $res . "\n");
            }
        }
    }
}
