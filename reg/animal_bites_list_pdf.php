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
9) Происшествие
10) Дата и время происшествия
11) Диагноз
12)Телефонограмма
*/

    function ConstructCaseQuery(&$ADB, $AParams)
    {
        $vFilter = array();

        $vTable = 'emst_cases';

        $vFilter[] = 'animal_bite_trauma != 0';
        if ( array_key_exists('beg_date', $AParams) )
           $vFilter[] = $ADB->CondGE('create_time', $AParams['beg_date']);
        if ( array_key_exists('end_date', $AParams) )
           $vFilter[] = $ADB->CondLT('create_time', DateAddDay($AParams['end_date']));

        $vFilter = implode(' AND ', $vFilter);
        $vOrder = 'emst_cases.id';
        return array($vTable, $vFilter, $vOrder);
    }


    class TPDF_SESes extends FPDFEx
    {
        function TPDF_SESes()
        {
            $this->FPDFEx('Animal Bites Messages Report', 'L');
            $this->Open();
            $this->SetMargins(10,5,5);
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
                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','Журнал "Укусы животных"'), '', 0, 'C');
                $this->SetX($vX);
                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','стр. ').$this->PageNo(), '', 0, 'R');
                $this->Ln($vHeight);

                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','с ').$this->BegDate.iconv('utf-8', 'cp1251',' г. по ').$this->EndDate.iconv('utf-8', 'cp1251',' г.'), '', 0, 'C');
                $this->Ln($vHeight*2);

                $vRowData= array( iconv('utf-8', 'cp1251','№'),
                                  iconv('utf-8', 'cp1251','Дата и время обращения'),
                                  iconv('utf-8', 'cp1251','Врач'),
                                  iconv('utf-8', 'cp1251','Ф.И.О. пострадавшего'),
                                  iconv('utf-8', 'cp1251','Дата рождения (полных лет)'),
                                  iconv('utf-8', 'cp1251','Пол'),
                                  iconv('utf-8', 'cp1251','Адрес'),
                                  iconv('utf-8', 'cp1251','Телефон'),
                                  iconv('utf-8', 'cp1251','Происшествие'),
                                  iconv('utf-8', 'cp1251','Дата и время проис- шествия'),
                                  iconv('utf-8', 'cp1251','Диагноз'),
                                  iconv('utf-8', 'cp1251','Телефоно- грамма'));
                $this->OutTableRow($vHeight, $vRowData, 'C');
            }
            else
            {
                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','Журнал "Укусы животных" с ').$this->BegDate.iconv('utf-8', 'cp1251',' г. по ').$this->EndDate.iconv('utf-8', 'cp1251',' г. / стр.').$this->PageNo(), '', 0, 'R');
                $this->Ln($vHeight*2);
            }

            $vRowData = array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
            $this->OutTableRow($vHeight, $vRowData, 'C');
        }


        function Render($AParams)
        {
            $vDB = GetDB();
            list($vTable, $vFilter, $vOrder) = ConstructCaseQuery($vDB, $AParams);
            $this->BegDate = iconv('utf-8', 'cp1251',Date2ReadableLong($AParams['beg_date']));
            $this->EndDate = iconv('utf-8', 'cp1251',Date2ReadableLong($AParams['end_date']));
            $vRecords = $vDB->Select($vTable, '*', $vFilter, $vOrder);

            $this->AddPage();
            while( $vRecord = $vRecords->Fetch() )
            {
                $this->DrawLine($vRecord);
            }

        }



        function DrawLine($ACase)
        {
//            $vWidth = $this->GetAreaWidth();
            $vHeight = $this->FontSize*1.5;
            $vRowData = array();
            $vRowData[] = $ACase['id'];
            $vRowData[] = Date2Readable($ACase['create_time']);
            $vRowData[] = FormatUserName($ACase['first_doctor_id']);
//            $vRowData[] = $ACase['last_name'].' '.$ACase['first_name'].' '.$ACase['patr_name'];
            $vRowData[] = FormatNameEx($ACase);
            $vRowData[] = FormatBornDateAndAge($ACase['create_time'], $ACase['born_date']);
            $vRowData[] = FormatSex($ACase['is_male']);
            $vRowData[] = FormatAddresses(
            FormatAddress($ACase['addr_reg_street'],  $ACase['addr_reg_num'],  $ACase['addr_reg_subnum'],  $ACase['addr_reg_apartment']),
            FormatAddress($ACase['addr_phys_street'], $ACase['addr_phys_num'], $ACase['addr_phys_subnum'], $ACase['addr_phys_apartment']));
            $vRowData[] = $ACase['phone'];
            $vRowData[] = $ACase['accident'];
            $vRowData[] = Date2Readable($ACase['accident_datetime']);
            $vRowData[] = $ACase['diagnosis'];
            $vRowData[] = $ACase['message_number'];
            foreach($vRowData as &$v)
            {
                $v = iconv('utf-8', 'cp1251', $v);
            }
            $this->OutTableRow($vHeight, $vRowData);
        }



        function OutTableRow($AHeight, $ARowData, $AAlign='')
        {
//                            1   2    3   4   5   6   7   8   9  10   11 12
            $vCols   = array(12,  22, 25, 30, 22, 10, 35, 20, 30, 22, 35, 20);
            $vAligns = array('R','L','L','L','L','C','L','L','L','L','L','L');

/*
1) № И.Б.
2) Дата и время обращения
3) Врач
4) Ф.И.О. пострадавшего
5) Дата рождения (полных лет)
6) Пол
7) Aдрес
8) Телефон
9) Происшествие
10) Дата и время происшествия
11) Диагноз
12)Телефонограмма
*/
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

    $vDoc = new TPDF_SESes;
    $vDoc->Render($_GET);

    $vDoc->Output('animal_bites.pdf', 'I');
?>
