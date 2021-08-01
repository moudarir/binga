<?php
namespace Moudarir\Binga;

use GuzzleHttp\Client as CltAlias;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Client {

    /**
     * @var CltAlias
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
    private $outputFormat;

    /**
     * Client constructor.
     *
     * @param string $endpoint
     */
    public function __construct (string $endpoint) {
        if (!isset(self::$client)):
            self::$client = new CltAlias(['base_uri' => $endpoint]);
        endif;
    }

    /**
     * @param string $method GET | POST
     * @param string $uri
     * @return self|null
     * @throws GuzzleException
     */
    public function request (string $method, string $uri): ?self {
        $this->request = self::$client->request($method, $uri, $this->params);
        $this->setContent();
        $this->setOutputFormat();

        return $this;
    }

    /**
     * @param array $params
     * @return self
     */
    public function setParams (array $params = []): self {
        $formats = Config::ACCEPTED_FORMATS;
        $acceptFormat = array_key_exists($this->accept, $formats) ? $formats[$this->accept] : $formats[Config::DEFAULT_ACCEPTED_FORMAT];
        $default = [
            'headers' => [
                'Accept' => $acceptFormat
            ]
        ];
        $this->params = !empty($params) ? array_merge($default, $params) : $default;

        return $this;
    }

    /**
     * @param string $accept
     * @return self
     */
    public function setAccept (string $accept = 'json'): self {
        $this->accept = $accept;
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getRequest () {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getResponse (): array {
        switch ($this->outputFormat):
            case 'json':
            case 'jsonp':
                $response = json_decode($this->content, true);
                break;
            case 'xml':
                $response = (array)simplexml_load_string($this->content, 'SimpleXMLElement', LIBXML_NOCDATA);
                break;
            default:
                $response = [];
                break;
        endswitch;

        return $response;
    }

    /**
     * @return string
     */
    public function getContent (): string {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getParams (): array {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getOutputFormat (): string {
        return $this->outputFormat;
    }

    /**
     * @return string
     */
    public function getAccept (): string {
        return $this->accept;
    }

    /**
     * @return self
     */
    private function setContent (): self {
        $requestBody = $this->request->getBody();
        if (isset($this->params['stream']) && $this->params['stream'] === true):
            $content = '';
            while (!$requestBody->eof()):
                $content .= $requestBody->read(1024);
            endwhile;
            $requestBody->close();
        else:
            $content = $requestBody->getContents();
        endif;

        $this->content = $content;

        return $this;
    }

    /**
     * @return self
     */
    private function setOutputFormat (): self {
        $contentTypes = $this->request->getHeader('Content-Type');

        if (!empty($contentTypes)):
            $contentType = '';
            foreach ($contentTypes as $type):
                $arr = explode(';', $type);
                $contentType = $arr[0];
                break;
            endforeach;

            switch ($contentType):
                case 'application/xml':
                case 'text/xml':
                    $this->outputFormat = 'xml';
                    break;
                case 'application/json':
                case 'application/javascript':
                default:
                    $this->outputFormat = Config::DEFAULT_ACCEPTED_FORMAT;
                    break;
            endswitch;
        else:
            $this->outputFormat = Config::DEFAULT_ACCEPTED_FORMAT;
        endif;

        return $this;
    }

}