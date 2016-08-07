<?php namespace hVenus\SFExpressAPI\BSP;

use hVenus\SFExpressAPI\Core\AbstractBSP;
use hVenus\SFExpressAPI\Support\Auth;

/**
 * 订单结果查询接口
 * 因 Internet 环境下，网络不是绝对可靠，用户系统下订单到顺丰后，不一定可以收到 BSP 返回的数据，
 * 此接口用于在未收到返回数据时，查询下订单（含筛选）接口客户订单当前 的处理情况。
 * Class OrderSearchService
 * @package hVenus\SFExpressAPI\BSP
 */
class OrderSearchService extends AbstractBSP
{

    public function OrderSearch($orderid) {
        $OrderSearch = '<OrderSearch orderid="'.$orderid.'" />';
        $xml = $this->buildXml($OrderSearch);
        $verifyCode = Auth::sign($xml, $this->config['checkword']);

        $params = array(
            'xml' => $xml,
            'verifyCode' => $verifyCode
        );

        $data = $this->ApiPost($params);
        return $this->OrderSearchResponse($data);
    }

    private function OrderSearchResponse($data) {
        return $this->getResponse($data, 'OrderResponse');
    }
}