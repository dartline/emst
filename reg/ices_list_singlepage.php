<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
require_once 'library/cases_table.php';
require_once 'library/table.php';

/*
1) '№',
2) "ФИО постр.\nгод рожд.\n(адрес прож.,\nконт.тел.)",
3) "дата,\nвремя получ. травмы",
4) "точный адрес\nполуч.травмы\n(c указ.конкр.места)",
5) "название обсл.\nорганизации,\nна терр.кот.\nполуч. травма",
6) "№ участка,\nФИО, должн.\nлица, ответст.за\nданн.террит.",
7) "госпит.в\n(назв. мед.\nучрежд.,\nдиагноз)",
*/


    function ConstructCaseQuery(&$ADB, $AParams)
    {
        $vFilter = array();
        $vTable = 'emst_cases';
        $vFilter[] = 'ice_trauma != 0';
        if ( array_key_exists('beg_date', $AParams) )
           $vFilter[] = $ADB->CondGE('create_time', $AParams['beg_date']);
        if ( array_key_exists('end_date', $AParams) )
           $vFilter[] = $ADB->CondLT('create_time', DateAddDay($AParams['end_date']));
        $vFilter = implode(' AND ', $vFilter);
        $vOrder = 'emst_cases.id';
        return array($vTable, $vFilter, $vOrder);
    }


    function SelectAddress($AReg, $APhys)
    {
        if ( trim($APhys) == '' )
            return trim($AReg);
        else
            return trim($APhys);
    }


    function ProduceHeader($vBegDate, $vEndDate)
    {
        print '<table width=90% border="0" cellspacing="0" cellpadding="0"><tr><td>';
        print '<h3 align="center">АДМИНИСТРАЦИЯ МОСКОВСКОВСКОГО РАЙОНА САНКТ-ПЕТЕРБУРГА<br>';
        print 'ОТДЕЛ ЗДРАВООХРАНЕНИЯ</h3>';
        print '</td></tr><tr><td>';
        print '<h2 align="center">Санкт-Петербургское государственное учреждение здравоохранения<br>';
        print '"Городская  поликлиника № 51"</h2>';
        print '</td></tr><tr><td>';
        print '<h1 align="center">Сведения<br>';
        print 'о травматизме граждан<br>';
        print 'вследствие гололеда, падания наледей и сосулей с крыш зданий<br>';
        print 'с '.Date2ReadableLong($vBegDate).' г. по '.Date2ReadableLong($vEndDate).' г.</h3>';
        print '</td></tr></table>';
        print "\n";

        print '<table width=90% border="4" cellspacing="0" cellpadding="2">';
        print '<thead>';
        print '<th valign=top width=10%>'.tcfText("№").'</th>';
        print '<th valign=top width=18%>'.tcfText("ФИО постр.год рожд.\n(адрес прож.,\nконт.тел.)").'</th>';
        print '<th valign=top width=12%>'.tcfText("дата,\nвремя\nполуч.\nтравмы").'</th>';
        print '<th valign=top width=15%>'.tcfText("точный адрес\nполуч.травмы\n(c указ.конкр.\nместа)").'</th>';
        print '<th valign=top width=15%>'.tcfText("название обсл.\nорганизации,\nна терр.кот.\nполуч. травма").'</th>';
        print '<th valign=top width=15%>'.tcfText("№ участка,\nФИО, должн.\nлица, ответст.за\nданн.террит.").'</th>';
        print '<th valign=top width=15%>'.tcfText("госпит.в\n(назв. мед.\nучрежд.,\nдиагноз)").'</th>';
        print '</thead><tbody>';
        print "\n";
    }

    function ProduceFooter()
    {
        print '</tbody></table>';
        print "\n";
    }


    function ProduceTable($ABegDate, $AEndDate)
    {
            $vDB = GetDB();
            list($vTable, $vFilter, $vOrder) = ConstructCaseQuery($vDB, array('beg_date'=>$ABegDate, 'end_date'=>$AEndDate));
            $vRecords = $vDB->Select($vTable, '*', $vFilter, $vOrder);

            $vNo = 0;
            while( $vRecord = $vRecords->Fetch() )
            {
                DrawLine(++$vNo, $vRecord);
            }
    }



    function DrawLine($AIndex, $ACase)
    {
        $vBornDate = explode('-',$ACase['born_date']);
        $vAccDate  = explode(' ', $ACase['accident_datetime']);
        $vAccDate[0] = Date2Readable($vAccDate[0]);
        $vAccDate[1] = explode(':', $vAccDate[1]);
        $vAccDate[1] = $vAccDate[1][0].':'.$vAccDate[1][1];
        $vAccDate = implode(', ', $vAccDate);

        $vUnknown  = 'нет сведений';


        $vRowData = array();

        $vRowData[] = $AIndex;
//        $vRowData[] = FormatName($ACase['last_name'], $ACase['first_name'],$ACase['patr_name'])
        $vRowData[] = FormatNameEx($ACase)
                    . ",\n"
                    . @$vBornDate[0]
                    . ",\n"
                    . FormatAddress($ACase['addr_reg_street'], $ACase['addr_reg_num'], $ACase['addr_reg_subnum'], $ACase['addr_reg_apartment'])
                    . ",\n"
                    . $ACase['phone']
                    ;
//            $vRowData[] = Date2Readable($ACase['accident_datetime']);
        $vRowData[] = $vAccDate;
        $vRowData[] = $ACase['accident'];
        $vRowData[] = $vUnknown;
        $vRowData[] = $vUnknown;
//            $vRowData[] = $vUnknown . ",\n" . $ACase['diagnosis'];
        $vRowData[] = $ACase['diagnosis'];
        OutTableRow($vRowData);
    }



    function OutTableRow($ARowData)
    {
//        $vCols   = array(10,  35, 25, 30, 30, 30, 30);
//        $vAligns = array('R','L','L','L','L','L','L');

        $N = 7;
        print '<tr>';
        for($i=0; $i<$N; $i++)
        {
            print '<td valign=top>'.tcfText($ARowData[$i]).'</td>';
        }
    }

// =======================================================================

    if ( array_key_exists('beg_date', $_GET) )
      $vBegDate = $_GET['beg_date'];
    else
      $vBegDate = date('Y-m-d');

    if ( array_key_exists('end_date', $_GET) )
      $vEndDate = $_GET['end_date'];
    else
      $vEndDate = date('Y-m-d');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
    <title>Отчет "гололёд" с <?= Date2ReadableLong($vBegDate) ?> г. по <?= Date2ReadableLong($vEndDate)  ?> </title>
</head>
<body>
<?php
    ProduceHeader($vBegDate, $vEndDate);
?>
<?php
    ProduceTable($vBegDate, $vEndDate);
?>
<?php
    ProduceFooter();
?>
</body>
</html>
