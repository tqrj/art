<?php
//echo  PACK('H*','696D616765732F646564652E6A7067');
$data = ['item','test'=>'qqq','rrq'];

function test($data)
{
    array_walk($data,function ($item,$key){
        echo $item.$key.PHP_EOL;

    });
}
test($data);

