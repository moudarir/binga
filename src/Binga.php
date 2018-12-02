<?php
namespace Moudarir\Binga;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
     * @param string $method GET | POST
     * @param string $uri
     * @param array $params
     * @return array ['error', 'code', 'message', ?'orders']
     */
    public function request ($method, $uri, $params = []): array {
        try {
            $options    = $this->setOptions($this->username, $this->password, $params);
            $request    = $this->client->request($method, $uri, $options);

            if ($request->getStatusCode() === 200) {
                $formatted  = $this->formatContents($request);
                $content    = $formatted['content'];
                $response   = $this->checkContent($content);

                if (!$response['error']) {
                    $response['orders'] = $content->orders;
                }

            } else {
                $response = [
                    'error'     => true,
                    'code'      => $request->getStatusCode(),
                    'message'   => $request->getReasonPhrase()
                ];
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
     * @return string
     */
    public function generateCheckSum ($type, $data): string {
        $checkSum = '';

        switch ($type) {
            case 'pre-pay':
                $checkSum .= 'PRE-PAY';
                break;
            case 'pay':
                $checkSum .= 'PAY';
                break;
        }

        $checkSum .= $data['amount'].$this->storeId.$data['externalId'].$data['buyerEmail'].$this->privateKey;

        return md5($checkSum);
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

        $options = !empty($params) ? array_merge($params, $default) : $default;

        return $options;
    }

    /**
     * formatContents()
     *
     * @param mixed|\Psr\Http\Message\ResponseInterface $request
     * @return array
     */
    private function formatContents ($request): array {
        $body   = $request->getBody();
        $format = $request->getHeader('Content-Type');
        if (!empty($format)) {
            if (in_array('application/json', $format)) {
                return [
                    'type'      => 'json',
                    'content'   => json_decode($body->getContents())
                ];
            }
        }

        return [
            'type'      => 'xml',
            'content'   => $body->getContents()
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

}