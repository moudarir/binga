<?php
namespace Moudarir\Binga\Http;

use Moudarir\Binga\Config\Config;

trait BingaTrait {

    /**
     * @var HttpClient
     */
    private static $http_client;

    /**
     * @return HttpClient
     */
    public static function getHttpClient (): HttpClient
    {
        return self::$http_client;
    }

    /**
     * @param array $config
     * @param string $env
     */
    public static function setHttpClient (array $config, string $env = 'dev'): void
    {
        if (!isset(self::$http_client)) {
            $params = [
                'auth' => [$config['username'], $config['password']],
                'http_errors' => $env === 'dev',
            ];
            $client = new HttpClient($env === 'prod' ? Config::PROD_ENDPOINT : Config::DEV_ENDPOINT);

            if (!empty($params)) {
                $client->setParams($params);
            }

            self::$http_client = $client;
        }
    }


}