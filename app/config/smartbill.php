<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SmartBill API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the SmartBill API. This should not need to be changed
    | unless SmartBill changes their API endpoint.
    |
    */

    'base_url' => env('SMARTBILL_BASE_URL', 'https://ws.smartbill.ro:8183/SBORO/api'),

    /*
    |--------------------------------------------------------------------------
    | SmartBill credentials
    |--------------------------------------------------------------------------
    |
    | Here you need to supply the credentials for the SmartBill platform
    | and the token you can be found at the following link:
    | https://cloud.smartbill.ro/core/integrari/
    | and the username is your login email.
    |
    */

    'username' => env('SMARTBILL_USERNAME', ''),
    'token' => env('SMARTBILL_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Company VAT code
    |--------------------------------------------------------------------------
    |
    | The VAT code for the SmartBill account, you can have multiple
    | companies in SmartBill, but at the moment currently
    | one account is supported in this package
    |
    */

    'vatCode' => env('SMARTBILL_VAT_CODE', ''),

    /*
    |--------------------------------------------------------------------------
    | Invoice Series
    |--------------------------------------------------------------------------
    |
    | Here you may define your invoice, proforma and receipt starting series
    | But first you need to define them in SmartBill at the following link
    | https://cloud.smartbill.ro/core/configurare/serii/
    |
    */

    'invoiceSeries' => env('SMARTBILL_INVOICE_SERIES', 'TEST-INV'),
    'proformaSeries' => env('SMARTBILL_PROFORMA_SERIES', 'TEST-PRO'),
    'receiptSeries' => env('SMARTBILL_RECEIPT_SERIES', 'TEST-REC'),

];
