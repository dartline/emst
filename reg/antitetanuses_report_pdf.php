<?php

#####################################################################
#
# Травмпункт. (c) 2005 Vista
#
#####################################################################

require_once 'library/fpdfex.php';
require_once 'library/cases_table.php';

/*
1) № И.Б.
2) Дата и время обращения
3) Врач
4) Ф.И.О. пострадавшего
5) Дата рождения (полных лет)
6) Пол
7) Aдрес
8) Телефон
9) Диагноз
10) Манипуляция
11) Описание
*/
/*
function ConstructCaseQuery(&$ADB, $AParams)
{
    $vFilter = array();

    $vTable = 'emst_cases LEFT JOIN rb_antitetanus ON emst_cases.antitetanus_id = rb_antitetanus.id';

    $vFilter[] = $ADB->CondIsNotNull('antitetanus_id');
    if ( array_key_exists('beg_date', $AParams) )
       $vFilter[] = $ADB->CondGE('create_time', $AParams['beg_date']);
    if ( array_key_exists('end_date', $AParams) )
       $vFilter[] = $ADB->CondLT('create_time', DateAddDay($AParams['end_date']));

    $vFilter = implode(' AND ', $vFilter);
    $vOrder = 'emst_cases.id';
    return array($vTable, $vFilter, $vOrder);
}


class TPDF_Antitetanuses_Report extends FPDFEx
{
    function TPDF_Antitetanuses_Report()
    {
        $this->FPDFEx('Antitetanuses Report', 'L');
        $this->Open();
        $this->SetMargins(10,5,5);

        $this->AgesList = array(-999, 15, 18, 30, 40, 50, 60, 70);
    }

    function Header()
    {
        $this->SetFont('arial_rus','',10);
        $vHeight = $this->FontSize*1.5;
        $vWidth  = $this->GetAreaWidth();

        if ( $this->PageNo() == 1 )
        {
            $vX = $this->GetX();
            $vY = $this->GetY();
            $this->Cell($vWidth, $vHeight, 'Отчет по прививкам', '', 0, 'C');
            $this->SetX($vX);
            $this->Cell($vWidth, $vHeight, 'стр. '.$this->PageNo(), '', 0, 'R');
            $this->Ln($vHeight);

            $this->Cell($vWidth, $vHeight, 'за период с '.$this->BegDate.' г. по '.$this->EndDate.' г.', '', 0, 'C');
            $this->Ln($vHeight*2);
        }
        else
        {
            $this->Cell($vWidth, $vHeight, 'Отчет по прививкам за период с '.$this->BegDate.' г. по '.$this->EndDate.' г. / стр.'.$this->PageNo(), '', 0, 'R');
            $this->Ln($vHeight*2);
        }
    }


    function Render($AParams)
    {
        $vDB = GetDB();
        list($vTable, $vFilter, $vOrder) = ConstructCaseQuery($vDB, $AParams);
        $this->BegDate = Date2ReadableLong($AParams['beg_date']);
        $this->EndDate = Date2ReadableLong($AParams['end_date']);
        $this->Table = array();
        $vRecords = $vDB->Select($vTable, 'emst_cases.*, rb_antitetanus.name as antitetanus_name', $vFilter, $vOrder);
        while( $vRecord = $vRecords->Fetch() )
        {
            $this->AddRecord($vRecord);
        }
        $this->AddPage();

    }



    function AddRecord($ACase)
    {
        $vAge = CalcAge($ACase['born_date'], $ACase['create_time']);


//            $vWidth = $this->GetAreaWidth();
        $vHeight = $this->FontSize*1.5;
        $vRowData = array();
        $vRowData[] = $ACase['id'];
        $vRowData[] = Date2Readable($ACase['create_time']);
        $vRowData[] = FormatUserName($ACase['first_doctor_id']);
        $vRowData[] = FormatName($ACase);
        $vRowData[] = $ACase['last_name'].' '.$ACase['first_name'].' '.$ACase['patr_name'];
        $vRowData[] = FormatBornDateAndAge();
        $vRowData[] = FormatSex($ACase['is_male']);
        $vRowData[] = FormatAddresses(
                         FormatAddress($ACase['addr_reg_street'],  $ACase['addr_reg_num'],  $ACase['addr_reg_subnum'],  $ACase['addr_reg_apartment']),
                         FormatAddress($ACase['addr_phys_street'], $ACase['addr_phys_num'], $ACase['addr_phys_subnum'], $ACase['addr_phys_apartment']));
        $vRowData[] = $ACase['phone'];
        $vRowData[] = $ACase['diagnosis'];

        $vRowData[] = $ACase['antitetanus_name'];
        $vRowData[] = $ACase['antitetanus_series'];
        $this->OutTableRow($vHeight, $vRowData);
    }



    function OutTableRow($AHeight, $ARowData, $AAlign='')
    {
//                            1   2    3   4   5   6   7   8   9  10   11
        $vCols   = array(15,  25, 25, 30, 25, 10, 35, 20, 30, 30, 30);
        $vAligns = array('R','L','L','L','L','C','L','L','L','L','L');

        $vSplitedData = array();
        $vMaxLines = 1;

        for($i=0; $i<count($vCols); $i++)
        {
            $vSplitedData[$i] = $this->SplitText( @$ARowData[$i], $vCols[$i] );
            $vMaxLines = max($vMaxLines, count($vSplitedData[$i]));
        }
        $this->CheckSpace($AHeight*$vMaxLines);
        $vX = $this->GetX();
        $vY = $this->GetY();
        for($i=0; $i<count($vCols); $i++)
        {
            $this->Cell($vCols[$i],  $AHeight*$vMaxLines, '',  'LTRB', 0, '');
        }
        $this->SetXY($vX, $vY);
        for($j=0; $j<$vMaxLines; $j++)
        {
         for($i=0; $i<count($vCols); $i++)
            {
                $vText = @$vSplitedData[$i][$j];
                $this->Cell($vCols[$i],  $AHeight, $vText,  '', 0, empty($AAlign)?$vAligns[$i]:$AAlign);
            }
            $this->Ln($AHeight);
        }
    }
}

// =======================================================================

if ( !array_key_exists('beg_date', $_GET) )
  $_GET['beg_date'] = date('Y-m-d');
if ( !array_key_exists('end_date', $_GET) )
  $_GET['end_date'] = date('Y-m-d');

$vDoc = new TPDF_Antitetanuses_Report;
$vDoc->Render($_GET);

//    while (ob_get_level())
//        ob_end_clean();

//    header('Accept-Ranges: bytes');
$vDoc->Output('AntitetanusesReport.pdf', 'I');
*/
/*
function array_find($AWhat, &$AArray)
{
    $vFound = false;
    $L = 0;
    $H = count($AArray)-1;
    while ( $L<=$H ) do
    {
        $I = ($L + $H) >> 1;
        $C = $AArray[$I]-$AWhat;
        if ( $C < 0 )
            $L = $I + 1;
        else
        {
            $H := $I - 1;
            if ( $C==0  )
            {
                $vFound = true;
                $L := $I;
            }
        }
    };
    return array($vFound, $L);
}

*/


?>
  Нету такого отчёта