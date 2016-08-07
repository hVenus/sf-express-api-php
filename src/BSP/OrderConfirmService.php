<?php namespace hVenus\SFExpressAPI\BSP;

use hVenus\SFExpressAPI\Core\AbstractBSP;
use hVenus\SFExpressAPI\Support\Auth;

/**
 * 该接口用于：
 *  客户在确定将货物交付给顺丰托运后，将运单上的一些重要信息，如快件重量通过此接口发送给顺丰。
 *  客户在发货前取消订单。
 * 注意：订单取消之后，订单号也是不能重复利用的。
 * Class OrderConfirmService
 * @package hVenus\SFExpressAPI\BSP
 */
class OrderConfirmService extends AbstractBSP
{

    /**
     * 确认订单
     * @param $orderid
     * @param $mailno
     * @param array $options
     * @return array|bool
     */
    public function OrderConfirm($orderid, $mailno, $options=array()) {
        return $this->OrderConfirmRequest($orderid, $mailno, 1, $options);
    }

    /**
     * 取消订单
     * @param $orderid
     * @param string $mailno
     * @param array $options
     * @return array|bool
     */
    public function OrderCancel($orderid, $mailno='', $options=array()) {
        return $this->OrderConfirmRequest($orderid, $mailno, 2, $options);
    }

    public function OrderConfirmRequest($orderid, $mailno, $dealtype, $options=array()) {
        $params = array();
        $params['dealtype '] = $dealtype;
        $params['orderid'] = $orderid;
        $params['mailno '] = $mailno;

        $OrderConfirm  = '';
        if (count($params)>0){
            $OrderConfirm = '<OrderConfirm ';
            foreach ($params as $k=>$v) {
                $OrderConfirm .= $k . '=' . '"' . $v . '" ';
            }
            $OrderConfirm = trim($OrderConfirm) . '>';
            if (count($options)>0){
                $OrderConfirm .= $this->OrderConfirmOption($options);
            }
            $OrderConfirm .= '</OrderConfirm>';
        } else {
            return false;
        }

        $xml = $this->buildXml($OrderConfirm);
        $verifyCode = Auth::sign($xml, $this->config['checkword']);

        $params = array(
            'xml' => $xml,
            'verifyCode' => $verifyCode
        );

        $data = $this->ApiPost($params);
        return $this->OrderConfirmResponse($data);
    }

    private function OrderConfirmOption($params) {
        $xml = '';
        if (count($params)>0) {
            foreach ($params as $item) {
                if(count($item)>0){
                    $root = '<OrderConfirmOption ';
                    foreach ($item as $k=>$v) {
                        $root .= $k.'="'.$v.'" ';
                    }
                    $root .='></OrderConfirmOption>';
                    $xml .= $root;
                }
            }
        }
        return $xml;
    }

    private function  OrderConfirmResponse($data) {
        return $this->getResponse($data, 'OrderConfirmResponse');
    }
}