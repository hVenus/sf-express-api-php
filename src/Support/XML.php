<?php
namespace hVenus\SFExpressAPI\Support;


class XML
{
    /**
     * XML to array.
     *
     * @param string $xml XML string
     *
     * @return array|\SimpleXMLElement
     */
    public static function parse($xml)
    {
        $data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);

        if (is_object($data) && get_class($data) === 'SimpleXMLElement') {
            $data = self::arrarval($data);
        }

        return $data;
    }

    /**
     * XML to object
     * @param $xml
     * @return \SimpleXMLElement
     */
    public static function parseRaw($xml)
    {
        $data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        return $data;
    }

    /**
     * Object to array.
     *
     * @param string $data
     *
     * @return array
     */
    private static function arrarval($data)
    {
        if (is_object($data) && get_class($data) === 'SimpleXMLElement') {
            $data = (array) $data;
        }

        if (is_array($data)) {
            foreach ($data as $index => $value) {
                $data[$index] = self::arrarval($value);
            }
        }

        return $data;
    }


}
