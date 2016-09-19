<?php

namespace Config;

class PHPClient
{

    public $rpc_secret_key = '769af463a39f077a0340a189e9c1ec28';
    public $monitor_log_dir = '/tmp/monitor-log';

    public $ClubService = array(
        'uri' => "127.0.0.1:7000",
        'user' => 'club_manage',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
}