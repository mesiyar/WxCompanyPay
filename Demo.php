<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/31
 * Time: 16:58
 */
require_once 'WxComPay.php';


$mchAppID = '';//微信appid
$mchID = '';//商户号
$key = '';//分配的key
$apiclientCert = '';//apiclient_cert.pem的实际地址
$apiclientKey = '';//apiclient_key.pem的实际地址
$openid = '';
$amount = '';
$order = '';
$desc = '';
$wxPay = new WxComPay($mchAppID, $mchID, $key, $apiclientCert,$apiclientKey);
$result = $wxPay->weixinTransferMoney($openid,$amount,$order,$desc);
var_dump($result);