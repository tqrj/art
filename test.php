<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
$key = "example_key";
$payload = array(
    "iss" => "http://example.org",
    "aud" => "http://example.com",
    "iat" => 1356999524,
    "nbf" => 1357000000,
    'exp' => time()+60
);

/**
 * IMPORTANT:
 * You must specify supported algorithms for your application. See
 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
 * for a list of spec-compliant algorithms.
 */
JWT::$leeway = 60; // $leeway in seconds
$jwt = JWT::encode($payload, $key);
var_dump($jwt);
//$jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLm9yZyIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUuY29tIiwiaWF0IjoxMzU2OTk5NTI0LCJuYmYiOjEzNTcwMDAwMDAsImV4cCI6MTYxNjA1NzQwNH0.47YO-uKWVrMbq9yQ_zEjmAyPA7OFHRRHucenajwuJOc';
try {
    $decoded = JWT::decode($jwt, $key, array('HS256'));

    print_r($decoded);
}catch (Firebase\JWT\ExpiredException | Exception $e){
    echo $e->getMessage();
}


/*
 NOTE: This will now be an object instead of an associative array. To get
 an associative array, you will need to cast it as such:
*/

//$decoded_array = (array) $decoded;

/**
 * You can add a leeway to account for when there is a clock skew times between
 * the signing and verifying servers. It is recommended that this leeway should
 * not be bigger than a few minutes.
 *
 * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
 */
//JWT::$leeway = 60; // $leeway in seconds
//$decoded = JWT::decode($jwt, $key, array('HS256'));

//use app\traits\Lottery;
//
//$n = 0;
//while ($n < 100000) {
//    $n++;
//    \Co\run(function () {
//        $str[] = '单10';
//        $str[] = '12345-12345-12345-12369-2580/0.1';
//        $str[] = '万23456千23456除各1';
//        Lottery::parseExp($str[mt_rand(0,2)]);
//        Lottery::getCode(Lottery::LOTTERY_TYPE_now);
//        Lottery::getCode(Lottery::LOTTERY_TYPE_OLD);
//    });
//
//}
//echo 'ojbk处理完毕' . PHP_EOL;