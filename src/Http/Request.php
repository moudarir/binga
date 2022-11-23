<?php
namespace Moudarir\Binga\Http;

use GuzzleHttp\Exception\GuzzleException;
use Moudarir\Binga\Config\Config;

class Request {

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Request constructor.
     *
     * @param HttpClient $httpClient
     */
    public function __construct (HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $code
     * @param string $format
     * @return array
     */
    public function get (string $code, string $format = 'json'): array
    {
        try {
            $uri = sprintf(Config::ORDER_URI, $code);
            $response = $this->httpClient
                ->setAccept($format)
                ->setRequest($uri, 'GET')
                ->getResponse();

            if ($response['result'] === 'success') {
                return [
                    'error' => false,
                    'order' => new Order($response['orders']['order'])
                ];
            }

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
     * @param int $limit
     * @param int $offset
     * @param string $format
     * @return array
     */
    public function all (int $limit = 20, int $offset = 0, string $format = 'json'): array
    {
        try {
            $params = [
                'query' => [
                    'page' => 1,
                    'limit' => $limit, 'l' => $limit,
                    'offset' => $offset, 'o' => $offset
                ]
            ];
            $response = $this->httpClient
                ->setParams($params)
                ->setAccept($format)
                ->setRequest(Config::ORDERS_URI, 'GET')
                ->getResponse();

            if ($response['result'] === 'success') {
                $orders = [];

                if (!empty($response['orders']['order'])) {
                    foreach ($response['orders']['order'] as $order) {
                        $orders[] = new Order($order);
                    }
                }

                return [
                    'error' => false,
                    'orders' => $orders
                ];
            }

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
     * @param string $store_id
     * @param int $limit
     * @param int $offset
     * @param string $format
     * @return array
     */
    public function store (string $store_id, int $limit = 20, int $offset = 0, string $format = 'json'): array
    {
        try {
            $params = [
                'query' => [
                    'page' => 1,
                    'limit' => $limit, 'l' => $limit,
                    'offset' => $offset, 'o' => $offset
                ]
            ];
            $uri = sprintf(Config::STORE_ORDERS_URI, $store_id);
            $response = $this->httpClient
                ->setParams($params)
                ->setAccept($format)
                ->setRequest($uri, 'GET')
                ->getResponse();

            if ($response['result'] === 'success') {
                $orders = [];

                if (!empty($response['orders']['order'])) {
                    foreach ($response['orders']['order'] as $order) {
                        $orders[] = new Order($order);
                    }
                }

                return [
                    'error' => false,
                    'orders' => $orders
                ];
            }

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
     * Bill a customer.
     *
     * @param array $data
     * @param string $payment_type
     * @param string $format
     * @return array
     */
    public function charge (array $data, string $payment_type = 'prepay', string $format = 'json'): array
    {
        try {
            $params = ['form_params' => $data];
            $uri = $payment_type === 'pay' ? Config::PAY_URI : Config::PREPAY_URI;
            $response = $this->httpClient
                ->setParams($params)
                ->setAccept($format)
                ->setRequest($uri, 'POST')
                ->getResponse();

            if ($response['result'] === 'success') {
                return [
                    'error' => false,
                    'order' => new Order($response['orders']['order'])
                ];
            }

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