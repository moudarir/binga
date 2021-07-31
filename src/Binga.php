<?php
namespace Moudarir\Binga;

use GuzzleHttp\Exception\GuzzleException;

class Binga {

    /**
     * @var Client
     */
    private static $client;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $storeId;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @param array $_config ['username', 'password', 'store_id', 'private_key']
     * @param string $environment
     */
    public function __construct (array $_config = [], string $environment = 'dev') {
        $config = is_array($_config) ? array_merge(Config::DEV_CONFIG, $_config) : Config::DEV_CONFIG;
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->storeId = $config['store_id'];
        $this->privateKey = $config['private_key'];

        if (!isset(self::$client)):
            self::$client = new Client($environment === 'prod' ? Config::PROD_ENDPOINT : Config::DEV_ENDPOINT);
        endif;
    }

    /**
     * @param string $code
     * @param array $_params
     * @return array
     */
    public function getOrder (string $code, array $_params = []): array {
        try {
            $config = [
                'auth' => [$this->username, $this->password],
            ];
            $params = array_merge($config, $_params);
            self::$client->setParams($params)->request('GET', '/bingaApi/api/orders/'.$code);
            $response = self::$client->getResponse();
        } catch (GuzzleException $e) {
            $response = [
                'error' => true,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @param array $_params
     * @return array
     */
    public function getOrders (array $_params = []): array {
        try {
            $config = [
                'auth' => [$this->username, $this->password],
            ];
            $params = array_merge($config, $_params);
            self::$client->setParams($params)->request('GET', '/bingaApi/api/orders/store/'.$this->storeId);
            $response = self::$client->getResponse();
        } catch (GuzzleException $e) {
            $response = [
                'error' => true,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @param array $orderData
     * @param array $_params
     * @param string $type
     * @param int $expireDays
     * @return array
     */
    public function sendOrder (array $orderData, array $_params = [], string $type = 'pay', int $expireDays = 7): array {
        try {
            if (is_float($orderData['amount']) || is_int($orderData['amount'])):
                $orderData['amount'] = Common::formatAmount($orderData['amount']);
            endif;

            $uri = $type === 'pay' ? Config::PAY_URI : Config::PREPAY_URI;
            $data = [
                'storeId' => $this->storeId,
                'apiVersion' => Config::API_VERSION,
                'expirationDate' => Common::formatExpirationDate($expireDays),
                'orderCheckSum' => $this->generateCheckSum($type, $orderData)
            ];
            $formParams = array_merge($data, $orderData);
            $config = [
                'auth' => [$this->username, $this->password],
                'form_params' => $formParams
            ];
            $params = array_merge($config, $_params);
            self::$client->setParams($params)->request('GET', $uri);
            $response = self::$client->getResponse();
        } catch (GuzzleException $e) {
            $response = [
                'error' => true,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @param string $type
     * @param array $data
     * @return string|null
     */
    public function generateCheckSum (string $type, array $data): ?string {
        $types = Config::ORDER_TYPES;

        if (array_key_exists($type, $types)):
            $checkSum = $types[$type].$data['amount'].$this->storeId.$data['externalId'].$data['buyerEmail'].$this->privateKey;
            return md5($checkSum);
        endif;

        return null;
    }

}