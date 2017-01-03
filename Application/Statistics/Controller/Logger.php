<?php
namespace Controller;

class Logger extends \Framework\CController
{

    public function actionIndex()
    {
        $error_msg = '';
        $log_content = '';
        try {
            if(!isset($_GET['project_name'])){
                throw new \Exception('请先选择项目后再开始查看日志');
            }
            if(empty($_GET['project_name'])){
                throw new \Exception('项目不能为空！');
            }
            if(empty($_GET['start_time'])){
                throw new \Exception('开始时间不能为空！');
            }
            if(empty($_GET['end_time'])){
                throw new \Exception('结束时间不能为空！');
            }

            $start_timestamp = strtotime($_GET['start_time']);
            $end_timestamp = strtotime($_GET['end_time']);

            if($start_timestamp >= $end_timestamp){
                throw new \Exception('开始时间不能大于等于结束时间！');
            }

            $where = array();
            $where['add_time'] = array('$gt'=>$start_timestamp,'$lt'=>$end_timestamp);

            $p = isset($_GET['p']) && !empty($_GET['p']) ? intval($_GET['p']) : 1;
            $page_size = isset($_GET['page_size']) && !empty($_GET['page_size']) ? intval($_GET['page_size']) : 10;
            $config =\Config\Mongo::getConfig();
            $mongo = $this->mongo = new \MongoClient('mongodb://'.$config['host'].':'.$config['port']);
            $db = $mongo->selectDB('StatisticsLog');
            $collection = $db->selectCollection($_GET['project_name']);

            $startNum = ($p-1)*$page_size;
            $list = $collection->find($where)->skip($startNum)->limit($page_size);
            $log_content = '';
            foreach($list as $row){
                $log_content .= '请求时间：'.date('Y-m-d H:i:s',$row['add_time']).
                                ' 调用接口【'.$row['class_name'].'->'.$row['function_name'].'】'.
                                ' 状态码【'.$row['code'].'】'.
                                ' 日志内容【'.$row['msg']."】<br>";
            }

        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }

        $this->assign('log_content',$log_content);
        $this->assign('error_msg',$error_msg);
        $this->assign('page_request',$_GET);
        return $this->display('index');
    }

}