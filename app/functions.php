<?php


use art\context\Context;
use art\WsApp;

date_default_timezone_set("Asia/Shanghai");
/**
 * 针对无键名数组unset 避免jsonEncode变成对象
 * @param $value
 * @param $array
 * @return array|bool
 */
function art_unset($value, &$array)
{
    $sort = array_flip($array);
    if (isset($sort[$value])) {
        return array_splice($array, $sort[$value], 1);
    }
    return false;
}

/**
 * @return string
 */
function art_rand_mobile()
{
    $arr = array(130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 144, 147, 150, 151, 152, 153, 155, 156, 157, 158, 159, 176, 170, 173, 177, 178, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189);
    return $arr[array_rand($arr)] . mt_rand(10000000, 99999999);
}

/**
 * @return string
 */
function art_rand_province()
{
    $arr = array('广东', '上海', '江苏', '浙江', '福建', '四川', '湖北', '湖南', '陕西', '云南', '安徽', '广西', '新疆', '重庆', '江西', '甘肃', '贵州', '海南', '宁夏', '青海', '西藏', '北京', '天津', '山东', '河南', '辽宁', '河北', '山西', '内蒙', '吉林', '黑龙江');
    return $arr[array_rand($arr)];
}

/**
 * @return false|string
 */
function art_d()
{
    return date("Y-m-d H:i:s");
}

//随机字符串，默认长度10
/**
 * @param int $num
 * @return false|string
 */
function art_set_salt($num = 10)
{
    $str = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
    $salt = substr(str_shuffle($str), 10, $num);
    return $salt;
}

//art加密方式
/**
 * @param $pwd
 * @param $salt
 * @return string
 */
function art_set_password($pwd, $salt)
{
    return md5(md5($pwd . $salt) . $salt);
}

/**
 * 生成验证码
 *
 * @param Integer $width 验证码宽度(px)
 * @param Integer $height 验证码高度(px)
 * @param Integer $num 验证码中字符个数
 * @param Integer $type 验证码类型：1 纯数字，2 纯字母，3 数字和字母组合
 * @param String $font_name 验证码字符使用的字体，字体需和该程序文件放置在同一个目录下
 *
 * @return array 返回生成验证码对应的字符串
 * @author Otstar Lin
 *
 */
function art_verify($width = 100, $height = 40, $num = 5, $type = 3, $font_name = 'Roboto-Medium.ttf')
{
    // 创建画布，加载字体
    $image = imagecreatetruecolor($width, $height);
    putenv('GDFONTPATH=' . realpath('.'));
    $font = $font_name;
    // 创建颜色
    global $light_c, $deep_c;
    for ($i = 0; $i < $num; $i++) {
        $deep_c[] = imagecolorallocate($image, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
    }
    $line_c = imagecolorallocate($image, mt_rand(130, 180), mt_rand(130, 180), mt_rand(130, 180));
    $bg = imagecolorallocate($image, 246, 246, 246);
    imagefill($image, 0, 0, $bg);
    // 创建字符
    if ($type == 1) {
        $char_arr = range('0', '9');
    } else if ($type == 2) {
        $char_arr = range('a', 'z');
    } else if ($type == 3) {
        $char_arr = range('0', '9');
        $char_arr = array_merge($char_arr, range('a', 'z'));
    }
    shuffle($char_arr);
    $char_arr = array_slice($char_arr, 0, $num);
    // 绘制字符
    for ($i = 0; $i < $num; $i++) {
        imagettftext($image, 18, mt_rand(-30, 30), ($width - 20) / $num * $i + 10, ($height / 2) + 18 / 2, $deep_c[$i], $font, $char_arr[$i]);
    }
    // 绘制干扰点
    for ($i = 0; $i < $width; $i = $i) {
        $i += mt_rand(1, 5);
        $y_num = mt_rand(1, 5);
        for ($j = 0; $j < $y_num; $j++) {
            imagesetpixel($image, $i, mt_rand(0, $height), $deep_c[mt_rand(0, $num - 1)]);
        }
    }
    // 绘制干扰线
    for ($i = 0; $i < $width; $i++) {
        for ($j = 0; $j < 3; $j++) {
            imagesetpixel($image, $i, sin($i / 50 * M_PI) * 3 + $height / 2 + $j, $line_c);
        }
    }
    ob_start();
    imagejpeg($image);
    $image_data = ob_get_contents();
    ob_end_clean();
    $image_data_base64 = base64_encode($image_data);
    unset($image_data);
    return ['base64' => 'data:image/jpeg;base64,' . $image_data_base64, 'deCode' => implode('', $char_arr)];
}

/**
 * 验证数据
 * @access protected
 * @param array $data 数据
 * @param array $validate 验证规则数组
 * @param array $message 提示信息
 * @param bool $batch 是否批量验证
 * @return array|string|true
 * @throws ValidateException
 */
function art_validate(array $data, $validate, array $message = [], bool $batch = false)
{
    if (is_array($validate)) {
        $v = new \art\validate\Validate();
        $v->rule($validate);
    }
    $v->message($message);
    // 是否批量验证
    if ($batch) {
        $v->batch(true);
    }
    try {
        $v->failException(true)->check($data);
    } catch (\art\exception\ValidateException $e) {
        throw new \art\exception\HttpException(202, $e->getMessage());
    }
    return true;
    //return $v->failException(true)->check($data);
}

/**
 * @param int $code
 * @param string $msg
 * @param array $data
 * @param string $location
 * @param int $selfWsId
 * @param int $recvId
 * @param string $wsGroup
 */
function _art_assign(int $code = 200, $msg = "success", $data = [], string $location = '', int $selfWsId = 0, int $recvId = 0, string $wsGroup = '')
{
    $res['code'] = $code;
    $res['msg'] = $msg;
    $res['data'] = $data;
    /*    if (is_object($data)) {
            $data = $data->toArray();
        }*/
    $response = Context::get('response');
    if (!property_exists($response, 'artWsId')) {
        $response->status($code);
        $response->header('Content-type', 'text/json');
        if (!empty($location))
            $response->status(302);
        $response->header('Location', $location);
        $response->end(json_encode($res));;
        Context::delete();
    }
}

/**
 * @param int $code
 * @param string $msg
 * @param array $data
 * @param int $selfWsId
 * @param int $recvId
 * @param string $wsGroup
 */
function _art_assign_ws(int $code = 200, $msg = "success", $data = [], int $selfWsId = 0, int $recvId = 0, string $wsGroup = '')
{
    $res['code'] = $code;
    $res['msg'] = $msg;
    $res['data'] = $data;
    \art\ws\ArtWs::pushMsg(json_encode($res), $selfWsId, $recvId, $wsGroup);
}

/**
 * @param int $code
 * @param string $msg
 * @param array $data
 * @param string $location
 */
function art_assign(int $code = 200, $msg = "success", $data = [], string $location = '')
{
    throw new \art\exception\HttpException($code, $msg, $data, $location);
}

/**
 * @param int $code
 * @param string $msg
 * @param array $data
 * @param string $wsGroup
 * @param int $recvId
 * @param int $selfWsId
 */
function art_assign_ws(int $code = 200, $msg = "success", $data = [], string $wsGroup = '', int $recvId = 0, int $selfWsId = 0)
{
    $message['code'] = $code;
    $message['msg'] = $msg;
    $message['data'] = $data;
    $message = json_encode($message);
    \art\ws\ArtWs::pushMsg($message, $selfWsId, $recvId, $wsGroup);
    //throw new \art\exception\HttpException($code,$msg,$data,'',$selfWsId,$recvId,$wsGroup);
}
