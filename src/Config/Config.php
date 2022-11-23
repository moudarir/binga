<?php
namespace Moudarir\Binga\Config;

class Config {

    /**
     * Binga API Version
     */
    const API_VERSION = '1.1';
    const PROD_ENDPOINT = 'https://api.binga.ma';
    const DEV_ENDPOINT = 'http://preprod.binga.ma';
    const DEV_CONFIG = [
        'username' => 'Binga.ma',
        'password' => 'Binga',
        'store_id' => '4010',
        'private_key' => '4010653ddd7e9b8cece2779bbed423ce'
    ];
    const ORDERS_URI = '/bingaApi/api/orders';
    const ORDER_URI = self::ORDERS_URI.'/%s';
    const STORE_ORDERS_URI = self::ORDERS_URI.'/store/%s';
    const PAY_URI = self::ORDERS_URI.'/pay';
    const PREPAY_URI = self::ORDERS_URI.'/pay';
    const PAYMENT_TYPES = ['pay' => "PAY", 'prepay' => "PRE-PAY"];
    const TIMEZONE = 'GMT';
    const DEFAULT_ACCEPTED_FORMAT = 'json';

    /**
     * 'Accept' Formats
     */
    const ACCEPTED_FORMATS = [
        'json' => 'application/json',
        'jsonp' => 'application/javascript',
        'xml' => 'application/xml'
    ];

    /**
     * HTTP Status Codes
     */
    const STATUS_CODES = [
        100	=> 'Continue',
        101	=> 'Switching Protocols',

        200	=> 'OK',
        201	=> 'Created',
        202	=> 'Accepted',
        203	=> 'Non-Authoritative Information',
        204	=> 'No Content',
        205	=> 'Reset Content',
        206	=> 'Partial Content',

        300	=> 'Multiple Choices',
        301	=> 'Moved Permanently',
        302	=> 'Found',
        303	=> 'See Other',
        304	=> 'Pas de changement effectué.',
        305	=> 'Use Proxy',
        307	=> 'Temporary Redirect',

        400	=> 'Bad Request',
        401	=> 'Unauthorized',
        402	=> 'Payment Required',
        403	=> 'Forbidden',
        404	=> 'Not Found',
        405	=> 'Method Not Allowed',
        406	=> 'Not Acceptable',
        407	=> 'Proxy Authentication Required',
        408	=> 'Request Timeout',
        409	=> 'Conflict',
        410	=> 'Gone',
        411	=> 'Length Required',
        412	=> 'Precondition Failed',
        413	=> 'Request Entity Too Large',
        414	=> 'Request-URI Too Long',
        415	=> 'Unsupported Media Type',
        416	=> 'Requested Range Not Satisfiable',
        417	=> 'Expectation Failed',
        422	=> 'Unprocessable Entity',
        426	=> 'Upgrade Required',
        428	=> 'Precondition Required',
        429	=> 'Too Many Requests',
        431	=> 'Request Header Fields Too Large',

        500	=> 'Internal Server Error',
        501	=> 'Not Implemented',
        502	=> 'Bad Gateway',
        503	=> 'Service Unavailable',
        504	=> 'Gateway Timeout',
        505	=> 'HTTP Version Not Supported',
        511	=> 'Network Authentication Required'
    ];

}