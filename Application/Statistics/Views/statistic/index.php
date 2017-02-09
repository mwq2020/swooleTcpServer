<script type="text/javascript" src="/assets/js/laydate/laydate.js"></script>
<div class="row clearfix">
    <div class="col-md-12 column">
        <form action="" method="get">
            项目：
            <select name="project_name">
                <option value="0">请选择</option>
                <?php if($collectionList): ?>
                    <?php foreach($collectionList as $row): ?>
                        <option value="<?php echo $row; ?>" <?php if(isset($page_request['project_name']) && $page_request['project_name'] == $row){ echo "selected";} ?> ><?php echo $row; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            类名：
            <input type="text" name="class_name" value="<?php echo isset($page_request['class_name']) ? $page_request['class_name'] : ''; ?>" />

            方法名：
            <input type="text" name="function_name" value="<?php echo isset($page_request['function_name']) ? $page_request['function_name'] : ''; ?>" />

            开始时间：
            <input type="text" name="start_time" value="<?php echo isset($page_request['start_time']) ? $page_request['start_time'] : date('Y-m-d 00:00:00'); ?>" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" />

            结束时间：
            <input type="text" name="end_time" value="<?php echo isset($page_request['end_time']) ? $page_request['end_time'] : date('Y-m-d 23:59:59'); ?>" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" />

            <input type="submit" value="提交"/>
        </form>
    </div>
</div>

<?php if(!empty($error_msg)): ?>
<div class="row clearfix margin_top5">
    <div class="col-md-12 column">
    <div class="alert alert-dismissable alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <strong><?php echo $error_msg;?></strong>
    </div>
    </div>
</div>
<?php else: ?>

<div class="row clearfix">
    <div class="col-md-12 column">
        <div class="row clearfix">
            <div class="col-md-12 column height-400" id="req-container" >
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12 column height-400" id="time-container" >
            </div>
        </div>
        <script>
            Highcharts.setOptions({
                global: {
                    useUTC: false
                }
            });
            $('#req-container').highcharts({
                chart: {
                    type: 'spline'
                },
                title: {
                    text: '<?php echo $statistics_title;?>  请求量曲线'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    type: 'datetime',
                    dateTimeLabelFormats: {
                        hour: '%H:%M'
                    }
                },
                yAxis: {
                    title: {
                        text: '请求量(次/5分钟)'
                    },
                    min: 0
                },
                tooltip: {
                    formatter: function() {
                        return '<p style="color:'+this.series.color+';font-weight:bold;">'
                            + this.series.name +
                            '</p><br /><p style="color:'+this.series.color+';font-weight:bold;">时间：' + Highcharts.dateFormat('%m月%d日 %H:%M', this.x) +
                            '</p><br /><p style="color:'+this.series.color+';font-weight:bold;">数量：'+ this.y + '</p>';
                    }
                },
                credits: {
                    enabled: false,
                },
                series: [		{
                    name: '成功曲线',
                    data: [
                        <?php echo $success_series_data;?>
                    ],
                    lineWidth: 2,
                    marker:{
                        radius: 1
                    },

                    pointInterval: 300*1000
                },
                    {
                        name: '失败曲线',
                        data: [
                            <?php echo $fail_series_data;?>
                        ],
                        lineWidth: 2,
                        marker:{
                            radius: 1
                        },
                        pointInterval: 300*1000,
                        color : '#9C0D0D'
                    }]
            });
            $('#time-container').highcharts({
                chart: {
                    type: 'spline'
                },
                title: {
                    text: '<?php echo $statistics_title;?>  请求耗时曲线'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    type: 'datetime',
                    dateTimeLabelFormats: {
                        hour: '%H:%M'
                    }
                },
                yAxis: {
                    title: {
                        text: '平均耗时(单位：秒)'
                    },
                    min: 0
                },
                tooltip: {
                    formatter: function() {
                        return '<p style="color:'+this.series.color+';font-weight:bold;">'
                            + this.series.name +
                            '</p><br /><p style="color:'+this.series.color+';font-weight:bold;">时间：' + Highcharts.dateFormat('%m月%d日 %H:%M', this.x) +
                            '</p><br /><p style="color:'+this.series.color+';font-weight:bold;">平均耗时：'+ this.y + '</p>';
                    }
                },
                credits: {
                    enabled: false,
                },
                series: [		{
                    name: '成功曲线',
                    data: [
                        <?php echo $success_time_series_data;?>
                    ],
                    lineWidth: 2,
                    marker:{
                        radius: 1
                    },
                    pointInterval: 300*1000
                },
                    {
                        name: '失败曲线',
                        data: [
                            <?php echo $fail_time_series_data;?>
                        ],
                        lineWidth: 2,
                        marker:{
                            radius: 1
                        },
                        pointInterval: 300*1000,
                        color : '#9C0D0D'
                    }			]
            });
        </script>

    </div>
</div>
<?php endif; ?>