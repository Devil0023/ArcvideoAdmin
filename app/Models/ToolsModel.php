<?php

namespace App\Models;

Class ToolsModel
{
    //curl get
    public function curl_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $dom = curl_exec($ch);
        curl_close($ch);
        return $dom;
    }

    //curl post
    public function curl_post($url, $postdata){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        return $result;
    }

    public static function checkSign($appsecret, $data, $sign){
        unset($data['sign']);

        ksort($data);

        $string = '';

        foreach ($data as $k => $v) {
            $string.= $k.$v;
        }

        $string .= $appsecret;

        return strtoupper(sha1($string)) === $sign;

    }
}