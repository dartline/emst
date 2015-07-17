<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
require_once 'library/fpdfex.php';
require_once 'library/cases_table.php';

/*
1) Дата направления
2) № И.Б.
3) Ф.И.О. пострадавшего
4) Дата рождения (полных лет)
//5) Пол
//6) Aдрес
7) Телефон
8) Область
//9) Диагноз
10) Врач
11) Описание
*/

    function ConstructQuery(&$ADB, $AParams)
    {
        $vFilter = array();

        $vTable = 'emst_rg '.
                  ' LEFT JOIN emst_cases ON emst_rg.case_id = emst_cases.id';

        if ( array_key_exists('case_id', $AParams) )
           $vFilter[] = $ADB->CondEqual('case_id', $AParams['case_id']);
        if ( array_key_exists('beg_date', $AParams) )
           $vFilter[] = $ADB->CondGE('date', $AParams['beg_date']);
        if ( array_key_exists('end_date', $AParams) )
           $vFilter[] = $ADB->CondLT('date', DateAddDay($AParams['end_date']));

        $vFilter = implode(' AND ', $vFilter);
        $vOrder = 'date, case_id';
        return array($vTable, $vFilter, $vOrder);
    }


    class TPDF_RGs extends FPDFEx
    {
        function TPDF_RGs()
        {
            $this->FPDFEx('Plasters Report', 'L');
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
                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','Журнал направлений на RG'), '', 0, 'C');
                $this->SetX($vX);
                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','стр. ').$this->PageNo(), '', 0, 'R');
                $this->Ln($vHeight);

                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','с ').$this->BegDate.iconv('utf-8', 'cp1251',' г. по ').$this->EndDate.iconv('utf-8', 'cp1251',' г.'), '', 0, 'C');
                $this->Ln($vHeight*2);

                $vRowData= array( iconv('utf-8', 'cp1251','Дата направления'),
                                  iconv('utf-8', 'cp1251','№ И.Б.'),
                                  iconv('utf-8', 'cp1251','Ф.И.О. пострадавшего'),
                                  iconv('utf-8', 'cp1251','Дата рождения (полных лет)'),
//                                  'Пол',
//                                  'Aдрес',
                                  iconv('utf-8', 'cp1251','Телефон'),
                                  iconv('utf-8', 'cp1251','Область'),
//                                  'Диагноз',
                                  iconv('utf-8', 'cp1251','Врач'),
                                  iconv('utf-8', 'cp1251','Описание'));
                $this->OutTableRow($vHeight, $vRowData, 'C');
            }
            else
            {
                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','Журнал направлений на RG с ').$this->BegDate.iconv('utf-8', 'cp1251',' г. по ').$this->EndDate.iconv('utf-8', 'cp1251',' г. / стр.').$this->PageNo(), '', 0, 'R');
                $this->Ln($vHeight*2);
            }

            $vRowData = array( '1', '2', '3', '4', '5', '6', '7');
            $this->OutTableRow($vHeight, $vRowData, 'C');
        }


        function Render($AParams)
        {
            $vDB = GetDB();
            list($vTable, $vFilter, $vOrder) = ConstructQuery($vDB, $AParams);
            $this->BegDate = iconv('utf-8', 'cp1251',Date2ReadableLong($AParams['beg_date']));
            $this->EndDate = iconv('utf-8', 'cp1251',Date2ReadableLong($AParams['end_date']));
            $vFields = 'emst_rg.*, emst_cases.last_name, emst_cases.first_name, emst_cases.patr_name, emst_cases.born_date, emst_cases.is_male,'.
                       'emst_cases.addr_reg_street,  emst_cases.addr_reg_num,  emst_cases.addr_reg_subnum,  emst_cases.addr_reg_apartment,'.
                       'emst_cases.addr_phys_street, emst_cases.addr_phys_num, emst_cases.addr_phys_subnum, emst_cases.addr_phys_apartment,'.
                       'emst_cases.phone';

            $vRecords = $vDB->Select($vTable, $vFields, $vFilter, $vOrder);

            $this->AddPage();
            while( $vRecord = $vRecords->Fetch() )
            {
                $this->DrawLine($vRecord);
            }
        }


        function DrawLine($ARG)
        {
//            $vWidth = $this->GetAreaWidth();
            $vHeight = $this->FontSize*1.5;
            $vRowData = array();

            // 1) Дата направления
            $vRowData[] = Date2Readable($ARG['date']);
            // 2) № И.Б.
            $vRowData[] = $ARG['case_id'];
            // 3) Ф.И.О. пострадавшего
//            $vRowData[] = FormatName($ARG['last_name'], $ARG['first_name'], $ARG['patr_name']);
            $vRowData[] = FormatNameEx($ARG);
            // 4) Дата рождения (полных лет)
            $vRowData[] = FormatBornDateAndAge($ARG['date'], $ARG['born_date']);
            // 5) Пол
//            $vRowData[] = FormatSex($ARG['is_male']);
            // 6) Aдрес
//            $vRowData[] = FormatAddresses( 
//                             FormatAddress($ARG['addr_reg_street'],  $ARG['addr_reg_num'],  $ARG['addr_reg_subnum'],  $ARG['addr_reg_apartment']),
//                             FormatAddress($ARG['addr_phys_street'], $ARG['addr_phys_num'], $ARG['addr_phys_subnum'], $ARG['addr_phys_apartment']));
            // 7) Телефон
            $vRowData[] = $ARG['phone'];
            // 8) Область
            $vRowData[] = $ARG['area'];
            // 9) Диагноз
//            $vRowData[] = $ARG['diagnosis'];
            // 10) Врач
            $vRowData[] = FormatUserName($ARG['user_id']);
            // 11) Описание
            $vRowData[] = $ARG['description'];
            foreach($vRowData as &$v)
            {
                $v = iconv('utf-8', 'cp1251', $v);
            }
            $this->OutTableRow($vHeight, $vRowData);
        }



        function OutTableRow($AHeight, $ARowData, $AAlign='')
        {
////                            1   2    3   4   5   6   7   8   9  10   11  12
//            $vCols   = array(25,  15, 30, 30, 25, 10, 35, 20, 30, 30, 30);
//            $vAligns = array('L','R','L','L','L','C','L','L','L','L','L');
//                            1   2    3   4   5   6   7   8   9  10   11  12
            $vCols   = array(25,  15, 30,  25, 20,30, 30, 100);
            $vAligns = array('L','R','L', 'C','L','L','L','L');

/*
1) Дата направления
2) № И.Б.
4) Ф.И.О. пострадавшего
5) Дата рождения (полных лет)
6) Пол
7) Aдрес
8) Телефон
9) Область
10) Диагноз
11) Врач
12) Описание
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

    $vDoc = new TPDF_RGs;
    $vDoc->Render($_GET);

//    while (ob_get_level())
//        ob_end_clean();

//    header('Accept-Ranges: bytes');
    $vDoc->Output('RGs.pdf', 'I');
?>
