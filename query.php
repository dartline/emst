<?php
	header('Content-Type: text/html;charset=utf-8');
	$unixTime = time();
	$nowDate = date('Y-m-d',$unixTime);
	$args = array_merge($_GET,$_POST);
	if(!isset($args['dateBegin']))$args['dateBegin'] = $nowDate; else{
		if($args['dateBegin'] == '')$args['dateBegin'] = $nowDate;
	}
	if(!isset($args['timeBegin']))$args['timeBegin'] = '00:00'; else{
		if($args['timeBegin'] == '')$args['timeBegin'] = '00:00';
	}
	if(!isset($args['dateEnd']))$args['dateEnd'] = $nowDate; else{
		if($args['dateEnd'] == '')$args['dateEnd'] = $nowDate;
	}
	if(!isset($args['timeEnd']))$args['timeEnd'] = '23:59'; else{
		if($args['timeEnd'] == '')$args['timeEnd'] = '23:59';
	}
	
	$host = 'localhost';
	$dbname = 'travma';
	$user = 'dbuser';
	$password = 'dbpassword';
	try{
		if($dbname !== '')$dbname = ';dbname='.$dbname;
		$pdoEmst = new PDO('mysql:host='.$host.$dbname,$user,$password);
		$pdoEmst->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);  
		$pdoEmst->exec('SET NAMES utf8');
	}catch(PDOException $e){  
		echo 'Ошибка при соединении с MySQL!<br/>';
		return;
	}

	/* Все пациенты*/
	$sqlSelectCases = "SELECT `id`,`first_name`,`last_name`,`patr_name` FROM `emst_cases`";	
	$stmt = $pdoEmst->prepare($sqlSelectCases);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	$rows = $stmt->fetchAll();
	$count = 0;
	$sqlTmp = '';
	$htmlPatient = '';
	foreach($rows as $row){
		$id = $row['id'];
		$selected = '';
		if(isset($args['patient'])){
			$patient = $args['patient'];
			if(is_array($patient)){
				if(array_search($id,$patient) !== false){
					$selected = ' selected';
					if($count)$sqlTmp .= ' OR ';
					$sqlTmp .= '`emst_surgeries`.`case_id` = '.$id;
					$count++;
				}
			}
		}
		
		$htmlPatient .= '<option value="'.$id.'"'.$selected.'>'
		.$row['first_name'].' '.$row['last_name'].' '.$row['patr_name']
		.'</option>';
	}
	
	/* Явки выбранных пациентов на выбранный период */
	$sqlSelectSurgeries = "SELECT
	CONCAT(`emst_cases`.`first_name`,' ',`emst_cases`.`last_name`,' ',`emst_cases`.`patr_name`) AS `ФИО пациента`,
	`emst_surgeries`.`date` AS `Дата явки`,
	`users`.`login` AS `Фамилия врача`,
	`emst_surgeries`.`date` AS `Диагноз`
	FROM `emst_surgeries` 
	LEFT JOIN `emst_cases` ON `emst_cases`.`id` = `emst_surgeries`.`case_id` 
	LEFT JOIN `users` ON `users`.`id` = `emst_surgeries`.`user_id` 
	WHERE `emst_surgeries`.`diagnosis_mkb` LIKE 'S9%' 
	AND `date` >= ".$pdoEmst->quote($args['dateBegin']." ".$args['timeBegin'])." AND `date` <= ".$pdoEmst->quote($args['dateEnd']." ".$args['timeEnd']);
	if($sqlTmp !== '')$sqlSelectSurgeries .= " AND (".$sqlTmp.")";
	$stmt = $pdoEmst->prepare($sqlSelectSurgeries);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	$rows = $stmt->fetchAll();	
	$htmlYavki = '<table><thead>
	<tr><th>ФИО пациента</th><th>Дата явки</th><th>Фамилия врача</th><th>Диагноз</th></tr>
	</thead><tbody>';
	foreach($rows as $row){
		$htmlYavki .= '<tr><td>'.$row['ФИО пациента'].'</td><td>'.$row['Дата явки'].'</td><td>'
		.$row['Фамилия врача'].'</td><td>'.$row['Диагноз'].'</td></tr>';
	}
	$htmlYavki .= '</tbody></table>';
	$html = '
	<html>
	<head>
		<title>Явки  обращения пациента</title>
		<base href="/" />
		<meta charset="utf-8">
		<meta http-equiv="Content-Script-Type" content="text/javascript">
		<meta http-equiv="Content-Style-Type" content="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="author" content="Абсалямов Амир">
		<style>
			*{
				vertical-align:top;
				box-sizing: border-box;
				
			}
			label,div{
				padding:10px;
				display: block;
			}
			.lt{
				float:left;
			}
			.border{
				border:solid 1px #EEE;
				margin:10px;
			}
			input{
				padding:5px;
			}
			form{
				overflow:hidden;
			}
			table{
				width:100%;
			}
			td,th{
				padding:5px;
				border:solid 1px #EEE;
			}
			th{
				font-weight:bold;
			}
			tr:nth-child(even){
				background:#EEE;
			}
		</style>
	<head>
	<body>
	<h1>Явки пациентов</h1>
	<form method="get" class="lt border">
		<div class="lt">
			<label>Пациенты: <select size="20" multiple name="patient[]">'.$htmlPatient.'</select></label>
		</div>
		<div class="lt">
			<label>Начало периода:<br/>
				<input type="date" name="dateBegin" value="'.$args['dateBegin'].'"/>
				<input type="time" name="timeBegin" value="'.$args['timeBegin'].'"/>
			</label>
			<label>Конец периода:<br/>
				<input type="date" name="dateEnd" value="'.$args['dateEnd'].'"/>
				<input type="time" name="timeEnd" value="'.$args['timeEnd'].'"/>
			</label>
			<input type="submit" value="Вывести все явки"/>
		</div>
	</form>
	<div class="lt border" style="width:100%;">
		<h2>Все явки</h2>
		'.$htmlYavki.'
	</div>
	</body>
	</html>';
	
	echo $html;
?>