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
     * Binga Constructor.
     *
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
     * Retrieve an order by its code.
     *
     * @param string $code
     * @param string $format
     * @return array
     */
    public function order (string $code, string $format = 'json'): array {
        try {
            $params = [
                'auth' => [$this->username, $this->password],
            ];
            self::$client
                ->setAccept($format)
                ->setParams($params)
                ->request('GET', '/bingaApi/api/orders/'.$code);
            $response = self::$client->getResponse();

            if ($response['result'] === 'success'):
                return [
                    'error' => false,
                    'order' => $response['orders']['order']
                ];
            endif;

            return [
                'error' => true,
                'code' => (int)$response['error']['code'],
                'message' => $response['error']['message']
            ];
        } catch (GuzzleException $e) {
            return [
                'error' => true,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * List all of the merchant's orders.
     *
     * @param array $_params
     * @param string $format
     * @return array|array[]
     */
    public function orders (array $_params = [], string $format = 'json'): array {
        try {
            $params = [
                'auth' => [$this->username, $this->password],
                'stream' => true
            ];

            if (array_key_exists('query', $_params)):
                $params['query'] = $_params['query'];
            endif;

            self::$client
                ->setAccept($format)
                ->setParams($params)
                ->request('GET', '/bingaApi/api/orders');
            $response = self::$client->getResponse();

            if ($response['result'] === 'success'):
                return [
                    'error' => false,
                    'orders' => $response['orders']['order']
                ];
            endif;

            return [
                'error' => true,
                'code' => (int)$response['error']['code'],
                'message' => $response['error']['message']
            ];
        } catch (GuzzleException $e) {
            return [
                'error' => true,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * List all store orders.
     *
     * @param array $_params
     * @param string $format
     * @return array|array[]
     */
    public function storeOrders (array $_params = [], string $format = 'json'): array {
        try {
            $params = [
                'auth' => [$this->username, $this->password],
                'stream' => true
            ];

            if (array_key_exists('query', $_params)):
                $params['query'] = $_params['query'];
            endif;

            self::$client
                ->setAccept($format)
                ->setParams($params)
                ->request('GET', '/bingaApi/api/orders/store/'.$this->storeId);
            $response = self::$client->getResponse();

            if ($response['result'] === 'success'):
                return [
                    'error' => false,
                    'orders' => $response['orders']['order']
                ];
            endif;

            return [
                'error' => true,
                'code' => (int)$response['error']['code'],
                'message' => $response['error']['message']
            ];
        } catch (GuzzleException $e) {
            return [
                'error' => true,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Bill a customer as 'Pay'.
     * @param array $orderData
     * @param string $format
     * @param int $expireDays
     * @return array
     */
    public function pay (array $orderData, string $format = 'json', int $expireDays = 7): array {
        return $this->charge($orderData, 'pay', $format, $expireDays);
    }

    /**
     * Bill a customer as 'Prepay'.
     * @param array $orderData
     * @param string $format
     * @param int $expireDays
     * @return array
     */
    public function book (array $orderData, string $format = 'json', int $expireDays = 7): array {
        return $this->charge($orderData, 'prepay', $format, $expireDays);
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

    /**
     * Bill a customer.
     *
     * @param array $orderData
     * @param string $type
     * @param string $format
     * @param int $expireDays
     * @return array
     */
    private function charge (array $orderData, string $type = 'prepay', string $format = 'json', int $expireDays = 7): array {
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
            $params = [
                'auth' => [$this->username, $this->password],
                'form_params' => $formParams
            ];
            self::$client
                ->setAccept($format)
                ->setParams($params)
                ->request('POST', $uri);
            $response = self::$client->getResponse();

            if ($response['result'] === 'success'):
                return [
                    'error' => false,
                    'order' => $response['orders']['order']
                ];
            endif;

            return [
                'error' => true,
                'code' => (int)$response['error']['code'],
                'message' => $response['error']['message']
            ];
        } catch (GuzzleException $e) {
            return [
                'error' => true,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

}