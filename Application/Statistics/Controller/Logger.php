<?php
namespace Controller;

class Logger extends \Framework\CController
{

    public function actionIndex()
    {
        $error_msg = '';
        $log_content = '';
        try {
            $config =\Config\Mongo::getConfig();
            $mongo = $this->mongo = new \MongoClient('mongodb://'.$config['host'].':'.$config['port']);
            $db = $mongo->selectDB('StatisticsLog');
            $collectionList = $db->getCollectionNames();
            $this->assign('collectionList',$collectionList);

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
            if(!empty($_GET['class_name'])){
                $where['class_name'] = $_GET['class_name'];
            }
            if(!empty($_GET['function_name'])){
                $where['function_name'] = $_GET['function_name'];
            }

            $p = isset($_GET['p']) && !empty($_GET['p']) ? intval($_GET['p']) : 1;
            $page_size = isset($_GET['page_size']) && !empty($_GET['page_size']) ? intval($_GET['page_size']) : 10;
            $collection = $db->selectCollection($_GET['project_name']);

            $startNum = ($p-1)*$page_size;
            $list = $collection->find($where)->skip($startNum)->limit($page_size)->sort(array('add_time'=>-1));
            $count = $collection->find($where)->count();
            $this->assign('count',$count);
            $log_content = '';
            //echo "<pre>";
            foreach($list as $id => $row){
                //print_r($row);
                $log_content .= '请求时间：'.date('Y-m-d H:i:s',$row['add_time']).
                                ' 调用接口【'.$row['class_name'].'->'.$row['function_name'].'】'.
                                ' 状态码【'.$row['code'].'】'.
                                ' 日志内容【'.substr($row['msg'],0,70).'】'.
                                ' <a href="/logger/info?project_name='.$row['project_name'].'&id='.$id.'">查看</a>'.
                                '<br>';
            }

            $page = new \Model\Pagination($count,20,$_GET);
            $page->url = $this->domain_url.$this->request_uri;
            $this->assign('pageStr',$page->show());

        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }
        $this->assign('log_content',$log_content);
        $this->assign('error_msg',$error_msg);
        $this->assign('page_request',$_GET);
        return $this->display('index');
    }

    /**
     * 显示日志详情.
     * @throws \Exception
     */
    public function actionInfo()
    {
        $config =\Config\Mongo::getConfig();
        $mongo = $this->mongo = new \MongoClient('mongodb://'.$config['host'].':'.$config['port']);
        $db = $mongo->selectDB('StatisticsLog');
        $collection = $db->selectCollection($_GET['project_name']);
        $info = $collection->findOne(array("_id" =>(new \MongoId($_GET['id'])) ));
        $this->assign('info',$info);
        return $this->display('info');
    }


    /**
     * 获取当前时间戳的访问数据
     */
    public function actionSyncdata()
    {
        echo "<pre>";

        $params = array();
        $params['action'] = 'sync_statistics_data';
        $params['timestamp'] = time();
        $result = (new \Model\Request)->getStatisticsData($params);
        var_dump($result);


        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);

        $redis_key = 'second_'.time();
        $result = $redis->lRange($redis_key,0,1000);
        foreach($result as $row){
            echo $row."<br>";
            print_r(json_decode($row,true));
        }
    }

}