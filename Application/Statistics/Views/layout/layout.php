<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <title>TestCenter-调试工具</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="/assets/js/html5shiv.js"></script>
    <![endif]-->
    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/assets/img/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/assets/img/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/assets/img/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="/assets/img/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="/assets/img/favicon.png">
    <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="/assets/js/bootstrap.min.js"></script>
    <!--<script type="text/javascript" src="/assets/js/scripts.js"></script>-->
    <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="/assets/js/highcharts.js"></script>
</head>
<body>
<div class="container">
    <div class="row clearfix">
        <div class="col-md-12 column">
            <ul class="nav nav-tabs">
                <li>
                    <a href="http://<?php echo isset($_SERVER['HTTP_HOST']) ? strchr($_SERVER['HTTP_HOST'],':',true) : ''; ?>:55757">概述</a>
                </li>
                <li>
                    <a href="http://<?php echo isset($_SERVER['HTTP_HOST']) ? strchr($_SERVER['HTTP_HOST'],':',true) : ''; ?>:55757/?fn=statistic">监控</a>
                </li>
                <li>
                    <a href="http://<?php echo isset($_SERVER['HTTP_HOST']) ? strchr($_SERVER['HTTP_HOST'],':',true) : ''; ?>:55757/?fn=logger">日志</a>
                </li>
                <li class="disabled">
                    <a href="">告警</a>
                </li>
                <li class="active">
                    <a id="testPage" href="<?php echo isset($_SERVER['HTTP_HOST']) ? strchr($_SERVER['HTTP_HOST'],':',true) : ''; ?>:2020">调试界面</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- 正式页面内容start -->
    <?php echo $content; ?>
    <!-- 正式页面内容end -->

</div>


<div class="footer">Powered by <a href="http://www.swoole.com" target="_blank"><strong>swoole!</strong></a></div>
</body>
</html>
