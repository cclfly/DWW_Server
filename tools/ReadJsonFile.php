<?php
/**
 * Created by PhpStorm.
 * User: cclfly
 * Date: 2016/12/8
 * Time: 16:11
 */
function read_json_file($path)
{
    $file = fopen($path, 'r');
    $json = fread($file, filesize($path));
    $data = json_decode($json);
    fclose($file);
    return $data;
}