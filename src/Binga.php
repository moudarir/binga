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
            $contents   = $this->formatContents($request, $params);
            $response   = $this->checkContent($contents);

            if (!$response['error']) {
                $response['orders'] = $contents->orders->order;
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
    public function generateCheckSum ($type, $data):? string {
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
     * @return mixed|\stdClass
     */
    private function formatContents ($request, $params): \stdClass {
        $requestBody    = $request->getBody();
        $requestFormats = $request->getHeader('Content-Type');
        $format         = 'json';

        if (isset($params['stream']) && $params['stream'] === true) {
            $contents = '';
            while (!$requestBody->eof() ) {
                $contents .= $requestBody->read(1024);
            }
            $requestBody->close();
        } else {
            $contents = $requestBody->getContents();
        }

        if (!empty($requestFormats)) {
            foreach ($requestFormats as $requestFormat) {
                switch ($requestFormat) {
                    case 'application/json':
                    case 'text/javascript':
                        $format = 'json';
                        break;
                    case 'application/xml':
                    case 'text/xml':
                        $format = 'xml';
                        break;
                    default:
                        $format = 'unknown';
                        break;
                }

                break;
            }
        }

        if ($format === 'json') {
            $contentsObject = json_decode($contents);
        } elseif ($format === 'xml') {
            $parser         = new Reader(new Document());
            $contentsObject = $parser->extract($contents)->getOriginalContent();
        } else {
            $contentsObject = new \stdClass();
        }

        return $contentsObject;
    }

    /**
     * checkContent()
     *
     * @param mixed|\stdClass $contents
     * @return array
     */
    private function checkContent ($contents): array {
        if (Statics::isEmptyObject($contents)) {
            $response = [
                'error'     => false,
                'code'      => 204,
                'message'   => 'No Content.'
            ];
        } else {
            $response   = ['error' => false];
            $result     = $contents->result;

            if ($result === 'error') {
                $error      = $contents->error;
                $response   = [
                    'error'     => true,
                    'code'      => (int)$error->code,
                    'message'   => $error->message
                ];
            }
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