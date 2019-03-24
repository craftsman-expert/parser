<?php





/**
 * @param string $file_name
 *
 * @return string
 */
function extractFileExt($file_name)
{
    /**
     * Класс SplFileInfo предлагает высокоуровневый объектно-ориентированный интерфейс к информации для отдельного файла.
     */
    $info = new SplFileInfo($file_name);
    $ext = $info->getExtension();
    unset($info);
    return $ext;
}



function extractFilePath($file_name)
{
    /**
     * Класс SplFileInfo предлагает высокоуровневый объектно-ориентированный интерфейс к информации для отдельного файла.
     */
    $info = new SplFileInfo($file_name);
    $path = $info->getPath();
    unset($info);
    return $path;
}




/**
 * @param $var
 *
 * @return bool
 */
function isBool($var)
{
    if (!is_string($var))
        return (bool) $var;
    switch (strtolower($var)) {
        case 'true':
            return true;
        case 'false':
            return true;
        default:
            return false;
    }
}



/**
 * @param $var
 *
 * @return bool|null
 */
function toBool($var)
{
    if (!is_string($var))
        return (bool) $var;
    switch (strtolower($var)) {
        case 'true':
            return true;
        case 'false':
            return false;
        default:
            return null;
    }
}



/**
 * @param $var
 *
 * @return bool|float|int|null
 */
function varCast($var)
{
    if (is_numeric($var)) {
        return (int) $var;
    } elseif (is_float($var)) {
        return (double) $var;
    } elseif (toBool($var)) {
        return toBool($var);
    } else {
        return $var;
    }
}



/**
 * @param array $array
 *
 * @return array
 */
function cast($array)
{
    $arr = [];
    foreach ($array as $key => $val) {
        $arr[$key] = varCast($val);
    }
    return $arr;
}



/**
 * @param     $str
 * @param int $pl
 * @param int $pt
 * @param int $pb
 */
function prints($str, $pl = 0, $pt = 0, $pb = 0)
{
    $pd = '';
    for ($i = 0; $i < ($pl * 3); $i++){
        $pd .= ' ';
    }

    for ($i = 0; $i < ($pt * 2); $i++){
        print PHP_EOL;
    }

    print $pd . $str;

    for ($i = 0; $i < ($pb * 2); $i++){
        print PHP_EOL;
    }
}

/**
 * @param string $str
 * @param int    $pl
 * @param int    $pt
 * @param int    $pb
 *
 * @return string
 */
function println($str, $pl = 0, $pt = 0, $pb = 0)
{
    $pd = '';
    for ($i = 0; $i < ($pl * 3); $i++){
        $pd .= ' ';
    }

    for ($i = 0; $i < ($pt * 2); $i++){
        print PHP_EOL;
    }

    print $pd . $str . PHP_EOL;

    for ($i = 0; $i < ($pb * 2); $i++){
        print PHP_EOL;
    }
}



/**
 * @param $func_get_args
 *
 * @return array
 */
function getArguments($func_get_args)
{
    $arguments = [];
    if (is_array($func_get_args) && !empty($func_get_args)) {
        foreach ($func_get_args as $item) {
            list($name, $value) = explode('=', $item);
            $arguments[$name] = $value;
        }
    }

    return $arguments;

}



/**
 * @param $pattern
 * @param $str
 *
 * @return mixed
 */
function pregMatch($pattern, $str)
{
    preg_match($pattern, $str, $matches);
    return $matches;
}



/**
 *
 */
function clearConsole()
{
    echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
}




