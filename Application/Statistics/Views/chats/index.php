
<div class="row clearfix">
    <div class="col-md-12 column">
        <div id="container" style="min-width:700px;height:400px"></div>
    </div>
</div>

<div class="row clearfix">
    <div class="col-md-12 column"><h3>实时日志内容列表（动态更新）</h3></div>
    <div class="col-md-12 column" id="log_list">

    </div>
</div>

<script>
    $(function() {
        $(document).ready(function() {
            Highcharts.setOptions({
                global: {
                    useUTC: false
                }
            });
            var chart;
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'container',
                    type: 'spline',
                    animation: Highcharts.svg,
                    marginRight: 10,
                    events: {
                        load: function() {}
                    }
                },
                title: {
                    text: '接口实时统计'
                },
                xAxis: {
                    //min:1, // 定义最小值
                    type: 'datetime',
                    tickPixelInterval: 150
                },
                yAxis: [{
                    min:0, // 定义最小值
                    title: {
                        text: '秒/个'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                    {
                        title: {
                            text: ''
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                    }],
                tooltip: {
                    formatter: function() {
                        return '<b>' + this.series.name + '</b><br/>' + Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) + '<br/>' + Highcharts.numberFormat(this.y, 2);
                    }
                },
                legend: {
                    enabled: false
                },
                exporting: {
                    enabled: false
                },
                series: [
                    {
                        name: '实时访问流量 2xx',
                        data: (function() { // generate an array of random data
                            var data = [],
                                time = (new Date()).getTime()-1000,
                                i;
                            for (i = -19; i <= 0; i++) {
                                data.push({
                                    x: time + i * 1000,
                                    y: 0
                                });
                            }
                            return data;
                        })()
                    },
                    {
                        name: '实时访问流量 5xx',
                        data: (function() { // generate an array of random data
                            var data = [],
                                time = (new Date()).getTime()-1000,
                                i;
                            for (i = -19; i <= 0; i++) {
                                data.push({
                                    x: time + i * 1000,
                                    y: 0
                                });
                            }
                            return data;
                        })()
                    }
                ]
            }); // set up the updating of the chart each second
            var series = chart.series[0];
            var series1 = chart.series[1];

            setInterval(function() {
                    var x = (new Date()).getTime()-1000
                    //自动的获取数据
                    $.ajax({
                        url:'/chats/syncdata?m='+Math.random(),
                        type:'GET',
                        async:true,    //true:异步  false:同步
                        timeout:1000,    //超时时间
                        dataType:'json',
                        success:function(ajaxData){
                            statistics_data = $.parseJSON(ajaxData.statistics_data)
                            x = ajaxData.timestamp*1000;
                            series.addPoint([x, statistics_data.success_count], true, true);
                            series1.addPoint([x, statistics_data.fail_count], true, true);

                            //处理日志显示
                            //console.log(ajaxData.log_list);
                            if(ajaxData.log_list.length > 0){
                                //console.log(ajaxData.log_list);
                                $(ajaxData.log_list).each(function(k,v){
                                    $('#log_list').prepend('<div class="log_li"><p>请求类名:'+v.class_name+'&nbsp;&nbsp;请求方法名:'+v.function_name+'&nbsp;&nbsp;请求耗时:'+v.cost_time.toFixed(6)+'秒</p><p>请求参数:'+v.args+'</p><p>日志内容：'+(v.msg == '' ? '-' :v.msg)+'</p></div>')
                                });
                            }
                            //console.log($('.log_li').length);
                            if($('.log_li').length > 5){
                                //console.log('test'+$('.log_li').length);
                                $('.log_li').eq(4).nextAll().remove();
                            }
                        },
                        error:function(ajaxData,textStatus){ }
                    })
                },
                1000);
        });
    });
</script>