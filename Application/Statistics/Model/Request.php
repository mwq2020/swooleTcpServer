<?php
namespace Model;

class Request
{

    //从统计服务器获取内容
    public function getStatisticsData($data)
    {
        $data = json_encode($data);
        $client = stream_socket_client("tcp://127.0.0.1:55858", $errno, $errstr, 2);
        $buffer = '';
        if (!$client) {
            return "$errstr ($errno)<br />\n";
        } else {
            fwrite($client, $this->encode($data));
            while (!feof($client)) {
                $buffer .= fgets($client, 1024);
            }
        }
        fclose($client);
        return $buffer;
    }

    //打包数据
    public function encode($string)
    {
        $packData = pack('N', strlen($string)).$string;
        return $packData;
    }

    //数据解包
    public function decode($buffer)
    {
        $length = unpack('N', $buffer)[1];
        $string = substr($buffer, -$length);
        $data = json_decode($string, true);
        return $data;
    }

}