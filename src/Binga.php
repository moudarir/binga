<?php
namespace Moudarir\Binga;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Laravie\Parser\Xml\Reader;
use Laravie\Parser\Xml\Document;

class Binga {

    /**
     * @var Client
     */
    private $client;

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
     * Binga constructor.
     *
     * @param string $username recovered from Binga.ma
     * @param string $password recovered from Binga.ma
     * @param string $storeId recovered from Binga.ma
     * @param string $privateKey recovered from Binga.ma
     * @param string $environment
     */
    public function __construct ($username, $password, $storeId, $privateKey, $environment = 'dev') {
        $this->username     = $username;
        $this->password     = $password;
        $this->storeId      = $storeId;
        $this->privateKey   = $privateKey;

        $this->client       = new Client([
            'base_uri' => $environment === 'prod' ? Statics::BASE_URI_PROD : Statics::BASE_URI_DEV
        ]);
    }

    /**
     * getOrder()
     *
     * @param string $code
     * @param array $params
     * @return array
     */
    public function getOrder ($code, $params = []) {
        $response = $this->request('GET', '/bingaApi/api/orders/'.$code, $params);

        return $response;
    }

    /**
     * getOrders()
     *
     * @param array $params
     * @return array
     */
    public function getOrders ($params = []): array {
        $response = $this->request('GET', '/bingaApi/api/orders/store/'.$this->storeId, $params);

        return $response;
    }

    /**
     * sendOrder()
     *
     * @param $orderData
     * @param array $params
     * @param string $type
     * @param int $expireDays
     * @return array
     */
    public function sendOrder ($orderData, $params = [], $type = 'pay', $expireDays = 7): array {
        $orderData['amount'] = $this->getAmount($orderData['amount']);
        $default        = [
            'apiVersion'        => Statics::API_VERSION,
            'expirationDate'    => $this->setExpirationDate($expireDays),
            'storeId'           => $this->storeId,
            'orderCheckSum'     => $this->generateCheckSum($type, $orderData)
        ];
        $dataBody       = array_merge($default, $orderData);
        $params['form_params'] = $dataBody;
        $requestUri     = $type === 'pay' ? Statics::PAY_URI : Statics::PREPAY_URI;
        $response       = $this->request('POST', $requestUri, $params);

        return $response;
    }

    /**
     * request()
     *
     * @param string $method GET | POST
     * @param string $uri
     * @param array $params
     * @return array ['error', 'code', 'message', ?'orders']
     */
    public function request ($method, $uri, $params = []): array {
        try {
            $options    = $this->setOptions($this->username, $this->password, $params);
            $request    = $this->client->request($method, $uri, $options);

            $formatted  = $this->formatContents($request, $params);
            $content    = $formatted['content'];
            $response   = $this->checkContent($content);

            if (!$response['error']) {
                $response['orders'] = $content->orders->order;
            }
        } catch (GuzzleException $e) {
            $response = [
                'error'     => true,
                'code'      => $e->getCode(),
                'message'   => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * generateCheckSum()
     *
     * @param string $type
     * @param array $data
     * @return string|null
     */
    private function generateCheckSum ($type, $data):? string {
        $types = Statics::ORDER_TYPES;

        if (isset($types[$type])) {
            $checkSum = $types[$type].$data['amount'].$this->storeId.$data['externalId'].$data['buyerEmail'].$this->privateKey;
            return md5($checkSum);
        }

        return null;
    }

    /**
     * setExpirationDate()
     *
     * @param int $expireDays
     * @param string $format
     * @return string|null
     */
    public function setExpirationDate ($expireDays = 7, $format = 'Y-m-d\TH:i:se'):? string {
        $expiration = null;
        try {
            $carbon     = new Carbon(null, Statics::TIMEZONE);
            $daysNumber = $expireDays > 0 ? $expireDays : 7;
            $expiration = $carbon
                ->addDays($daysNumber)
                ->format($format);
        } catch (\Exception $e) {
            //return null;
        }

        return $expiration;
    }

    /**
     * setOptions()
     *
     * @param string $username
     * @param string $password
     * @param array $params
     * @return array
     */
    private function setOptions ($username, $password, $params = []): array {
        $default = [
            'auth'      => [$username, $password],
            'headers'   => [
                'Accept'    => 'application/json'
            ]
        ];
        $options = !empty($params) ? array_merge($default, $params) : $default;
        return $options;
    }

    /**
     * formatContents()
     *
     * @param mixed|\Psr\Http\Message\ResponseInterface $request
     * @param array $params
     * @return array
     */
    private function formatContents ($request, $params): array {
        $body   = $request->getBody();
        $format = $request->getHeader('Content-Type');

        if (isset($params['stream']) && $params['stream'] === true) {
            $contents = '';
            while (!$body->eof() ) {
                $contents .= $body->read(1024);
            }
            $body->close();
        } else {
            $contents = $body->getContents();
        }

        if (!empty($format)) {
            if (in_array('application/json', $format)) {
                return [
                    'type'      => 'json',
                    'content'   => json_decode($contents)
                ];
            } elseif (in_array('application/xml', $format)) {
                $parser = new Reader(new Document());
                $xml    = $parser->extract($contents)->getOriginalContent();

                return [
                    'type'      => 'xml',
                    'content'   => $xml
                ];
            }
        }

        return [
            'type'      => 'Unknown',
            'content'   => ''
        ];
    }

    /**
     * checkContent()
     *
     * @param object $content
     * @return array
     */
    private function checkContent ($content): array {
        $response = ['error' => false];
        $result = $content->result;

        if ($result === 'error') {
            $error  = $content->error;
            $response = [
                'error'     => true,
                'code'      => (int)$error->code,
                'message'   => $error->message
            ];
        }

        return $response;
    }

    /**
     * getAmount()
     *
     * @param float|int $amount
     * @return string
     */
    private function getAmount ($amount) {
        return bcadd(round($amount, 2), '0', 2);
    }

}