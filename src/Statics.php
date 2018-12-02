<?php
namespace Moudarir\Binga;

class Statics {

    const API_VERSION   = '1.1';
    const BASE_URI_DEV  = 'http://preprod.binga.ma';
    const BASE_URI_PROD = 'http://api.binga.ma';
    const PAY_URI       = '/bingaApi/api/orders/pay';
    const PREPAY_URI    = '/bingaApi/api/orders/pay';
    const ORDER_TYPES   = ['pay' => "PAY", 'prepay' => "PRE-PAY"];
    const TIMEZONE      = 'GMT';
    const USED_METHODS  = ['GET', 'POST'];

}