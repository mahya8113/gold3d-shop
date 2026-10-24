<?php
return [
 'merchant_id'=>env('ZARINPAL_MERCHANT_ID'),
 'sandbox'=>(bool)env('ZARINPAL_SANDBOX',true),
 'api_url'=>env('ZARINPAL_SANDBOX',true)?'https://sandbox.zarinpal.com/pg/v4/payment':'https://payment.zarinpal.com/pg/v4/payment',
 'startpay_url'=>env('ZARINPAL_SANDBOX',true)?'https://sandbox.zarinpal.com/pg/StartPay':'https://www.zarinpal.com/pg/StartPay',
];
