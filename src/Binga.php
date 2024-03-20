<?php

namespace Moudarir\Binga;

class Binga
{

    /**
     * @var string
     */
    private $store_id;

    /**
     * @var string
     */
    private $private_key;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param array{store_id: string, private_key: string, username: string, password: string} $config
     * @param string $environment 'dev' OR 'prod'
     * @throws BingaMissingParamException
     */
    public function __construct(array $config = [], string $env = 'dev')
    {
        $param = \array_merge(Config::DEV_CONFIG, $config);
        $this->store_id = $param['store_id'] ?? \getenv("BINGA_STORE_ID");
        $this->private_key = $param['private_key'] ?? \getenv("BINGA_PRIVATE_KEY");
        $username = $param['username'] ?? \getenv("BINGA_USERNAME");
        $password = $param['password'] ?? \getenv("BINGA_PASSWORD");

        if (empty($this->store_id)) {
            throw new BingaMissingParamException("The 'Store ID' is not defined.", 412);
        }
        if (empty($this->private_key)) {
            throw new BingaMissingParamException("The 'Private Key' is not defined.", 412);
        }
        if (empty($username)) {
            throw new BingaMissingParamException("The 'Username' is not defined.", 412);
        }
        if (empty($password)) {
            throw new BingaMissingParamException("The 'Password' is not defined.", 412);
        }

        if (!isset($this->client)) {
            $this->client = (new Client($env === 'prod' ? Config::PROD_ENDPOINT : Config::DEV_ENDPOINT))
                ->setParam('http_errors', ($env === 'dev'))
                ->setBasicAuth($username, $password);
        }
    }

    /**
     * Retrieve an order by its code.
     *
     * @param string $code
     * @param string $format 'json' OR 'xml'
     * @return Order
     * @throws BingaRequestException
     * @throws BingaResponseException
     */
    public function order(string $code, string $format = 'json'): Order
    {
        $uri = \sprintf(Config::ORDER_URI, $code);
        $response = $this->client->setAccept($format)->get($uri)->getResponse();
        $this->checkResponse($response);

        return new Order($response['orders']['order']);
    }

    /**
     * List all the merchant's orders.
     *
     * @param int $page
     * @param int $limit
     * @param int $offset
     * @param string $format 'json' OR 'xml'
     * @return array|Order[]
     * @throws BingaRequestException
     * @throws BingaResponseException
     */
    public function merchantOrders(int $page = 1, int $limit = 20, int $offset = 0, string $format = 'json'): array
    {
        $data = [
            'page' => $page,
            'limit' => $limit, 'l' => $limit,
            'offset' => $offset, 'o' => $offset
        ];
        $response = $this->client
            ->setAccept($format)->setQueryData($data)
            ->get(Config::ORDERS_URI)->getResponse();
        $this->checkResponse($response);
        $orders = [];

        if (!empty($response['orders']['order'])) {
            foreach ($response['orders']['order'] as $order) {
                $orders[] = new Order($order);
            }
        }

        return $orders;
    }

    /**
     * List all store orders.
     *
     * @param int $page
     * @param int $limit
     * @param int $offset
     * @param string $format 'json' OR 'xml'
     * @return array|Order[]
     * @throws BingaRequestException
     * @throws BingaResponseException
     */
    public function storeOrders(int $page = 1, int $limit = 20, int $offset = 0, string $format = 'json'): array
    {
        $data = [
            'page' => $page,
            'limit' => $limit, 'l' => $limit,
            'offset' => $offset, 'o' => $offset
        ];
        $uri = \sprintf(Config::STORE_ORDERS_URI, $this->store_id);
        $response = $this->client->setAccept($format)->setQueryData($data)->get($uri)->getResponse();
        $this->checkResponse($response);
        $orders = [];

        if (!empty($response['orders']['order'])) {
            foreach ($response['orders']['order'] as $order) {
                $orders[] = new Order($order);
            }
        }

        return $orders;
    }

    /**
     * Bill a customer as 'Pay'.
     *
     * @param array $data
     * @param string $format
     * @param int $expireDays
     * @return Order
     * @throws BingaResponseException
     * @throws BingaRequestException
     */
    public function pay(array $data, string $format = 'json', int $expireDays = 7): Order
    {
        return $this->charge($data, 'pay', $format, $expireDays);
    }

    /**
     * Bill a customer as 'Prepay'.
     *
     * @param array $data
     * @param string $format
     * @param int $expireDays
     * @return Order
     * @throws BingaResponseException
     * @throws BingaRequestException
     */
    public function book(array $data, string $format = 'json', int $expireDays = 7): Order
    {
        return $this->charge($data, 'prepay', $format, $expireDays);
    }

    /**
     * @param string $payment_type
     * @param array $data
     * @return string
     */
    public function generateCheckSum(string $payment_type, array $data): string
    {
        $types = Config::PAYMENT_TYPES;
        $type = \array_key_exists($payment_type, $types) ? $types[$payment_type] : $types['prepay'];
        $checkSum = $type.$data['amount'].$this->store_id.$data['externalId'].$data['buyerEmail'].$this->private_key;
        return \md5($checkSum);
    }

    /**
     * @param array $data
     * @param string $type
     * @param string $format
     * @param int $expireDays
     * @return Order
     * @throws BingaResponseException
     * @throws BingaRequestException
     */
    private function charge(array $data, string $type = 'prepay', string $format = 'json', int $expireDays = 7): Order
    {
        if (\is_float($data['amount']) || \is_int($data['amount'])) {
            $data['amount'] = Utils::formatAmount($data['amount']);
        }

        $defaults = [
            'storeId' => $this->store_id,
            'apiVersion' => Config::API_VERSION,
            'expirationDate' => Utils::formatExpirationDate($expireDays),
            'orderCheckSum' => $this->generateCheckSum($type, $data)
        ];
        $response = $this->client
            ->setAccept($format)->setFormData(\array_merge($defaults, $data))
            ->post($type === 'pay' ? Config::PAY_URI : Config::PREPAY_URI)->getResponse();
        $this->checkResponse($response);

        return new Order($response['orders']['order']);
    }

    /**
     * @param array $response
     * @return void
     * @throws BingaResponseException
     */
    private function checkResponse(array $response): void
    {
        if (!\array_key_exists('result', $response) || $response['result'] !== 'success') {
            throw new BingaResponseException($response['error']['message'], (int)$response['error']['code']);
        }
    }
}
