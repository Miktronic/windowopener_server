<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('alias')->nullable();
            $table->string('device_address')->unique();
            $table->integer('type')->default(1);
            $table->string('location')->nullable();
            $table->foreignId('country_id')->nullable();
            $table->foreignId('state_id')->nullable();
            $table->foreignId('city_id')->nullable();
            $table->enum('is_auto', ['Yes', 'No'])->default('No');
            $table->foreignId('user_id');
            $table->float('status')->default(0);
            $table->boolean('is_temp_include')->default(1);
            $table->boolean('is_hum_include')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
