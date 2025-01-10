<?php

const ADMIN = 'admin';
const CUSTOMER = 'customer';

function activeSide($route)
{
    if (Request::routeIs($route . '*')) {
        return 'active-side';
    }
}

function activeSideParent($routs)
{
    foreach ($routs as $i) {
        if (activeSide($i)) {
            return 'active-side-patent';
        }
    }
}

function activeUri($uri)
{
    if ($_SERVER['REQUEST_URI'] == $uri) {
        return 'category-active';
    }
}

function activeSideByName($route)
{
    if (Route::currentRouteName() == $route) {
        return 'active-side';
    }
}

function activeByController($route)
{
    if (stristr(Route::currentRouteName(), '.', true) == $route) {
        return 'active-side';
    }
}

function onlyInt($val)
{
    return preg_replace("/[^0-9]/", '', $val);
}

function clearText($text, $symbols = ['<', '>'])
{
    return str_replace($symbols, '', $text);
}

function mbSubstr($text, $start = 0, $end = 50)
{
    if (iconv_strlen($text, 'UTF-8') > $end) {
        return mb_substr($text, $start, $end, 'UTF-8') . '..';
    }
    return $text;
}

function qtyPage()
{
    $qty = (int)(session('pagination'));
    if ($qty) {
        return $qty;
    }
    return 20;
}

function extFileName($nameFile, $dot = true)
{
    $e = explode('.', $nameFile);
    if ($end = end($e)) {
        return $dot ? '.' . $end : $end;
    }
}

function modelsSelected($models = [], $id = [], $property = null, $exceptId = [])
{
    $i = [];
    foreach ($models as $item) {
        if ($exceptId && in_array($item->id, $exceptId)) continue;
        $i[] = [
            'id' => $item->id,
            'name' => $item->$property ?? $item->name,
            'selected' => in_array($item->id, $id) ? 'selected' : ''
        ];
    }
    return $i;
}

function paginationActive($qty)
{
    if ($qty == qtyPage()) {
        return 'selected';
    }
}

function is_json($string)
{
    return is_string($string) && is_array(json_decode($string, true)) ? true : false;
}

function numWord($value, $text, $show = true)
{
    if ($text == 'ball') {
        $words = ['бал', 'балла', 'балов'];
    }
    if ($text == 'event') {
        $words = ['событие', 'события', 'событий'];
    }
    $num = $value % 100;
    if ($num > 19) {
        $num = $num % 10;
    }

    $out = ($show) ? $value . ' ' : '';
    switch ($num) {
        case 1:
            $out .= $words[0];
            break;
        case 2:
        case 3:
        case 4:
            $out .= $words[1];
            break;
        default:
            $out .= $words[2];
            break;
    }
    return $out;
}

function declensionOfNumbers($num, $period)
{
    $numret = $num;
    $month = ['месяц', 'месяца', 'месяцев'];
    $day = ['день', 'дня', 'дней'];
    $hour = ['час', 'часа', 'часов'];
    $min = ['минуту', 'минуты', 'минут'];
    if ($period == 'month') $titles = $month;
    if ($period == 'day') $titles = $day;
    if ($period == 'hour') $titles = $hour;
    if ($period == 'min') $titles = $min;
    $cases = [2, 0, 1, 1, 1, 2];
    return $numret . " " . $titles[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];
}

function btnCreate($url, $name = '', $title = '')
{
    $title = $title ? $title : __('app.Add');
    $name = $name ? '<i class="fas fa-plus"></i> ' . $name : '<i class="fas fa-plus"></i>';
    return '<a class="btn btn-outline-success btn-dashboard"
       href="' . $url . '"
       title="' . $title . '"> ' . $name . ' </a>';
}

function checkIsJson($str)
{
    return is_string($str) && is_array(json_decode($str, true)) ? true : false;
}

function extensionByMime($file)
{
    $extension = null;
    if (!is_file($file)) {
        return $extension;
    }
    $fInfo = finfo_open();
    $mime_type = finfo_buffer($fInfo, file_get_contents($file), FILEINFO_MIME_TYPE);
    switch ($mime_type) {
        case 'image/png':
            $extension = 'png';
            break;
        case 'image/jpeg':
            $extension = 'jpg';
            break;
        case 'application/pdf':
            $extension = 'pdf';
            break;
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            $extension = 'docx';
            break;
        case 'application/msword':
            $extension = 'doc';
            break;
        case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            $extension = 'xlsx';
            break;
        case 'application/vnd.ms-excel':
            $extension = 'xls';
            break;
    }
    finfo_close($fInfo);
    return $extension;
}

function strikethroughPrice($text)
{
    $i = '';
    if (!$text) {
        return $i;
    }
    foreach (str_split($text) as $item) {
        if ($item == '.') {
            $item = ',';
        }
        $i .= $item . '̶';
    }
    return $i . ' грн';
}

function subtract_percent($sum, $percent)
{
    if (!$percent) {
        return 0;
    }
    $num = (1 - $percent / 100) * $sum;
    if (!$num) {
        return 0;
    }
    return format_float($num);
}

function ball_percent($sum, $percent)
{
    if ($percent == 100) {
        return $sum;
    }
    if (!$percent) {
        return 0;
    }
    $i = $sum / 100;
    if (!$i) {
        return 0;
    }
    $i = $i * $percent;
    return format_float($i);
}

function format_float($s)
{
    $num = 0;
    $e = explode('.', $s);
    if (isset($e[0])) {
        $num = $e[0];
    }
    if (isset($e[1]) && $e[1]) {
        $i = (substr($e[1], 0, 2));
        if ($i) {
            $num = $num . '.' . $i;
        }
    }
    $num = (float)$num;
    if ($num) {
        return $num;
    }
    return 0;
}

function exception_format_api($e)
{
    return ['status' => 'error', 'exception' => $e->getMessage()];
}

function saveFileJson($data, $pathFile)
{
    return file_put_contents($pathFile, json_encode($data, JSON_UNESCAPED_UNICODE));
}

function convert_sec_to_time($sec)
{
    return gmdate("H:i:s", $sec);
}

function convert_sec_to_time2($sec)
{
    $secs = $sec % 60;
    $hrs = $sec / 60;
    $min = $hrs % 60;
    $hrs = $hrs / 60;
    return (int)$hrs . ":" . (int)$min . ":" . (int)$secs;
}

function isOnlyCyrillicLetters($str)
{
    if (preg_match("/^[0-9А-Яа-яЇїЁёІіъЪЄє ' -]+$/isu", $str) === 0) {
        return false;
    }
    return true;
}

function getConstByType($class, $type)
{
    $reflectionClass = new ReflectionClass($class);
    $key = strtoupper($type);
    $constants = $reflectionClass->getConstants();
    $constArr = [];
    foreach ($constants as $name => $values) {
        if (strpos($name, $key) !== false) {
            $constArr[$name] = $values;
        }
    }
    return $constArr;
}

function getFullPhoneFormat(?string $phone, string $phoneCode = "380", int $phoneNeedLen = 12): ?string
{
    if (!$phone || (strlen($phone) > $phoneNeedLen)) {
        return null;
    }
    if (preg_match("/^\+[0-9]{" . $phoneNeedLen . "}$/", $phone)) {
        return $phone;
    }
    if (preg_match("/^[0-9]{" . $phoneNeedLen . "}$/", $phone)) {
        return "+" . $phone;
    }
    $phone = onlyInt($phone);
    $phoneLen = strlen($phone);
    $needAddCount = $phoneNeedLen - $phoneLen;
    $strAdd = substr($phoneCode, 0, $needAddCount);
    $phone = "+" . $strAdd . $phone;

    if (preg_match("/^\+" . $phoneCode . "[0-9]{" . ($phoneNeedLen - strlen($phoneCode)) . "}$/", $phone)) {
        return $phone;
    }
    return null;
}
