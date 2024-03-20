<?php

namespace Moudarir\Binga;

use GuzzleHttp\Client as CltAlias;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;

class Client
{

    /**
     * @var CltAlias
     */
    private $client;

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
    private $input_format;

    /**
     * @var float|null
     */
    private $transfer_time;

    /**
     * @var string|null
     */
    private $effective_uri;

    public function __construct(?string $base_uri = null)
    {
        $config = $base_uri !== null ? ['base_uri' => $base_uri] : [];
        $this->client = new CltAlias($config);
    }

    /**
     * @param string $uri
     * @param string $method
     * @return Client
     * @throws BingaRequestException
     */
    public function request(string $uri, string $method): self
    {
        try {
            $this->request = $this->client->request($method, $uri, $this->getParams());
            $this->setContent()->setInputFormat();
            return $this;
        } catch (GuzzleException $exception) {
            throw new BingaRequestException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param string $uri
     * @return Client
     * @throws BingaRequestException
     */
    public function get(string $uri): self
    {
        return $this->request($uri, 'GET');
    }

    /**
     * @param string $uri
     * @return Client
     * @throws BingaRequestException
     */
    public function post(string $uri): self
    {
        return $this->request($uri, 'POST');
    }

    /**
     * @param string $uri
     * @return Client
     * @throws BingaRequestException
     */
    public function put(string $uri): self
    {
        return $this->request($uri, 'PUT');
    }

    /**
     * @param string $uri
     * @return Client
     * @throws BingaRequestException
     */
    public function patch(string $uri): self
    {
        return $this->request($uri, 'PATCH');
    }

    /**
     * @param string $uri
     * @return Client
     * @throws BingaRequestException
     */
    public function delete(string $uri): self
    {
        return $this->request($uri, 'DELETE');
    }

    /**
     * @param string $uri
     * @return Client
     * @throws BingaRequestException
     */
    public function options(string $uri): self
    {
        return $this->request($uri, 'OPTIONS');
    }

    /**
     * @param string $uri
     * @return Client
     * @throws BingaRequestException
     */
    public function head(string $uri): self
    {
        return $this->request($uri, 'HEAD');
    }

    /**
     * @return Client
     */
    public function resetParams(): self
    {
        $this->params = [];

        return $this;
    }

    /**
     * Getters
     */

    /**
     * @return ResponseInterface
     */
    public function getRequest(): ResponseInterface
    {
        return $this->request;
    }

    /**
     * @return array
     * @throws BingaResponseException
     */
    public function getResponse(): array
    {
        $statusCode = $this->getRequest()->getStatusCode();

        if ($statusCode >= 400) {
            throw new BingaResponseException(Config::STATUS_CODES[$statusCode] ?? 'Unknown Error', $statusCode);
        }

        $content = $this->getContent();
        $response = [];

        if (!empty($content)) {
            switch ($this->getInputFormat()) {
                case 'xml':
                    $response = (array)\simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
                    break;
                case 'json':
                case 'jsonp':
                    $response = \json_decode($this->getContent(), true);
                break;
            }
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        if (!\array_key_exists('on_stats', $this->params)) {
            $this->params['on_stats'] = function (TransferStats $stats) {
                $this->setTransferTime($stats->getTransferTime())->setEffectiveUri((string)$stats->getEffectiveUri());
            };
        }
        if (\array_key_exists('headers', $this->params)) {
            if (!\array_key_exists('User-Agent', $this->params['headers'])) {
                $this->params['headers']['User-Agent'] = null;
            }
            if (!\array_key_exists('Accept', $this->params['headers'])) {
                $this->params['headers']['Accept'] = Config::ACCEPTED_FORMATS[Config::DEFAULT_ACCEPTED_FORMAT];
            }
        } else {
            $this->params['headers'] = [
                'User-Agent' => null,
                'Accept' => Config::ACCEPTED_FORMATS[Config::DEFAULT_ACCEPTED_FORMAT],
            ];
        }

        return $this->params;
    }

    /**
     * @return string
     */
    public function getInputFormat(): string
    {
        return $this->input_format;
    }

    /**
     * @return float|null
     */
    public function getTransferTime(): ?float
    {
        return $this->transfer_time;
    }

    /**
     * @return string|null
     */
    public function getEffectiveUri(): ?string
    {
        return $this->effective_uri ?? null;
    }

    /**
     * Setters
     */

    /**
     * @param string $name
     * @param mixed $value
     * @return Client
     */
    public function setParam(string $name, $value): self
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return Client
     */
    public function setHeader(string $name, $value): self
    {
        $this->params['headers'][$name] = $value;
        return $this;
    }

    /**
     * @param string $format
     * @return Client
     */
    public function setAccept(string $format = 'json'): self
    {
        $formats = Config::ACCEPTED_FORMATS;

        if (\array_key_exists($format, $formats)) {
            return $this->setHeader('Accept', $formats[$format]);
        }

        return $this;
    }

    /**
     * @param string $format
     * @return Client
     */
    public function setContentType(string $format = 'json'): self
    {
        $formats = Config::ACCEPTED_FORMATS;

        if (\array_key_exists($format, $formats)) {
            return $this->setHeader('Content-Type', $formats[$format]);
        }

        return $this;
    }

    /**
     * @param string $data
     * @return Client
     */
    public function setBodyData(string $data): self
    {
        return $this->setParam('body', $data);
    }

    /**
     * @param array $data
     * @return Client
     */
    public function setQueryData(array $data): self
    {
        return $this->setParam('query', $data);
    }

    /**
     * @param array $data
     * @return Client
     */
    public function setJsonData(array $data): self
    {
        return $this->setParam('json', $data);
    }

    /**
     * @param array $data
     * @return Client
     */
    public function setFormData(array $data): self
    {
        return $this->setParam('form_params', $data);
    }

    /**
     * @param string $username
     * @param string $password
     * @return Client
     */
    public function setBasicAuth(string $username, string $password): self
    {
        return $this->setHeader('Authorization', 'Basic ' . \base64_encode("$username:$password"));
    }

    /**
     * @param string $token
     * @return Client
     */
    public function setBearerAuth(string $token): self
    {
        return $this->setHeader('Authorization', 'Bearer ' . $token);
    }

    /**
     * @param float|null $transfer_time
     * @return Client
     */
    public function setTransferTime(?float $transfer_time): self
    {
        $this->transfer_time = $transfer_time;

        return $this;
    }

    /**
     * @param string|null $effective_uri
     * @return Client
     */
    public function setEffectiveUri(?string $effective_uri): self
    {
        $this->effective_uri = $effective_uri;

        return $this;
    }

    /**
     * @return Client
     */
    private function setContent(): self
    {
        $requestBody = $this->getRequest()->getBody();
        $params = $this->getParams();
        if (\array_key_exists('stream', $params) && $params['stream'] === true) {
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
     * @return void
     */
    private function setInputFormat(): void
    {
        $contentTypes = $this->getRequest()->getHeader('Content-Type');

        if (!empty($contentTypes)) {
            $contentType = '';
            foreach ($contentTypes as $type) {
                $arr = \explode(';', $type);
                $contentType = $arr[0];
                break;
            }

            switch ($contentType) {
                case 'application/xml':
                case 'text/xml':
                    $this->input_format = 'xml';
                break;
                default:
                    $this->input_format = Config::DEFAULT_ACCEPTED_FORMAT;
                break;
            }
        } else {
            $this->input_format = Config::DEFAULT_ACCEPTED_FORMAT;
        }
    }
}
