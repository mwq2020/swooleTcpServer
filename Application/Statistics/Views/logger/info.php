<div class="row clearfix">
    <div class="col-md-12 column">
        <table class="table table-hover table-condensed table-bordered">
            <thead>
            <tr>
                <th colspan="2">日志详情</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td width="10%">请求接口</td>
                <td><?php echo $info['class_name'].'    -> '.$info['function_name'] .'()'; ?></td>
            </tr>
            <tr>
                <td>请求参数</td>
                <td><?php echo json_encode($info['args']); ?></td>
            </tr>
            <tr>
                <td>接口耗时</td>
                <td><?php echo number_format($info['cost_time'],6); ?></td>
            </tr>
            <tr>
                <td>服务ip</td>
                <td><?php echo $info['remote_ip']; ?></td>
            </tr>
            <tr>
                <td>服务状态</td>
                <td><?php echo ($info['is_success']? '成功':'失败').'【'.$info['code'].'】'; ?></td>
            </tr>
            <tr>
                <td>日志内容</td>
                <td><pre><?php echo $info['msg']; ?></pre></td>
            </tr>
            <tr>
                <td>创建时间</td>
                <td><?php echo date('Y-m-d H:i:s',$info['add_time']); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>