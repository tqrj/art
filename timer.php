<?php


Swoole\Timer::tick(1000, function(){
    echo "timeout\n";
});
