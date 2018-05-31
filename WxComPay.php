<?php

class WxComPay
{
    //企业零钱支付接口地址
    private $payUrl = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
    //公众号appid
    private $mchAppId;
    //商户号--mchid--1
    private $mchId;
    //API密钥
    private $key;
    //$apiclientCert.pem 地址
    private $apiclientCert;
    //apiclientKey.pem 地址
    private $apiclientKey;

    /**
     * WxComPay constructor.
     * @param string $mchAppId
     * @param string $mchId
     * @param string $key
     * @param $apiclientCert
     * @param $apiclientKey
     */
    public function __construct(string $mchAppId, string $mchId, string $key, $apiclientCert, $apiclientKey)
    {
        $this->mchAppId = $mchAppId;
        $this->mchId = $mchId;
        $this->key = $key;
        $this->apiclientCert = $apiclientCert;
        $this->apiclientKey = $apiclientKey;
    }

    /**
     * 支付
     * @param string $openid 用户微信openid
     * @param string $reUserName 用户真名
     * @param string $amount 金额
     * @param string $partnerTradeNo 订单号
     * @param string $desc 描述信息
     * @return array                    接口返回相关信息
     */
    public function weixinTransferMoney(string $openid, string $amount, string $partnerTradeNo, string $desc, $reUserName = ''): array
    {
        $data = [
            'mch_appid' => $this->mchAppId,
            'mchid' => $this->mchId,
            'nonce_str' => $this->get_unique_value(),
            'partner_trade_no' => $partnerTradeNo,
            'openid' => $openid,
            'check_name' => 'NO_CHECK',
            'amount' => $amount,
            'desc' => $desc,
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
        ];

        if ($reUserName) {
            $data['re_user_name'] = $reUserName;
        }

        //API密钥，输入你的appsecret
        $appsecret = $this->key;
        $data = array_filter($data);
        ksort($data);
        $str = "";
        foreach ($data as $k => $v) {
            $str .= $k . "=" . $v . "&";
        }
        $str .= "key=" . $appsecret;
        $data['sign'] = strtoupper(MD5($str));
        //1.将请求数据由数组转换成xml
        $xml = $this->array2xml($data);
        //2.进行请求操作
        $res = $this->curl($xml, $this->payUrl);
        //3.将请求结果由xml转换成数组
        $arr = $this->xml2array($res);

        return $arr;
    }

    // 生成32位唯一随机字符串
    private function get_unique_value()
    {
        $str = uniqid(mt_rand(), 1);
        $str = sha1($str);
        return md5($str);
    }

    //进行curl操作
    private function curl($param = "", $url)
    {
        $postUrl = $url;
        $curlPost = $param;
        //初始化curl
        $ch = curl_init();
        //抓取指定网页
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, 1);
        // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        //这个是证书的位置
        curl_setopt($ch, CURLOPT_SSLCERT, $this->apiclientCert);
        //这个也是证书的位置
        curl_setopt($ch, CURLOPT_SSLKEY, $this->apiclientKey);
        //运行curl
        $data = curl_exec($ch);
        //关闭curl
        curl_close($ch);
        //返回结果
        return $data;
    }

    // 将xml转换成数组
    private function xml2array($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $arr = json_decode(json_encode($xmlstring), true);
        return $arr;
    }

    // 将数组转换成xml
    private function array2xml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $k => $v) {
            $xml .= "<" . $k . ">" . $v . "</" . $k . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

}