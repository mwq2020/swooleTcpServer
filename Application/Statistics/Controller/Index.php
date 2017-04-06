<?php
namespace Controller;

class Index extends \Framework\CController
{

    //入口
    public function actionIndex()
    {
        //return $this->exitOut('http://127.0.0.1:55757/logger/index');
        //return $this->exitOut(array('aaaa' => 'bbbb','cccc'=>'ddddd'));

        $start_timestamp = isset($_GET['date']) && !empty($_GET['date']) ? strtotime($_GET['date']) : strtotime(date('Y-m-d'));
        $end_timestamp = $start_timestamp + (24*3600) - 1;
        if(PHP_VERSION >= 7){
            $manager = \Mongo\MongoDbConnection::instance('statistics')->getMongoManager();
            $where = array('time_stamp' => [ '$gte' => $start_timestamp, '$lte' => $end_timestamp, ]);
            $options = array( 'skip' => 0, );
            $collection = new \MongoDB\Collection($manager, 'Statistics','All_Statistics');
            $dataList = $collection->find($where, $options);
            $list = array();
            foreach($dataList as $row) {
                array_push($list,$row);
            }
        } else {
            $mongo = \Mongo\Connection::instance('statistics')->getMongoConnection();
            $db = $mongo->selectDB('Statistics');
            $collection = $db->selectCollection('All_Statistics');
            $list = $collection->find(array("time_stamp" => array('$gt'=>$start_timestamp,'$lt'=>$end_timestamp)))->skip(0);
        }

        $success_series_data = [];
        $fail_series_data = [];
        $success_time_series_data = [];
        $fail_time_series_data = [];

        /*
        foreach($list as $row){
            $timestamp = $row['time_stamp'];
            $success_series_data[$timestamp]  = "[".($timestamp*1000).",{$row['success_count']}]";
            $fail_series_data[$timestamp]     = "[".($timestamp*1000).",{$row['fail_count']}]";

            if($row['success_count'] > 0){
                $success_time_series_data[$timestamp] = "[".($timestamp*1000).",".($row['success_cost_time']/$row['success_count'])."]";
            }
            if($row['fail_count'] > 0){
                $fail_time_series_data[$timestamp]    = "[".($timestamp*1000).",".($row['fail_cost_time']/$row['fail_count'])."]";
            }
        }

        for($i = $start_timestamp; $i < $end_timestamp; $i += 60){
            if(!isset($success_series_data[$i])){
                $fail_series_data[$i] = "[".($i*1000).",0]";
                $fail_time_series_data[$i]    = "[".($i*1000).",0]";
            }
        }
        */

        //整理成每5分钟数据，看起来比较清晰些
        $table_data = '';
        $list_data_5 = array();//每5分钟的数据统计
        foreach($list as $row){
            $five_minute_time = $this->formatTimeIn5($row['time_stamp']);
            if(isset($success_series_data[$five_minute_time])){
                $success_series_data[$five_minute_time]        += $row['success_count'];
                $fail_series_data[$five_minute_time]           += $row['fail_count'];
                $success_time_series_data[$five_minute_time]   += $row['success_cost_time'];
                $fail_time_series_data[$five_minute_time]      += $row['fail_cost_time'];
            } else {
                $success_series_data[$five_minute_time]        = $row['success_count'];
                $fail_series_data[$five_minute_time]           = $row['fail_count'];
                $success_time_series_data[$five_minute_time]   = $row['success_cost_time'];
                $fail_time_series_data[$five_minute_time]      = $row['fail_cost_time'];
            }

            if(isset($list_data_5[$five_minute_time])){
                $list_data_5[$five_minute_time]['total_count']          += ($row['success_count']+$row['fail_count']);
                $list_data_5[$five_minute_time]['success_count']        += $row['success_count'];
                $list_data_5[$five_minute_time]['fail_count']           += $row['fail_count'];
                $list_data_5[$five_minute_time]['success_cost_time']    += $row['success_cost_time'];
                $list_data_5[$five_minute_time]['fail_cost_time']       += $row['fail_cost_time'];
            } else {
                $list_data_5[$five_minute_time]['total_count']          = ($row['success_count']+$row['fail_count']);
                $list_data_5[$five_minute_time]['success_count']        = $row['success_count'];
                $list_data_5[$five_minute_time]['fail_count']           = $row['fail_count'];
                $list_data_5[$five_minute_time]['success_cost_time']    = $row['success_cost_time'];
                $list_data_5[$five_minute_time]['fail_cost_time']       = $row['fail_cost_time'];
            }


        }

        //整理表格统计数据
        foreach($list_data_5 as $date => $row){
            $precent = ($row['total_count']) > 0 ? round(($row['success_count']/($row['total_count']))*100) : 0;
            $html_class = 'class="danger"';
            if($row['total_count'] == 0) {
                $html_class = '';
            } elseif($precent>=99.99)  {
                $html_class = 'class="success"';
            } elseif($precent>=99) {
                $html_class = '';
            } elseif($precent>=98) {
                $html_class = 'class="warning"';
            }
            $table_data .= "\n<tr $html_class>
                       <td>".date('Y-m-d H:i:s',$date)."</td>
                       <td>".$row['total_count']."</td>
                        <td>".$row['total_count'] <= 0 ? 0 : round(($row['success_cost_time']+$row['fail_cost_time'])/$row['total_count'],6)."</td>
                        <td>{$row['success_count']}</td>
                        <td>".$row['success_count']<=0 ? 0 : round($row['success_cost_time']/$row['success_count'],6)."</td>
                        <td>{$row['fail_count']}</td>
                        <td>".$row['fail_count'] <=0 ? 0 : round($row['fail_cost_time']/$row['fail_count'],6)."</td>
                        <td>{$precent}%</td>
                    </tr> ";
        }

        foreach($success_series_data as $five_minute_time => $row){
            $success_series_data[$five_minute_time]        = "[".($five_minute_time*1000).",{$row}]";
        }
        foreach($fail_series_data as $five_minute_time => $row){
            $fail_series_data[$five_minute_time]        = "[".($five_minute_time*1000).",{$row}]";
        }
        foreach($success_time_series_data as $five_minute_time => $row){
            $success_time_series_data[$five_minute_time]        = "[".($five_minute_time*1000).",{$row}]";
        }
        foreach($fail_time_series_data as $five_minute_time => $row){
            $fail_time_series_data[$five_minute_time]        = "[".($five_minute_time*1000).",{$row}]";
        }

        for($i = $start_timestamp; $i < $end_timestamp; $i += 300){
            if(!isset($success_series_data[$i])){
                $fail_series_data[$i] = "[".($i*1000).",0]";
                $fail_time_series_data[$i]    = "[".($i*1000).",0]";
            }
        }

        ksort($success_series_data);
        ksort($fail_series_data);
        ksort($success_time_series_data);
        ksort($fail_time_series_data);

        $success_series_data = implode(',', $success_series_data);
        $fail_series_data = implode(',', $fail_series_data);
        $success_time_series_data = implode(',', $success_time_series_data);
        $fail_time_series_data = implode(',', $fail_time_series_data);

        $this->assign('statistics_title',date('Y-m-d').'整体');
        $this->assign('success_series_data',$success_series_data);
        $this->assign('fail_series_data',$fail_series_data);
        $this->assign('success_time_series_data',$success_time_series_data);
        $this->assign('fail_time_series_data',$fail_time_series_data);
        $this->assign('table_data',$table_data);
        return $this->display('index');
    }

    public function actionTest()
    {
        return $this->display('test');
    }

    /**
     * 根据时间戳转成每5分钟的时间戳
     * @param $stamp
     * @param bool|true $is_timestamp
     * @return bool|int|string
     */
    private function formatTimeIn5($stamp,$is_timestamp = true)
    {
        $i = date('i',$stamp);
        if($i%5 ==0){
            $new_time = date('Y-m-d H:i:s',$stamp);
        } else {
            if($i<5){
                $i = substr($i,0,-1)."5";
                $new_time = date('Y-m-d H:'.$i.":s",$stamp);
            } elseif($i>55){
                $new_time = date('Y-m-d H:00:00',$stamp+600);
            }  else {
                if(substr($i,-1) < 5){
                    $i = substr($i,0,-1)."5";
                } else {
                    $i = (substr($i,0,-1) +1)."0";
                }
                $new_time = date('Y-m-d H:'.$i.':00',$stamp);
            }
        }

        return $is_timestamp == true ? strtotime($new_time) : $new_time;
    }

}