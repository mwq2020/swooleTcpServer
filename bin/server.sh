#! /bin/sh

### BEGIN INIT INFO
# Provides:          bestdo_server
# Required-Start:    $remote_fs $network
# Required-Stop:     $remote_fs $network
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts bestdo_server
# Description:       starts the mail push server daemon
### END INIT INFO


#php路径，如不知道在哪，可以用whereis php尝试
#PHP_BIN=/usr/bin/php
PHP_BIN=/opt/php5/bin/php

#代码根目录
SERVER_PATH=/www/swooleServer

getMasterPid()
{
    PID=`/bin/ps axu|grep server|grep swoole|awk '{print $2}'`
    echo $PID
}

getManagerPid()
{
    #MID=`/bin/ps axu|grep init|grep service|grep manager|awk '{print $2}'`
    MID=`/bin/ps axu|grep server|grep swoole|grep master|awk '{print $2}'`
    echo $MID
}
case "$1" in
        start)
                PID=`getMasterPid`
                if [ -n "$PID" ]; then
                    echo "server is running"
                    exit 1
                fi
                echo  "starting server "
                $PHP_BIN $SERVER_PATH/server.php
                echo " done"
        ;;

        stop)
                PID=`getMasterPid`

                if [ -z "$PID" ]; then
                    echo "server is not running"
                    exit 1
                fi
                echo "shutting down server "

                kill $PID #杀掉worker进程

                MID=`getManagerPid`
                kill -9 $MID #杀掉主进程
                echo " done"
        ;;

        status)
                PID=`getMasterPid`
                if [ -n "$PID" ]; then
                    echo "server is running"
                else
                    echo "server is not running"
                fi
        ;;

        force-quit)
                $0 stop
        ;;

        restart)
                $0 stop
                $0 start
        ;;

        reloadworker)
                MID=`getManagerPid`
                if [ -z "$MID" ]; then
                    echo  "server is not running"
                    exit 1
                fi

                echo  "reload worker server "

                kill -USR1 $MID

                echo " done"
        ;;

        reloadtask)
                MID=`getManagerPid`
                if [ -z "$MID" ]; then
                    echo  "server is not running"
                    exit 1
                fi

                echo  "reload task server"

                kill -USR2 $MID

                echo " done"
        ;;

        reloadall)
                $0 reloadworker
                $0 reloadtask
        ;;

        *)
                echo "Usage: $0 {start|stop|force-quit|restart|reloadall|reloadworker|reloadtask|status}"
                exit 1
        ;;

esac


