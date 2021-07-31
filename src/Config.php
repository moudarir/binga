<?php
namespace Moudarir\Binga;

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
    const PAY_URI = '/bingaApi/api/orders/pay';
    const PREPAY_URI = '/bingaApi/api/orders/pay';
    const ORDER_TYPES = ['pay' => "PAY", 'prepay' => "PRE-PAY"];
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

}