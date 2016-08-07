<?php namespace hVenus\SFExpressAPI\BSP;

use hVenus\SFExpressAPI\Core\AbstractBSP;
use hVenus\SFExpressAPI\Support\Auth;

/**
 * 客户系统通过此接口向 BSP 发送主动的筛单请求，用于判断客户的收、派地址是否属于顺 丰的收派范围。
 * Class OrderFilterService
 * @package hVenus\SFExpressAPI\BSP
 */
class OrderFilterService extends AbstractBSP
{

    /**
     * 自动筛单
     * 系统根据地址库进行判断，并返回结果，系统无法检索到可派送的将返回不可派送。
     * @param string $d_address
     * @param string $orderid
     * @param array $options
     * @return array
     */
    public function OrderFilterAuto($d_address, $orderid='', $options=array()) {
        return $this->OrderFilter($d_address, 1, $orderid, $options);
    }

    /**
     * 人工筛单
     * 系统首先根据地址库判断，如果无法自动判断是否收派，系统将生成需要人工判断的任务，
     * 后续由人工处理，处理结束后，顺丰可主动推送给客户系统
     * @param string $d_address
     * @param string $orderid
     * @param array $options
     * @return array
     */
    public function OrderFilterManual($d_address, $orderid,$options=array()) {
        return $this->OrderFilter($d_address, 2, $orderid, $options);
    }

    public function OrderFilter($d_address, $filter_type=1, $orderid='', $options=array()) {
        $params = array();
        $params['d_address'] = $d_address;
        $params['filter_type '] = $filter_type;
        if(!empty($orderid)){
            $params['orderid '] = $orderid;
        }

        $OrderFilter = '<OrderFilter ';
        foreach ($params as $k=>$v) {
            $OrderFilter .= $k . '=' . '"' . $v . '" ';
        }
        $OrderFilter = trim($OrderFilter) . '>';
        if (count($options)>0){
            $OrderFilter .= $this->OrderFilterOption($options);
        }
        $OrderFilter .= '</OrderFilter>';

        $xml = $this->buildXml($OrderFilter);
        $verifyCode = Auth::sign($xml, $this->checkword);

        $params = array(
            'xml' => $xml,
            'verifyCode' => $verifyCode
        );

        $data = $this->ApiPost($params);

        return $this->OrderFilterResponse($data);
    }

    private function OrderFilterOption($params) {
        $xml = '';
        if (count($params)>0) {
            foreach ($params as $item) {
                if(count($item)>0){
                    $root = '<OrderFilterOption ';
                    foreach ($item as $k=>$v) {
                        $root .= $k.'="'.$v.'" ';
                    }
                    $root .='></OrderFilterOption>';
                    $xml .= $root;
                }
            }
        }
        return $xml;
    }

    private function OrderFilterResponse($data) {
        return $this->getResponse($data, 'OrderFilterResponse');
    }
}