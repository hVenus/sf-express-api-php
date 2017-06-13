<?php
namespace hVenus\SFExpressAPI\BSP;

use DOMDocument;
use hVenus\SFExpressAPI\Core\AbstractBSP;
use hVenus\SFExpressAPI\Support\Helper;
use Sabre;

class RouteService extends AbstractBSP
{
    use Helper;

    /**
     * 顺丰路由查询
     * @param string $tracking_number   查询号：
     *                                   如果 tracking_type=1，则此值为顺丰运单号
     *                                   如果 tracking_type=2，则此值为客户订单号
     *                                  如果有多个单号，以逗号分隔，
     *                                  如”123,124,125”。
     * @param int $tracking_type 查询号类别：
     *                             1：根据顺丰运单号查询，order 节点中 tracking_number将被当作顺丰运单号处理
     *                             2：根据客户订单号查询，order 节点中 tracking_number将被当作客户订单号处理
     * @param int $method_type 路由查询类别：
     *                           1：标准路由查询
     *                           2：定制路由查询
     * @return array
     */
    public function Routes($tracking_number, $tracking_type=1, $method_type=1) {
        // 请求的报文
        /*
            <Request service='RouteService' lang='zh-CN'>
                <Head>BSPdevelop</Head>
                <Body>
                    <RouteRequest
                    tracking_type='1'
                    method_type='1'
                    tracking_number='444003077898'/>
                </Body>
            </Request>
        */

        $RouteRequest = '<RouteRequest tracking_type="'.$tracking_type.'" method_type="'.$method_type.'" tracking_number="'.$tracking_number.'" />';

        //$xml = $this->MakeXML($tracking_number, $tracking_type, $method_type);

        $xml = $this->buildXml($RouteRequest);

        $verifyCode = $this->sign($xml, $this->config['checkword']);

        $params = array(
            'xml' => $xml,
            'verifyCode' => $verifyCode
        );

        $data = $this->ApiPost($params);

        return $this->RouteResponse($data);
    }

    /**
     * 生成对应的报文(无用)
     * @param $tracking_number
     * @param $tracking_type
     * @param $method_type
     * @return string
     */
    protected function MakeXML($tracking_number, $tracking_type, $method_type) {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc -> formatOutput = true;

        $root = $doc->createElement('Request');
        $root->setAttribute('service','RouteService');
        $root->setAttribute('lang','zh-CN');

        $head = $doc->createElement('Head');
        $head->appendChild($doc->createTextNode($this->config['accesscode']));
        $root->appendChild($head);

        $body = $doc->createElement('Body');//新建节点
        $request = $doc->createElement('RouteRequest');//新建节点
        $request->setAttribute('tracking_type',$tracking_type);
        $request->setAttribute('method_type',$method_type);
        $request->setAttribute('tracking_number',$tracking_number);

        $body->appendChild($request);

        $root->appendChild($body);

        $doc->appendChild($root);

        $ret =  $doc->saveXML();

        return $ret;
    }

    /**
     * 获取结果
     * @param $xml
     * @return array
     */
    protected function RouteResponse($xml) {
        $data = $this->LoadXml($xml);

        $service = $data['attributes']['service'];

        $head =  $data['Head'];

        if ($head == "OK") {
            $result = [];
            $t = [];

            $routeResponses = $data['Body']['RouteResponse'];
            if (count($routeResponses['Route'])>0) {
                $routes = $routeResponses['Route'];
                foreach($routes as $v) {
                    $tmp = [];
                    foreach($v['attributes'] as $k=>$a) {
                        $tmp[$k] = $a;
                    }
                    $t[$data['Body']['RouteResponse']['attributes']['mailno']][] = $tmp;
                }
            }
            $result['data'] = $t;
        } else if ($head == "ERR") {
            $result['code'] =  $data['ERROR']['attributes']['code'];
            $result['message'] =  $data['ERROR']['_value'];
        } else {
            $result = [];
        }

        return array_merge(['service'=>$service,'head'=>$head], $result);
    }
}