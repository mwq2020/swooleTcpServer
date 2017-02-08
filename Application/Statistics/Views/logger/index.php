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
        <input type="text" name="class_name" value="<?php echo isset($page_request['class_name']) ? $page_request['class_name'] : ''; ?>">

        方法名：
        <input type="text" name="function_name" value="<?php echo isset($page_request['function_name']) ? $page_request['function_name'] : ''; ?>">

        开始时间：
        <input type="text" name="start_time" value="<?php echo isset($page_request['start_time']) ? $page_request['start_time'] : date('Y-m-d 00:00:00'); ?>">

        结束时间：
        <input type="text" name="end_time" value="<?php echo isset($page_request['end_time']) ? $page_request['end_time'] : date('Y-m-d 23:59:59'); ?>">

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
<?php endif; ?>

<div class="row clearfix">
    <div class="col-md-12 column">
        <?php echo $log_content ? $log_content : '暂无日志。。。'; ?>
    </div>
</div>

<div class="row clearfix">
    <div class="col-md-12 column .list-inline">
        <?php if(isset($pageStr) && !empty($pageStr)): ?>
        <div style="float: left;padding-top: 25px;"><span>共<?php echo $count; ?>条 &nbsp;&nbsp;</span></div>
        <div style="float: left;height:55px;">
            <?php echo isset($pageStr) ? $pageStr : ''; ?>
        </div>
        <?php endif; ?>
    </div>
</div>



<div class="row clearfix">
    <div class="col-md-12 column">
        <div id="container" style="min-width:400px;height:400px"></div>
    </div>
</div>

<script>
//    Highcharts.setOptions({
//        global: {
//            useUTC: false
//        }
//    });
//    function activeLastPointToolip(chart) {
//        var points = chart.series[0].points;
//        chart.tooltip.refresh(points[points.length -1]);
//    }
//    $('#container').highcharts({
//        chart: {
//            type: 'spline',
//            animation: Highcharts.svg, // don't animate in old IE
//            marginRight: 10,
//            events: {
//                load: function () {
//                    // set up the updating of the chart each second
//                    var series = this.series[0],
//                        chart = this;
//                    setInterval(function () {
//                        var x = (new Date()).getTime(), // current time
//                            y = Math.random();
//                        series.addPoint([x, y], true, true);
//                        activeLastPointToolip(chart)
//                    }, 1000);
//                }
//            }
//        },
//        title: {
//            text: '接口实时访问数据'
//        },
//        xAxis: {
//            type: 'datetime',
//            tickPixelInterval: 150
//        },
//        yAxis: {
//            title: {
//                text: '每秒/个'
//            },
//            plotLines: [{
//                value: 0,
//                width: 1,
//                color: '#808080'
//            }]
//        },
//        tooltip: {
//            formatter: function () {
//                return '<b>' + this.series.name + '</b><br/>' +
//                    Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) + '<br/>' +
//                    Highcharts.numberFormat(this.y, 2);
//            }
//        },
//        legend: {
//            enabled: false
//        },
//        exporting: {
//            enabled: false
//        },
//        series: [{
//            name: 'Random data',
//            data: (function () {
//                // generate an array of random data
//                var data = [],
//                    time = (new Date()).getTime(),
//                    i;
//                for (i = -19; i <= 0; i += 1) {
//                    data.push({
//                        x: time + i * 1000,
//                        y: Math.random()
//                    });
//                }
//                return data;
//            }())
//        }]
//    }, function(c) {
//        activeLastPointToolip(c)
//    });


</script>



