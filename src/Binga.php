<?php
namespace Moudarir\Binga;

use Moudarir\Binga\Config\Config;
use Moudarir\Binga\Helpers\CommonHelper;
use Moudarir\Binga\Http\BingaTrait;
use Moudarir\Binga\Http\Request;

class Binga {

    use BingaTrait;

    /**
     * @var string
     */
    private $store_id;

    /**
     * @var string
     */
    private $private_key;

    /**
     * Binga Constructor.
     *
     * @param array $_config ['username', 'password', 'store_id', 'private_key']
     * @param string $environment
     */
    public function __construct (array $_config = [], string $environment = 'dev')
    {
        $config = !empty($_config) ? array_merge(Config::DEV_CONFIG, $_config) : Config::DEV_CONFIG;
        $this->store_id = $config['store_id'];
        $this->private_key = $config['private_key'];

        self::setHttpClient($config, $environment);
    }

    /**
     * Retrieve an order by its code.
     *
     * @param string $code
     * @param string $format
     * @return array
     */
    public function order (string $code, string $format = 'json'): array
    {
        return (new Request(self::getHttpClient()))->get($code, $format);
    }

    /**
     * List all of the merchant's orders.
     *
     * @param int $limit
     * @param int $offset
     * @param string $format
     * @return array|array[]
     */
    public function merchantOrders (int $limit = 20, int $offset = 0, string $format = 'json'): array
    {
        return (new Request(self::getHttpClient()))->all($limit, $offset, $format);
    }

    /**
     * List all store orders.
     *
     * @param int $limit
     * @param int $offset
     * @param string $format
     * @return array|array[]
     */
    public function storeOrders (int $limit = 20, int $offset = 0, string $format = 'json'): array
    {
        return (new Request(self::getHttpClient()))->store($this->store_id, $limit, $offset, $format);
    }

    /**
     * Bill a customer as 'Pay'.
     * @param array $orderData
     * @param string $format
     * @param int $expireDays
     * @return array
     */
    public function pay (array $orderData, string $format = 'json', int $expireDays = 7): array
    {
        return $this->charge($orderData, 'pay', $format, $expireDays);
    }

    /**
     * Bill a customer as 'Prepay'.
     * @param array $orderData
     * @param string $format
     * @param int $expireDays
     * @return array
     */
    public function book (array $orderData, string $format = 'json', int $expireDays = 7): array
    {
        return $this->charge($orderData, 'prepay', $format, $expireDays);
    }

    /**
     * @param string $payment_type
     * @param array $data
     * @return string
     */
    public function generateCheckSum (string $payment_type, array $data): string
    {
        $types = Config::PAYMENT_TYPES;
        $type = array_key_exists($payment_type, $types) ? $types[$payment_type] : $types['prepay'];
        $checkSum = $type.$data['amount'].$this->store_id.$data['externalId'].$data['buyerEmail'].$this->private_key;
        return md5($checkSum);
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
    private function charge (array $orderData, string $type = 'prepay', string $format = 'json', int $expireDays = 7): array
    {
        if (is_float($orderData['amount']) || is_int($orderData['amount'])) {
            $orderData['amount'] = CommonHelper::formatAmount($orderData['amount']);
        }

        $extra_data = [
            'storeId' => $this->store_id,
            'apiVersion' => Config::API_VERSION,
            'expirationDate' => CommonHelper::formatExpirationDate($expireDays),
            'orderCheckSum' => $this->generateCheckSum($type, $orderData)
        ];
        $data = array_merge($extra_data, $orderData);
        return (new Request(self::getHttpClient()))->charge($data, $type, $format);
    }

}