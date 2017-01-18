
<div class="row clearfix">
    <div class="col-md-12 column">
        <div id="container" style="min-width:700px;height:400px"></div>
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
                series: [{
                    name: '实时访问流量 2xx',
                    data: (function() { // generate an array of random data
                        var data = [],
                            time = (new Date()).getTime(),
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
                                time = (new Date()).getTime(),
                                i;
                            for (i = -19; i <= 0; i++) {
                                data.push({
                                    x: time + i * 1000,
                                    y: 0
                                });
                            }
                            return data;
                        })()
                    }]
            }); // set up the updating of the chart each second
            var series = chart.series[0];
            var series1 = chart.series[1];

            setInterval(function() {
                        //y = Math.random();
                    //series.addPoint([x, y + 1], true, true);
                    //series1.addPoint([x, y - 1], true, true);
                    var x = (new Date()).getTime()-1000
                    //自动的获取数据
                    $.ajax({
                        url:'/chats/syncdata',
                        type:'POST', //GET
                        async:true,    //true:异步  false:同步
                        data:{timestamp:x},
                        timeout:1000,    //超时时间
                        dataType:'json',
                        success:function(ajaxData){
                            statistics_data = $.parseJSON(ajaxData.statistics_data)
                            x = ajaxData.timestamp*1000;
                            series.addPoint([x, statistics_data.success_count], true, true);
                            series1.addPoint([x, statistics_data.fail_count], true, true);
                        },
                        error:function(ajaxData,textStatus){ }
                    })
                },
                1000);
        });
    });


</script>