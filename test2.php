<?php
echo iconv("gb2312//IGNORE","utf-8",'我去你妈');;
//$matches = [];
//$bool = preg_match("#(上|充|加|上分|充值|充钱|加钱|加分)(\d+)#", '上1212', $matches);
//echo $bool.PHP_EOL;
//var_dump($matches);
//echo (substr(time(),6));
//$exp = "#\d{1,}#";
//echo preg_match($exp,"下单0.1");

////$res = false;
////var_dump(empty($res));
///**
// * @param string $code
// * @param string $site
// * @return string
// */
//function _siteCode($code,$site)
//{
//    $site = str_replace('万','0',$site);
//    $site = str_replace('千','1',$site);
//    $site = str_replace('百','2',$site);
//    $site = str_replace('十','3',$site);
//    $site = str_replace('个','4',$site);
//    $siteLen = strlen($site);
//    $resCode = '';
//    for ($i = 0 ;$i < $siteLen;$i++){
//        $resCode .= substr($code,substr($site,$i,1),1);
//    }
//    return $resCode;
//}
// //echo substr('123456',0,1);
//echo _siteCode('91904','');
//$res = null;
//$res = serialize($res);
//var_dump(unserialize($res));
//echo "$res";
//echo preg_match("#上(\d+)#","上1上2",$res);
//var_dump($res);

/*require 'vendor/autoload.php';

use Carbon\Carbon;

$carbon = \Carbon\Carbon::parse(art_d(),'Asia/Shanghai');
$CarbonIssue = Carbon::parse(art_d(), 'Asia/Shanghai');
$diff = $CarbonIssue->diffInRealSeconds('2020-12-15 19:55:00');
echo $CarbonIssue->toDateTimeString();
echo $diff;*/

//echo $carbon->diffInMinutes($carbon1);

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

