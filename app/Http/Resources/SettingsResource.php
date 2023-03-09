<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
          'outside_temperature' => $this->outside_temperature,
            'inside_temperature' => $this->user->insideTemp(),
            'is_auto' => $this->is_auto,
            'low_temperature' => $this->low_temperature,
            'high_temperature' => $this->high_temperature,
        ];
    }
}
