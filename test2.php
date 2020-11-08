<?php
$test ='%7B%22dqqh%22%3A%2220201108173%22%2C%22dqkj%22%3A%222020%E5%B9%B411%E6%9C%888%E6%97%A514%E6%97%B625%E5%88%86%22%2C%22sqsj%22%3A%222020%2F11%2F08%2014%3A20%3A29%22%2C%22sqqh%22%3A%2220201108172%22%2C%22sqhm%22%3A%2221468%22%7D';
$test = gzuncompress($test);
echo $test;
var_dump(json_decode($test,true));



