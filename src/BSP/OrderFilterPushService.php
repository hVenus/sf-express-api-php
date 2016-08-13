<?php namespace hVenus\SFExpressAPI\BSP;

use hVenus\SFExpressAPI\Core\AbstractBSP;

/**
 * 人工筛单结果推送接口
 * 如果客户通过筛单功能得到的反馈结果为：1（人工确认），当完成人工筛单时，BSP 将 会通过此接口把人工筛单的结果推送给客户。
 * Class OrderFilterPushService
 * @package hVenus\SFExpressAPI\BSP
 */
class OrderFilterPushService extends AbstractBSP
{

    private $result = null;

    public function OrderFilterResult() {
        $post = $_POST;
        $data = urldecode($post);
        $xml = @simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        $arr = $this->arrarval($xml);
        if ('OrderFilterPushService' == $arr['@attributes']['service']) {
            $ret = array();
            if(count($data['Body'])>0){
                $d = $data['Body'];
                foreach($d as $k => $v){
                    $ret[] = $v['@attributes'];
                }
            }
            $this->result = $ret;
            return true;
        }
        return false;
    }

    public function getResult() {
        return $this->result;
    }

    public function OrderFilterResultResponse($status) {
        if($status == 'OK'){
            echo '<Response service="OrderFilterPushService"><Head>OK</Head></Response>';
        }
        if($status == 'ERR') {
            echo '<Response service="OrderFilterPushService"><Head>ERR</Head><ERROR code="4001">系统发生数据错误或运行时异常</ERROR></Response>';
        }
    }
}