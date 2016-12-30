<?php
namespace Controller;

class Index extends \Framework\CController
{

    //入口
    public function actionIndex()
    {
        $config =\Config\Mongo::getConfig();
        //$this->log(json_encode($config));
        $mongo = $this->mongo = new \MongoClient('mongodb://'.$config['host'].':'.$config['port']);

        $db = $mongo->selectDB('Statistics');
        $collection = $db->selectCollection('my_test_project');
        $list = $collection->find()->skip(0);

        $success_series_data = [];
        $fail_series_data = [];
        $success_time_series_data = [];
        $fail_time_series_data = [];
        foreach($list as $row){
            $timestamp = strtotime($row['time_minute']);
            $success_series_data[]  = "[".($timestamp*1000).",{$row['success_count']}]";
            $fail_series_data[]     = "[".($timestamp*1000).",{$row['fail_count']}]";
            $success_time_series_data[] = "[".($timestamp*1000).",{$row['success_cost_time']}]";
            $fail_time_series_data[]    = "[".($timestamp*1000).",{$row['fail_cost_time']}]";
        }

        $success_series_data = implode(',', $success_series_data);
        $fail_series_data = implode(',', $fail_series_data);
        $success_time_series_data = implode(',', $success_time_series_data);
        $fail_time_series_data = implode(',', $fail_time_series_data);

        $this->assign('success_series_data',$success_series_data);
        $this->assign('fail_series_data',$fail_series_data);
        $this->assign('success_time_series_data',$success_time_series_data);
        $this->assign('fail_time_series_data',$fail_time_series_data);
        return $this->display('index');
    }


    function formatTime($time)
    {
        return substr($time,0,4)."-".substr($time,4,2)."-".substr($time,6,2)." ".substr($time,8,2).":".substr($time,10,2).":".substr($time,12,2);
    }


}