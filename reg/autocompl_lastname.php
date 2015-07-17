<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.01.14
 * Time: 16:42
 */

$term = trim($_REQUEST['term']);

try {
    $con = new PDO('mysql:dbname=s11;host=localhost;charset=utf8;', 'dbuser', 'dbpassword');
} catch (PDOException $e) {
    echo 'Could not connect: ' . $e->getMessage();
}

$fieldName     = trim($_REQUEST['field']);
$filter1_name  = trim($_REQUEST['filter1_name']);
$filter2_name  = trim($_REQUEST['filter2_name']);
$filter1_value = trim($_REQUEST['filter1']);
$filter2_value = trim($_REQUEST['filter2']);

$sth = $con->prepare("SELECT {$fieldName} FROM Client WHERE {$fieldName} LIKE '{$term}%' AND {$filter1_name} LIKE '{$filter1_value}%' AND {$filter2_name} LIKE '{$filter2_value}%' GROUP BY {$fieldName} LIMIT 0, 25;");
$result = $sth->execute();

if (empty($result)) exit();
$result = $sth->fetchAll();

$list_names = array();
foreach ($result as $item) {
    $list_names[] = $item[$fieldName];
}
echo implode("\n", $list_names);

