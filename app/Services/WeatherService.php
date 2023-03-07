<?php
namespace App\Services;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    private $rest_url;
    private $version;
    private $key;
    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->rest_url = config('weather.base_url');
        $this->key = config('weather.api_key');
        $this->version = config('weather.version');
    }

    public function getWeatherAccount()
    {
        return $this->rest_url.$this->version.'/';
    }

    public function get($path, $parameters = [])
    {
        $account =$this->getWeatherAccount();
        $url = $account.$path.'.json?key='.$this->key;
        if ($parameters) {
            $url = $url.'&'.$this->makeParamsString($parameters);
        }
        try {
            $response = $this->client->get($url);
            $code = $response->getStatusCode();
            if ($code < 300) {
                $responseData = (string) $response->getBody();
                return ['success' => true, 'code' => $code, 'data' => json_decode($responseData, true)];
            }
            return ['success' => false, 'code' => $code, 'data' => (string) $response->getBody()];
        } catch (\Exception $e) {
            Log::error("Weather API Error : " . $e->getMessage() . "\n");
            return ['success' => false, 'code' => $e->getCode(), 'data' => (string) $e->getMessage()];
        }
    }

    public function makeParamsString($params)
    {
        $isLastIndex = array_key_last($params);
        $paramsString = '';
        foreach ($params as $key => $param)
        {
            if ($key != $isLastIndex)
            {
                $paramsString = $paramsString."&$key=".$param;
            } else {
                $paramsString = "$key=$param";
            }
        }
        return $paramsString;
    }
}
