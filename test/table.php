<?php
$table = new swoole_table(1024);
$table->column('fd', swoole_table::TYPE_INT);
$table->column('from_id', swoole_table::TYPE_INT);
$table->column('data', swoole_table::TYPE_STRING, 64);
$table->create();

//$serv = new swoole_server('127.0.0.1', 9501);
////将table保存在serv对象上
//$serv->table = $table;
//
//$serv->on('receive', function ($serv, $fd, $from_id, $data) {
//    $ret = $serv->table->set($fd, array('from_id' => $data, 'fd' => $fd, 'data' => $data));
//});
//
//$serv->start();
