<?php
namespace Functions;

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 15:50
 * @param $optionVal
 * @return array
 * Notes：
 */
function splitOption($optionVal)
{
    $res = [];
    handOption($optionVal, 0, $res);
    $arr = [];
    foreach ($res as $v) {
        $arr[$v['op']] = $v['val'];
    }
    return $arr;
}

function handOption($val, $key, &$arr)
{
    $letter = ['A.', 'B.', 'C.', 'D.', 'E.', 'F.', 'G.', 'H.', 'I.', 'J.'];
    if (strpos($val, $letter[$key]) || strpos($val, $letter[$key]) !== false) {
        $op = substr($letter[$key], 0, strpos($letter[$key], '.'));
        $opVal = removeTag($letter[$key], $letter[$key + 1], $val);
        array_push($arr, ['op' => $op, 'val' => $opVal]);
        handOption($val, $key + 1, $arr);
    } else {
        if (strpos($val, $letter[$key])) {
            $op = substr($letter[$key], 0, strpos($letter[$key], '.'));
            $opVal = trim(substr($val, strpos($val, $letter[$key]) + 2));
            array_push($arr, ['op' => $op, 'val' => $opVal]);
        } else {
            $op = substr($letter[$key - 1], 0, strpos($letter[$key - 1], '.'));
            $opVal = trim(substr($val, strpos($val, $letter[$key - 1]) + 2));
            array_push($arr, ['op' => $op, 'val' => $opVal]);
        }
    }
}

function removeTag($begin, $end, $str)
{
    $b = mb_strpos($str, $begin) + mb_strlen($begin);
    $e = mb_strpos($str, $end) - $b;

    $result = mb_substr($str, $b, $e);
    return trim($result);
}


/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 15:51
 * @param int $day
 * @return float|int
 * Notes：获取某天的当前时刻的时间戳
 */
function _time($day = 0)
{
    return time() + 86400 * $day;
}

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 15:52
 * @param int $day
 * @return false|string
 * Notes：获取某天的当前时刻的 日期
 */
function _datetime($day = 0)
{
    return date("Y-m-d H:i:s", time() + 86400 * $day);
}

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 15:53
 * @param int $day
 * @return false|string
 * Notes：获取某天的0点的 日期
 */
function _date_start_time($day = 0)
{
   return date("Y-m-d 00:00:00", time() + 86400 * $day);
}

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 15:54
 * @param int $day
 * @return false|string
 * Notes：获取某天的0点的 日期
 */
function _date_end_time($day = 0)
{
    return date("Y-m-d 23:59:59", time() + 86400 * $day);
}

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 16:02
 * @param $num
 * @return string
 * Notes：获取随机字符串
 */
function _getStr($num)
{
    $list = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'a', 'B', 'b', 'C', 'c', 'D', 'd', 'E', 'e', 'F', 'f', 'G', 'g', 'H', 'h', 'I', 'i', 'J', 'j', 'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'P', 'p', 'Q', 'q', 'X', 'x', 'Y', 'y', 'Z', 'z');
    $str = '';
    for ($i = 0; $i < $num; $i++) {
        $j = rand(0, 47);
        $str .= $list[$j][0];
    }
    return $str;
}

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 16:02
 * @param $url
 * @param $postData
 * @return bool|string
 * Notes：POST取远程数据
 */
function _httpPost($url, $postData)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, count($postData));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 16:03
 * @param $content
 * @return string
 * Notes：oss-图片处理
 */
function oss_upload($content)
{
    $bucket = Env::get('oss.bucket');
    $accessKeyId = Env::get('oss.accessKeyId');
    $accessKeySecret = Env::get('oss.accessKeySecret');
    $endpoint = Env::get('oss.endpoint');

    $oss = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
    $imgname = md5(uniqid(microtime(true), true)) . ".jpg"; //想要保存文件的名称
    $oss->putObject($bucket, $imgname, $content);
    if (empty($imgname)) {
        return '';
    }

    return 'https://' . $bucket . '.' . $endpoint . '/' . $imgname;
}

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 16:03
 * @param $str
 * @param $len
 * @param string $suffix
 * @return string|string[]
 * Notes：文章内容显示条件内的字数
 */
function cut_str($str, $len, $suffix = "...")
{
    $str = htmlspecialchars_decode($str);//把一些预定义的 HTML 实体转换为字符
    $str = str_replace("&nbsp;", "", $str);//将空格替换成空
    $str = strip_tags($str);//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
    //$con = mb_substr($contents, 0, 100,"utf-8");//返回字符串中的前100字符串长度的字符
    if (function_exists('mb_substr')) {
        if (strlen($str) > $len) {
            $str = mb_substr($str, 0, $len, "utf-8") . $suffix;
        }
        return $str;
    } else {
        if (strlen($str) > $len) {
            $str = substr($str, 0, $len, "utf-8") . $suffix;
        }
        return $str;
    }
}


    /**
     * 传入字符串，判断是否为空
     * @author love1990lv
     * @param $_str
     * @return bool
     */
    function _isBlank($_str)
    {
        if (!isset($_str)) return true;
        if (is_null($_str)) return true;
        if (trim($_str) == "") return true;
        return false;
    }



/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/7/31 10:07
 * @param $start_date
 * @param $end_date
 * @return false|string
 * Notes：计算2个日期差 包含 闭区间[]
 */
function diffDate($start_date, $end_date)
{
    return date_diff(date_create($start_date), date_create($end_date))->days + 1;
}

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/7/31 13:37
 * @param $start_date
 * @param $end_date
 * @param int $is_contain 是否包含end_date
 * @return array
 * Notes：获取2个日期间的 日期数组  闭区间
 * @throws Exception
 */
function diffDateArr($start_date, $end_date,$is_contain = 1)
{
    $temp = [];
    $period = new DatePeriod(
        new DateTime($start_date),
        new DateInterval('P1D'),
        new DateTime($end_date));
    foreach ($period as $key => $value) {
        $temp[] = $value->format('Y-m-d');
    }
    if($is_contain){
        $temp[] = $end_date;
    }
    return $temp;

}

/*
 * 根据字段名重新排序二维数组
 */
function array_sort($array, $on, $order = SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }
        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }
        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

/*
 * 根据字段名重新排序二维数组   重建键值对
 */
function newarray_sort($array, $on, $order = SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();
    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[] = $array[$k];
        }
    }

    return $new_array;
}

/**
 * Created by PhpStorm.
 * User: slovty
 * Date: 2020/8/18 16:08
 * @param $start
 * @param $end
 * @return string
 * Notes：
 */
function diffTimeText($start,$end){
    $diff_time = $end-$start;
    if ($diff_time <= 60){
        return '刚刚';
    }elseif ($diff_time > 60 && $diff_time < 3600){
        return floor($diff_time/60)."分钟前";
    }elseif ($diff_time > 3600 && $diff_time < 86400){
        return floor($diff_time/3600)."小时前";
    }elseif ($diff_time > 86400 && $diff_time < 604800){
        return floor($diff_time/86400)."天前";
    }elseif ($diff_time > 604800 && $diff_time < 2592000){
        return floor($diff_time/604800)."周前";
    }elseif ($diff_time > 2592000 ){
        return "一个月前";
    }
}