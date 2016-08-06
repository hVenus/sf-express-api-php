<?php namespace hVenus\SFExpressAPI\OMS;
use hVenus\SFExpressAPI\Core\AbstractOMS;
use hVenus\SFExpressAPI\Support\Auth;
use hVenus\SFExpressAPI\Support\XML;

/**
 * 客户系统通过该接口向顺丰发送商品信息，该接口必须先于入库单接口、出库单接口调用。
 */
class ItemService extends AbstractOMS
{

    /**
     * Item Request
     * @param $item
     * @return array
     */
    public function ItemRequest($item) {
        $itemRequest = '<ItemRequest>';
        $itemRequest .= '<CompanyCode>'.$this->config['companycode'].'</CompanyCode>';
        $itemRequest .= $this->Items($item);
        $itemRequest .= '</ItemRequest>';

        $xml = $this->buildXml($itemRequest);
        $verifyCode = Auth::sign($xml, $this->config['checkword']);

        $params = array(
            'logistics_interface' => urlencode($xml),
            'data_digest' => urlencode($verifyCode)
        );

        $data = $this->ApiPost($params);

        return $this->ItemResponse($data);
    }

    public function Items($data) {
        $xml = '';
        if(count($data)>0){
            foreach($data as $goods){
                if(count($goods)>0){
                    $BarCode = array();
                    if(isset($goods['BarCode'])){
                        $BarCode = $goods['BarCode'];
                        unset($goods['BarCode']);
                    }
                    $ItemCategory = array();
                    if(isset($goods['ItemCategory'])){
                        $ItemCategory = $goods['ItemCategory'];
                        unset($goods['ItemCategory']);
                    }
                    $Containers = array();
                    if(isset($goods['Containers'])){
                        $Containers = $goods['Containers'];
                        unset($goods['Containers']);
                    }

                    $xml .= '<Item>';
                    foreach($goods as $k => $v){
                        $xml .= '<'.$k.'>'.$v.'</'.$k.'>';
                    }
                    $xml .= $this->BarCode($BarCode);
                    $xml .= $this->ItemCategory($ItemCategory);
                    $xml .= $this->Containers($Containers);
                    $xml .= '</Item>';
                }
            }
        }
        return '<Items>'.$xml.'</Items>';
    }

    public function BarCode($codes) {
        $data = '';
        if (count($codes)>0) {
            $i = 1;
            $data .= '<BarCode>';
            foreach ($codes as $item) {
                $data .= '<BarCode'.$i.'>'.$item.'</BarCode'.$i.'>';
                $i++;
            }
            $data .= '</BarCode>';
        }
        return $data;
    }

    public function ItemCategory($categories) {
        $data = '';
        if (count($categories)>0) {
            $i = 1;
            $data .= '<ItemCategory>';
            foreach ($categories as $category) {
                $data .= '<ItemCategory'.$i.'>'.$category.'</ItemCategory'.$i.'>';
                $i++;
            }
            $data .= '</ItemCategory>';
        }
        return $data;
    }

    public function Containers($containers) {
        $data = '';
        if (count($containers)>0) {
            $root = '';
            foreach ($containers as $container) {
                $root .= '<Container>';
                foreach ($container as $k => $v) {
                    $root .= '<'.$k.'>'.$v.'</'.$k.'>';
                }
                $root .= '</Container>';
            }
            $data = '<Containers>'.$root.'</Containers>';
        }
        return $data;
    }

    public function ItemResponse($data) {
        $ret = $this->ret;
        $xml = @simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        if ($xml){
            $ret = array();
            $ret['head'] = (string)$xml->Head;
            if ($xml->Head == 'OK'){
                $d = XML::parse($data);
                $ret['data'] = $d['Body'];
            }
            if ($xml->Head == 'ERR'){
                $ret['message'] = (string)$xml->Error;
                if (isset($xml->Error[0])) {
                    foreach ($xml->Error[0]->attributes() as $key => $val) {
                        $ret[$key] = (string)$val;
                    }
                }
            }
        }
        return $ret;
    }
}