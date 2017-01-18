<?php
namespace Controller;

class Chats extends \Framework\CController
{

    //入口
    public function actionIndex()
    {
        return $this->display('index');
    }

    /**
     * 获取当前时间戳的访问数据
     */
    public function actionSyncdata()
    {
        $timestamp = intval($_POST['timestamp']/1000);
        $timestamp = $timestamp>0 ? $timestamp : time();
        $params = array();
        $params['action'] = 'sync_statistics_data';
        $params['timestamp'] = time()-1;
        $statistics_data = (new \Model\Request)->getStatisticsData($params);

        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);

        $redis_key = 'second_'.$timestamp;
        $list = $redis->lRange($redis_key,0,1000);
        $newList = array();
        foreach($list as $row){
            array_push($newList,json_decode($row,true));
        }

        echo json_encode(array('timestamp' => $timestamp,'statistics_data' => $statistics_data,'log_list'=>$newList));
    }

}