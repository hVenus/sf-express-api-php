<?php namespace hVenus\SFExpressAPI\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AbstractBSP
{
    protected $config = [
        'server' => "http://bspoisp.sit.sf-express.com:11080/",
        'server_ssl' => "https://bspoisp.sit.sf-express.com:11443/",
        'ssl' => false,
        'uri' => 'bsp-oisp/sfexpressService',
        'checkword' => 'j8DzkIFgmlomPt0aLuwU',
        'accesscode' => 'BSPdevelop'
    ];

    private $SERVICE = array(
        'hVenus\SFExpressAPI\BSP\DeliverTmService'          => 'DeliverTmService',
        'hVenus\SFExpressAPI\BSP\IdentitySearchService'     => 'IdentitySearchService',
        'hVenus\SFExpressAPI\BSP\OrderConfirmService'       => 'OrderConfirmService',
        'hVenus\SFExpressAPI\BSP\OrderFilterPushService'    => 'OrderFilterPushService',
        'hVenus\SFExpressAPI\BSP\OrderFilterService'        => 'OrderFilterService',
        'hVenus\SFExpressAPI\BSP\OrderSearchService'        => 'OrderSearchService',
        'hVenus\SFExpressAPI\BSP\OrderService'              => 'OrderService',
        'hVenus\SFExpressAPI\BSP\OrderZDService'            => 'OrderZDService',
        'hVenus\SFExpressAPI\BSP\RoutePushService'          => 'RoutePushService',
        'hVenus\SFExpressAPI\BSP\RouteService'              => 'RouteService',
        'hVenus\SFExpressAPI\BSP\ScopeService'              => 'ScopeService',
    );

    protected $ret = array(
        'head' => "ERR",
        'message' => '系统错误',
        'code' => -1
    );

    public function __construct($params = null)
    {
        if (null != $params) {
            $this->config = array_merge($this->config, $params);
        }
    }

    /**
     * post data to server
     * @param array $query
     * @param array $header
     * @return string
     */
    public function ApiPost($query=array(), $header=array()) {
        try {
            if($this->config['ssl']){
                $client =  new Client(['base_uri' => $this->config['server_ssl']]);
            } else {
                $client =  new Client(['base_uri' => $this->config['server']]);
            }

            // must utf-8
            $header['charset'] = 'UTF-8';
            $header['Content-Type'] = 'application/x-www-form-urlencoded';

            // 数据需要以form_params提交，不然传过去时会附加多余的数据，导致签名验证失败。
            $response = $client->post(
                $this->config['uri'],
                array(
                    'form_params' => $query,
                    'headers' => $header,
                    'verify' => false
                )
            );
            $body = $response->getBody();
            $contents = $body->getContents();
            return $contents;
        } catch(RequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse()->getBody()->getContents();
            } else {
                return $e->getMessage();
            }
        }
    }

    /**
     * get request service name.
     * @param null $class
     * @return mixed
     */
    public function getServiceName($class=null) {
        if (empty($class)) {
            return $this->SERVICE[get_called_class()];
        }
        return $this->SERVICE[$class];
    }

    /**
     * build full xml.
     * @param $bodyData
     * @return string
     */
    public function buildXml($bodyData){
        $xml = '<Request service="'.$this->getServiceName(get_called_class()).'" lang="zh-CN">' .
            '<Head>'.$this->config['accesscode'].'</Head>' .
            '<Body>' . $bodyData . '</Body>' .
            '</Request>';
        return $xml;
    }

    public function getResponse($data, $name) {
        $ret = $this->ret;
        $xml = @simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        if ($xml){
            $ret = array();
            $ret['head'] = (string)$xml->Head;
            if ($xml->Head == 'OK'){
                $ret = array_merge($ret , $this->getData($xml, $name));
            }
            if ($xml->Head == 'ERR'){
                $ret = array_merge($ret , $this->getErrorMessage($xml));
            }
        }
        return $ret;
    }

    public function getErrorMessage($xml) {
        $ret = array();
        $ret['message'] = (string)$xml->ERROR;
        if (isset($xml->ERROR[0])) {
            foreach ($xml->ERROR[0]->attributes() as $key => $val) {
                $ret[$key] = (string)$val;
            }
        }
        return $ret;
    }

    public function getData($xml, $name) {
        if (isset($xml->Body->$name)){
            foreach ($xml->Body->$name as $v) {
                foreach ($v->attributes() as $key => $val) {
                    $ret[$key] = (string)$val;
                }
            }
        }
    }
}