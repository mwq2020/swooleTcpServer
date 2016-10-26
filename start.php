<?php
$process = new swoole_process('callback_function', true);
$pid = $process->start();

echo "starting \r\n";
function callback_function(swoole_process $worker)
{
    foreach(glob(__DIR__.'/Application/*/server*.php') as $start_file)
    {
        $worker->exec('/usr/local/bin/php', array($start_file));
        echo $start_file."start success \r\n";
    }
}
swoole_process::wait();
echo "done";