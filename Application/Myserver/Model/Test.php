<?php
namespace Model;

class Test extends Base
{

    public function testEcho($id,$name,$page)
    {
        $data = func_get_args();
        $data['time'] = time();
        return $data;
    }


    public function testDb($id)
    {
        $db = \Db\Connection::instance();
        if(empty($id)){
            $info = $db->read('test')->select('*')->from('custom_sku_notice')->queryAll();
        } else {
            $info = $db->read('test')->select('*')->from('custom_sku_notice')->where('id='.$id)->queryRow();
        }
        return $info;
    }

    public function testException()
    {
        throw new \Exception('fff');
    }

}