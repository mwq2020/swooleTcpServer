<?php
namespace Controller;

class Index extends \Framework\CController
{

    //入口
    public function actionIndex()
    {
        $config =\Config\Mongo::getConfig();
        $mongo = $this->mongo = new \MongoClient('mongodb://'.$config['host'].':'.$config['port']);

        $start_timestamp = strtotime(date('Y-m-d'));
        $end_timestamp = $start_timestamp + (24*3600) - 1;

        $db = $mongo->selectDB('Statistics');
        $collection = $db->selectCollection('All_Statistics');
        $list = $collection->find(array("time_stamp" => array('$gt'=>$start_timestamp,'$lt'=>$end_timestamp)))->skip(0);

        $success_series_data = [];
        $fail_series_data = [];
        $success_time_series_data = [];
        $fail_time_series_data = [];
        foreach($list as $row){
            $timestamp = $row['time_stamp'];
            $success_series_data[$timestamp]  = "[".($timestamp*1000).",{$row['success_count']}]";
            $fail_series_data[$timestamp]     = "[".($timestamp*1000).",{$row['fail_count']}]";
            $success_time_series_data[$timestamp] = "[".($timestamp*1000).",{$row['success_cost_time']}]";
            $fail_time_series_data[$timestamp]    = "[".($timestamp*1000).",{$row['fail_cost_time']}]";
        }
        for($i = $start_timestamp; $i < $end_timestamp; $i += 60){
            if(!isset($success_series_data[$i])){
                $fail_series_data[$i] = "[".($i*1000).",0]";
                $fail_time_series_data[$i]    = "[".($i*1000).",0]";
            }
        }

        $success_series_data = implode(',', $success_series_data);
        $fail_series_data = implode(',', $fail_series_data);
        $success_time_series_data = implode(',', $success_time_series_data);
        $fail_time_series_data = implode(',', $fail_time_series_data);

        $this->assign('statistics_title',date('Y-m-d').'整体');
        $this->assign('success_series_data',$success_series_data);
        $this->assign('fail_series_data',$fail_series_data);
        $this->assign('success_time_series_data',$success_time_series_data);
        $this->assign('fail_time_series_data',$fail_time_series_data);
        return $this->display('index');
    }

    public function actionTest()
    {
        return $this->display('test');
    }


    function formatTime($time)
    {
        return substr($time,0,4)."-".substr($time,4,2)."-".substr($time,6,2)." ".substr($time,8,2).":".substr($time,10,2).":".substr($time,12,2);
    }


}