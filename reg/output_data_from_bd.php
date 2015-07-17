<?php

try {
    $con = new PDO('mysql:dbname=s11;host=localhost;charset=utf8;', 'dbuser', 'dbpassword');
} catch (PDOException $e) {
    $html .= 'Could not connect: ' . $e->getMessage();
}

$whereStr = array();

$nameFields = array('lastName', 'firstName', 'patrName');
$dateFields = array('birthDate');
$otherFields = array('sex');

foreach ($_REQUEST as $key=>$value) {
    if (empty($_REQUEST[$key])) continue;

    if (in_array($key, $nameFields)) {
        $reqValue = trim($value);
        $whereStr[] = "({$key} LIKE '{$reqValue}%')";
    } elseif (in_array($key, $dateFields)) {
        $whereStr[] = "({$key} = DATE('{$value}'))";
    } elseif (in_array($key, $otherFields)) {
        $whereStr[] = "({$key} = '{$value}')";
    }
}

if (count($whereStr) == 0) exit();
$whereStr = implode(' AND ', $whereStr);

$query = "
    SELECT id, lastName,firstName, patrName,birthDate
    FROM Client
    WHERE {$whereStr} GROUP BY birthDate ;
";

$sth = $con->prepare($query);
$sth->execute();
$result = $sth->fetchAll();
  // выводим данные в таблицу
$html = '<table  border="1" style="height: 100px;width: 100%;overflow: visible;">';
$html .= '<thead>';
$html .= '<tr>';
$html .= '<th>Фамилия</th>';
$html .= '<th>Имя</th>';
$html .= '<th>Отчество</th>';
$html .= '<th>Дата рождения</th>';
$html .= '</tr>';
$html .= '</thead>';
$html .= '<tbody>';

foreach($result as $item){
    $html .= '<tr id="field_patient" data-id="' . $item['id'] . '" style="cursor: pointer;" onmouseover="this.style.background=\'#ccc\'" onmouseout="this.style.background=\'\'">';
    $html .= '<td>' . $item['lastName'] . ' </td>';
    $html .= '<td>' . $item['firstName'] . '</td>';
    $html .= '<td>' . $item['patrName'] . ' </td>';
    $html .= "<td>   {$item['birthDate']}    </td>";
    $html .= '</tr>';
}
$html .= '</tbody>';
$html .= '</table>';

echo $html;