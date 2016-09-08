<?php
namespace Handler;

class Test
{

    public function testEcho($id,$name,$page)
    {
        return \Model\Test::instance()->testEcho($id,$name,$page);
    }

    public function testDb($id=0)
    {
       $obj = \Model\Test::instance();
       return $obj->testDb($id);
    }


}