<?php
$filename = '121.jpo.php';
if(preg_match("#php#", $filename) == false)
{
    echo 11;
}

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

