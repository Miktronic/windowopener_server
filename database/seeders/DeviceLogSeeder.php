<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceLog;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // Device::create(['alias'=>'tink1','device_address'=>'234errddf','location'=>'shymoli','user_id'=>2]);
        // Device::create(['alias'=>'tink2','device_address'=>'334errddf','location'=>'adabor','user_id'=>1]);

        $fake = Factory::create();


        $users = User::where('id','!=',1)->get();
        $status = [0,25,50,75,100];
        foreach ($users as $user ) {
            $is_auto =  $user->settings->is_auto;
            foreach ($user->devices as $device) {
                $device_id = $device->id;
                for($i=0; $i<10;++$i){
                    $temperature = $fake->randomFloat(2,5,40);
                    $data = [
                        'device_id'=>$device_id,
                        'is_auto'=>$is_auto,
                        'status'=>$status[random_int(0,4)],
                        'temperature'=>$temperature,
                        'timestamp'=>now()
                    ];
                    Log::info("DeviceLogSeeder data",$data);
                    DB::table('device_logs')->insert($data);
                }
            }


        }


    }
}
