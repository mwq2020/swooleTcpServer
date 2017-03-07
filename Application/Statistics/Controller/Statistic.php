<?php
namespace Controller;

class Statistic extends \Framework\CController
{

    public function actionIndex()
    {
        $error_msg = '';
        $success_series_data = [];
        $fail_series_data = [];
        $success_time_series_data = [];
        $fail_time_series_data = [];
        try {

            if(PHP_VERSION >= 7){
                $manager = (new \MongoDB\Client('statistics'))->getManager();
                $collectionListObj = (new \MongoDB\Database($manager,'Statistics'))->listCollections();
                $collectionList = array();
                foreach($collectionListObj as $row){
                    if($row->getName() == 'system.indexes'){
                        continue;
                    }
                    array_push($collectionList,$row->getName());
                }
            } else {
                $mongo = \Mongo\Connection::instance('statistics')->getMongoConnection();
                $db = $mongo->selectDB('Statistics');
                $collectionList = $db->getCollectionNames();
            }

            //去掉全局的统计
            $remove_key = array_search('All_Statistics',$collectionList);
            if($remove_key !== false){
                unset($collectionList[$remove_key]);
            }
            $this->assign('collectionList',$collectionList);

            $_GET['start_time'] = isset($_GET['start_time']) ? $_GET['start_time'] : date('Y-m-d 00:00:00');
            $_GET['end_time'] = isset($_GET['end_time']) ? $_GET['end_time'] : date('Y-m-d 23:59:59');
            if(!isset($_GET['project_name'])){
                throw new \Exception('请先选择项目后再开始查看监控');
            }
            if(empty($_GET['project_name'])){
                throw new \Exception('项目不能为空！');
            }

            $start_timestamp = strtotime($_GET['start_time']);
            $end_timestamp = strtotime($_GET['end_time']);
            if($start_timestamp >= $end_timestamp){
                throw new \Exception('开始时间不能大于等于结束时间！');
            }

            if(PHP_VERSION >= 7){
                $manager = \Mongo\MongoDbConnection::instance('statistics')->getMongoManager();
                $where = array();
                $where['time_stamp'] = array('$gte'=>$start_timestamp,'$lte'=>$end_timestamp);
                if(!empty($_GET['class_name'])){
                    $where['class_name'] = $_GET['class_name'];
                }
                if(!empty($_GET['function_name'])){
                    $where['function_name'] = $_GET['function_name'];
                }
                $options = array('skip' => 0);
                $collection = new \MongoDB\Collection($manager, 'Statistics',$_GET['project_name']);
                $dataList = $collection->find($where, $options);
                $list = array();
                foreach($dataList as $row) {
                    array_push($list,$row);
                }
            } else {
                $mongo = \Mongo\Connection::instance('statistics')->getMongoConnection();
                $db = $mongo->selectDB('Statistics');
                $collection = $db->selectCollection($_GET['project_name']);
                $where = array();
                $where['time_stamp'] = array('$gt'=>$start_timestamp,'$lt'=>$end_timestamp);
                $where = array();
                if(!empty($_GET['class_name'])){
                    $where['class_name'] = $_GET['class_name'];
                }
                if(!empty($_GET['function_name'])){
                    $where['function_name'] = $_GET['function_name'];
                }
                $list = $collection->find($where);
            }

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

            ksort($success_series_data);
            ksort($fail_series_data);
            ksort($success_time_series_data);
            ksort($fail_time_series_data);
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }

        $this->assign('error_msg',$error_msg);
        $this->assign('page_request',$_GET);

        $success_series_data = implode(',', $success_series_data);
        $fail_series_data = implode(',', $fail_series_data);
        $success_time_series_data = implode(',', $success_time_series_data);
        $fail_time_series_data = implode(',', $fail_time_series_data);
        $this->assign('statistics_title','【'.$_GET['start_time'].' ~ '. $_GET['end_time'].'】');
        $this->assign('success_series_data',$success_series_data);
        $this->assign('fail_series_data',$fail_series_data);
        $this->assign('success_time_series_data',$success_time_series_data);
        $this->assign('fail_time_series_data',$fail_time_series_data);
        return $this->display('index');
    }

}