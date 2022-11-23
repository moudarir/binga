<?php
namespace Moudarir\Binga\Http;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;
use Moudarir\Binga\Config\Config;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class HttpClient {

    /**
     * @var Client
     */
    private static $client;

    /**
     * @var ResponseInterface
     */
    private $request;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $accept = Config::DEFAULT_ACCEPTED_FORMAT;

    /**
     * @var string
     */
    private $output_format;

    /**
     * @var float|null
     */
    private $transfer_time;

    /**
     * @var string|null
     */
    private $effective_uri;

    /**
     * HttpClient constructor.
     *
     * @param string|null $base_uri
     */
    public function __construct (?string $base_uri = null)
    {
        if (!isset(self::$client)) {
            $config = $base_uri !== null ? ['base_uri' => $base_uri] : [];
            self::$client = new Client($config);
        }
    }

    /**
     * @return ResponseInterface
     */
    public function getRequest (): ResponseInterface
    {
        return $this->request;
    }

    /**
     * @return array|string
     */
    public function getResponse ()
    {
        $request = $this->getRequest();
        $content = $this->getContent();
        $statusCode = $request->getStatusCode();
        $formatted = array_key_exists('formatted', $this->getParams())
            ? $this->getParams()['formatted']
            : true;

        if (!empty($content)) {
            if ($formatted === false) {
                return $content;
            }

            switch ($this->getOutputFormat()) {
                case 'json':
                case 'array':
                    $response = json_decode($content, true);
                    break;
                case 'xml':
                    $response = $content;
                    break;
                default:
                    $response = [];
                    break;
            }

            if ($statusCode >= 400) {
                $response['error'] = true;
            }
        } else {
            $response = [
                'error' => $statusCode >= 400,
                'message' => Config::STATUS_CODES[$statusCode]
            ];
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getContent (): string
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getParams (): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getOutputFormat (): string
    {
        return $this->output_format;
    }

    /**
     * @return float|null
     */
    public function getTransferTime (): ?float
    {
        return $this->transfer_time;
    }

    /**
     * @return string|null
     */
    public function getEffectiveUri (): ?string
    {
        return $this->effective_uri ?? null;
    }

    /**
     * @param string $uri
     * @param string $method
     * @return self
     * @throws GuzzleException
     */
    public function setRequest (string $uri, string $method = 'GET'): self
    {
        try {
            $this->request = self::$client->request($method, $uri, $this->getParams());
            $this->setContent()->setOutputFormat();
        } catch (GuzzleException $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @param array $params
     * @return self
     */
    public function setParams (array $params = []): self
    {
        if (empty($this->params)) {
            $this->params = [
                'headers' => [
                    'User-Agent' => null,
                    'Accept' => Config::ACCEPTED_FORMATS[$this->accept]
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->setTransferTime($stats->getTransferTime())
                        ->setEffectiveUri((string)$stats->getEffectiveUri());
                },
            ];
        }

        $this->params = !empty($params) ? array_merge_recursive($this->params, $params) : $this->params;

        return $this;
    }

    /**
     * @param string $accept
     * @return self
     */
    public function setAccept (string $accept = 'json'): self
    {
        $formats = Config::ACCEPTED_FORMATS;

        if (array_key_exists($accept, $formats)) {
            $this->params['headers']['Accept'] = $formats[$accept];
        }

        return $this;
    }

    /**
     * @param float|null $transfer_time
     * @return self
     */
    public function setTransferTime (?float $transfer_time): self
    {
        $this->transfer_time = $transfer_time;

        return $this;
    }

    /**
     * @param string|null $effective_uri
     * @return self
     */
    public function setEffectiveUri (?string $effective_uri): self
    {
        $this->effective_uri = $effective_uri;

        return $this;
    }

    /**
     * @return self
     */
    private function setContent (): self
    {
        $requestBody = $this->getRequest()->getBody();
        $params = $this->getParams();
        if (array_key_exists('stream', $params) && $params['stream'] === true) {
            $content = '';

            while (!$requestBody->eof()) {
                $content .= $requestBody->read(1024);
            }

            $requestBody->close();
        } else {
            $content = $requestBody->getContents();
        }

        $this->content = $content;

        return $this;
    }

    /**
     * @return self
     */
    private function setOutputFormat (): self
    {
        $contentTypes = $this->getRequest()->getHeader('Content-Type');

        if (!empty($contentTypes)) {
            $contentType = '';

            foreach ($contentTypes as $type) {
                $arr = explode(';', $type);
                $contentType = $arr[0];
                break;
            }

            switch ($contentType) {
                case 'application/xml':
                case 'text/xml':
                    $this->output_format = 'xml';
                    break;
                case 'application/json':
                case 'application/javascript':
                default:
                    $this->output_format = Config::DEFAULT_ACCEPTED_FORMAT;
                    break;
            }
        } else {
            $this->output_format = Config::DEFAULT_ACCEPTED_FORMAT;
        }

        return $this;
    }

}