<?php
namespace hVenus\SFExpressAPI\Support;


class Auth
{
    /**
     * 计算验证码
     * data 是拼接完整的报文XML
     * checkword 是顺丰给的接入码
     *
     * @param string $data
     * @param string $checkword
     * @return string
     */
    public static function sign($data, $checkword) {
        $string = trim($data).trim($checkword);
        $md5 = md5(mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string)), true);
        $sign = base64_encode($md5);
        return $sign;
    }
}