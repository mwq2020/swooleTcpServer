<div class="row clearfix">

    <div class="col-md-12 column">
        <div class="row clearfix">
            <div class="col-md-12 column text-center">
                <?php $showDay = isset($_GET['date']) && !empty($_GET['date']) ? date('Y-m-d',strtotime($_GET['date'])) : date('Y-m-d'); ?>
                <?php for($i=13;$i>=0;$i--): ?>
                    <?php $tempDay = date('Y-m-d',time()-$i*24*3600); ?>
                    <a href="/?date=<?php echo $tempDay;?>" class="btn" type="button"><?php echo $tempDay == $showDay ? '<b>'.$tempDay.'</b>' : $tempDay;?></a>
                    <?php if($i==7){ echo "<br>";};?>
                <?php endfor; ?>
            </div>
        </div>
    </div>

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
    <table class="table table-hover table-condensed table-bordered">
        <thead>
        <tr>
            <th>时间</th><th>调用总数</th><th>平均耗时</th><th>成功调用总数</th><th>成功平均耗时</th><th>失败调用总数</th><th>失败平均耗时</th><th>成功率</th>
        </tr>
        </thead>
        <tbody>
        <?php echo $table_data;?>
        </tbody>
    </table>
</div>
