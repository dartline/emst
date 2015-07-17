<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
require_once 'library/fpdfex.php';
require_once 'library/cases_table.php';

/*
1) №
2) Ф.И.О. пострадавшего
3) Год рождения
4) Домашний адрес
5) Дата происшествия
6) Адрес места происшествия
7) Диагноз
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


    class TPDF_Ices extends FPDFEx
    {
        function TPDF_Ices()
        {
            $this->FPDFEx('Ice Trauma Report');
            $this->Open();
            $this->SetMargins(10,5,5);
        }

        function Header()
        {
            $this->SetFont('arial_rus','',10);
            $vHeight = $this->FontSize*1.5;
            if ( $this->PageNo() == 1 )
            {
                $vWidth  = $this->GetAreaWidth();
                $vW = 17;
                $vH = 17;
                $vX = $this->GetX();
                $vY = $this->GetY();
                $vX += ($vWidth-$vW)/2;

                $this->Image('images/reg_logo.jpeg', $vX, $vY, $vW, $vH);
                $this->Ln($vH);

                $this->Cell($vWidth, $vHeight, 'АДМИНИСТРАЦИЯ МОСКОВСКОВСКОГО РАЙОНА САНКТ-ПЕТЕРБУРГА', '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight, 'ОТДЕЛ ЗДРАВООХРАНЕНИЯ', '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight, 'Санкт-Петербургское государственное учреждение здравоохранения', '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight, '"Городская  поликлиника № 51"', '', 0, 'C');
                $this->Ln($vHeight);
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight, 'Сведения', '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight, 'о травматизме граждан', '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight, 'вследствие гололеда, падания наледей и сосулей с крыш зданий', '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight, 'с '.$this->BegDate.' г. по '.$this->EndDate.' г.', '', 0, 'C');
                $this->Ln($vHeight*2);

                $vRowData= array( '№',
                                  'Ф.И.О. пострадавшего',
                                  'Год рожд.',
                                  'Домашний адрес',
                                  'Дата происшествия',
                                  'Адрес места происшествия',
                                  'Диагноз');
                $this->OutTableRow($vHeight, $vRowData, 'C');
            }
            $vRowData = array( '1', '2', '3', '4', '5', '6', '7');
            $this->OutTableRow($vHeight, $vRowData, 'C');
        }


        function Render($AParams)
        {
            $vDB = GetDB();
            list($vTable, $vFilter, $vOrder) = ConstructCaseQuery($vDB, $AParams);
            $this->BegDate = Date2ReadableLong($AParams['beg_date']);
            $this->EndDate = Date2ReadableLong($AParams['end_date']);
            $vRecords = $vDB->Select($vTable, '*', $vFilter, $vOrder);

            $this->AddPage();
            $vNo = 0;
            while( $vRecord = $vRecords->Fetch() )
            {
                $this->DrawLine(++$vNo, $vRecord);
            }

        }



        function DrawLine($AIndex, $ACase)
        {
//            $vWidth = $this->GetAreaWidth();
            $vHeight = $this->FontSize*1.5;
            $vBornDate = explode('-',$ACase['born_date']);
            $vAccDate  = explode(' ', $ACase['accident_datetime']);
            $vRowData = array();
            $vRowData[] = $AIndex;
//            $vRowData[] = $ACase['last_name'].' '.$ACase['first_name'].' '.$ACase['patr_name'];
            $vRowData[] = FormatNameEx($ACase);
            $vRowData[] = @$vBornDate[0];
            $vRowData[] = SelectAddress(
                            FormatAddress($ACase['addr_reg_street'], $ACase['addr_reg_num'], $ACase['addr_reg_subnum'], $ACase['addr_reg_apartment']),
                            FormatAddress($ACase['addr_phys_street'], $ACase['addr_phys_num'], $ACase['addr_phys_subnum'], $ACase['addr_phys_apartment']));
            $vRowData[] = Date2Readable(@$vAccDate[0]);
            $vRowData[] = $ACase['accident'];
            $vRowData[] = $ACase['diagnosis'];
            $this->OutTableRow($vHeight, $vRowData);
        }



        function OutTableRow($AHeight, $ARowData, $AAlign='')
        {
            $vCols   = array(10,  35, 10, 35, 25, 40, 40);
            $vAligns = array('R','L','C','L','C','L','L');
            $vSplitedData = array();
            $vMaxLines = 1;

            for($i=0; $i<count($vCols); $i++)
            {
                $vSplitedData[$i] = $this->SplitText( $ARowData[$i], $vCols[$i] );
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

    $vDoc = new TPDF_Ices;
    $vDoc->Render($_GET);

//    while (ob_get_level())
//        ob_end_clean();

//    header('Accept-Ranges: bytes');
    $vDoc->Output('Ices.pdf', 'I');
?>
