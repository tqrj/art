<?php

$res = null;
$res = serialize($res);
var_dump(unserialize($res));
//echo "$res";
//echo preg_match("#上(\d+)#","上1上2",$res);
//var_dump($res);

//require 'vendor/autoload.php';
//
//$carbon = \Carbon\Carbon::now('Asia/Shanghai');
//$carStart = \Carbon\Carbon::now()->addDays(10);
//$carEnd = \Carbon\Carbon::now()->subDays(10);
//echo $carbon->isSameMonth($carStart);
//echo $carbon->tz;

//$test ='ddd';
//$data[0][0] = 111;
//$data[0][1] = 112;
//array_walk($data,function ($item,$key) use (&$test){
//    print_r($item);
//    $test =1121;
//},$test);
//echo $test;
//echo date('j',strtotime('2020-1-18 19:46:27'));

//if(preg_match("#php#", $filename) == false)
//{
//    echo 11;
//}

//echo  PACK('H*','696D616765732F646564652E6A7067');
////unset($data[0]);
//art_unset('qq',$data);
//var_dump(json_encode($data));
///**
// * 针对无键名数组unset 避免jsonEncode变成对象
// * @param $value
// * @param $array
// * @return array|bool
// */
//function art_unset($value,&$array)
//{
//    $sort = array_flip($array);
//    if (isset($sort[$value])){
//        return array_splice($array,$sort[$value],1);
//    }
//    return false;
//}



//function test($data)
//{
//    array_walk($data,function ($item,$key){
//        echo $item.$key.PHP_EOL;
//
//    });
//}
//test($data);

