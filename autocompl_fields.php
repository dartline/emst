<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.01.14
 * Time: 16:42
 */

$term = trim($_REQUEST['term']);
if (empty($term)) exit();

$link = mysql_connect('localhost', 'user', 'password');
if (!$link) throw new Exception('Do not connect: ' . mysql_error());

$db_selected = mysql_select_db('emst', $link);
if (!$db_selected) throw new Exception('Do not select db schema: ' . mysql_error());

//$term = iconv('utf-8', 'windows-1251', $term);
echo "SELECT last_name FROM imp_clients WHERE last_name LIKE CONVERT('{$term}%' USING latin1);";

$result = mysql_query("SELECT last_name FROM imp_clients WHERE last_name LIKE '{$term}%';");
if (!$result) throw new Exception('Bad query: ' . mysql_error());

while ($row = mysql_fetch_assoc($result)) {
    $a_json[] = array("value"=>$row['last_name']);
}

$strings = '"' . implode('","', $a_json) . '"';
echo "{{$strings}}";
