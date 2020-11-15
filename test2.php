<?php
//echo  PACK('H*','696D616765732F646564652E6A7067');
$data = ['test'=>'qwewqc','qqq'=>'121','rrq'=>'qweqqq','qcc'=>'ccc'];
//unset($data[1]);
art_unset('qqq',$data);
var_dump(json_encode($data));




//function test($data)
//{
//    array_walk($data,function ($item,$key){
//        echo $item.$key.PHP_EOL;
//
//    });
//}
//test($data);

