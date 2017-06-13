<?php
namespace hVenus\SFExpressAPI\Support;


use DOMDocument;

trait Helper
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

    /**
     * 读取xml
     * @param $xml
     * @return array
     */
    protected function LoadXml($xml) {
        $obj = new DOMDocument();
        $obj->loadXML($xml);
        $ret = $this->xml_to_array($obj->documentElement);
        return $ret;
    }

    /**
     * 转xml到数组
     * @param $root
     * @return array
     */
    protected function xml_to_array($root) {
        $result = array();

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }
            }
            $groups = array();
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = self::xml_to_array($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = self::xml_to_array($child);
                }
            }
        }

        return $result;
    }


}