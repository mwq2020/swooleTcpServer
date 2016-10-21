#! /bin/sh


for((i=0;i<10;))
do
        let "i=i+1"
        echo "-------------j is  -------------------"
        echo $i
        php /www/bestdo/swooleTcpServer/test/b.php
done


