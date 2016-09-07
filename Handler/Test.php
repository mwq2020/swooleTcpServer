<?php
namespace Handler;

class Test
{

    public function testEcho($id,$name,$page)
    {
        $data = func_get_args();
        $data['time'] = time();
        return \Model\Test::instance()->testEcho($id,$name,$page);
        //return $data;
    }

    public function testDb($id=0)
    {
       $obj = \Model\Test::instance();
       return $obj->testDb($id);
    }
}