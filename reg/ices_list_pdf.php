<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
require_once 'library/fpdfex.php';
require_once 'library/cases_table.php';

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

                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251', 'АДМИНИСТРАЦИЯ МОСКОВСКОВСКОГО РАЙОНА САНКТ-ПЕТЕРБУРГА'), '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','ОТДЕЛ ЗДРАВООХРАНЕНИЯ'), '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight,  iconv('utf-8', 'cp1251','Санкт-Петербургское государственное учреждение здравоохранения'), '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight,  iconv('utf-8', 'cp1251','"Городская  поликлиника № 51"'), '', 0, 'C');
                $this->Ln($vHeight);
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight,  iconv('utf-8', 'cp1251','Сведения'), '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight,  iconv('utf-8', 'cp1251','о травматизме граждан'), '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight,  iconv('utf-8', 'cp1251','вследствие гололеда, падания наледей и сосулей с крыш зданий'), '', 0, 'C');
                $this->Ln($vHeight);
                $this->Cell($vWidth, $vHeight,  iconv('utf-8', 'cp1251','с '.$this->BegDate.' г. по '.$this->EndDate.' г.'), '', 0, 'C');
                $this->Ln($vHeight*2);

                $vRowData= array( iconv('utf-8', 'cp1251','№'),
                                  iconv('utf-8', 'cp1251',"ФИО постр.\nгод рожд.\n(адрес прож.,\nконт.тел.)"),
                                  iconv('utf-8', 'cp1251',"дата,\nвремя\nполуч.\nтравмы"),
                                  iconv('utf-8', 'cp1251',"точный адрес\nполуч.травмы\n(c указ.конкр.\nместа)"),
                                  iconv('utf-8', 'cp1251',"название обсл.\nорганизации,\nна терр.кот.\nполуч. травма"),
                                  iconv('utf-8', 'cp1251',"№ участка,\nФИО, должн.\nлица, ответст.за\nданн.террит."),
                                  iconv('utf-8', 'cp1251',"госпит.в\n(назв. мед.\nучрежд.,\nдиагноз)"),
                                  );
                $this->OutTableRow($vHeight, $vRowData, 'L');
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
                $this->DrawLine($vDB, ++$vNo, $vRecord);
            }

            $this->SetFont('arial_rus','',1);
            $this->ExactCell(1, 'Вечная слава Российской Прокуратуре -- самому законному сливальщику государственных денег!');
        }



        function DrawLine($ADB, $AIndex, $ACase)
        {
            $vHeight = $this->FontSize*1.5;
            $vBornDate = explode('-',$ACase['born_date']);
            $vAccDate  = explode(' ', $ACase['accident_datetime']);
            $vAccDate[0] = Date2Readable($vAccDate[0]);
            $vAccDate[1] = explode(':', $vAccDate[1]);
            $vAccDate[1] = $vAccDate[1][0].':'.$vAccDate[1][1];
            $vAccDate = implode(', ', $vAccDate);

            $vUnknown  = 'нет сведений';

            $vSurgeries = $ADB->Select('emst_surgeries', 
                                       'diagnosis, clinical_outcome_id, clinical_outcome_notes',
                                       $ADB->CondEqual('case_id', $ACase['id']),
                                       'date, id',
                                       1);
            if ( ($vFirstSurgery = $vSurgeries->Fetch()) === false )
                $vFirstSurgery = array();                 



            $vRowData = array();

            $vRowData[] = $AIndex;
//            $vRowData[] = FormatName($ACase['last_name'], $ACase['first_name'],$ACase['patr_name'])
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
//            $vRowData[] = $ACase['diagnosis'];
            $vColDataGosp = @FormatClinicalOutcome($vFirstSurgery['clinical_outcome_id'], $vFirstSurgery['clinical_outcome_notes']);
            $vColDataDiag = @$vFirstSurgery['diagnosis'];
            if ( !empty($vColDataGosp) && !empty($vColDataDiag) )
              $vColData = $vColDataGosp.', '.$vColDataDiag;
            else
              $vColData = $vColDataGosp.$vColDataDiag;
            $vRowData[] = $vColData;
            foreach($vRowData as &$v)
            {
                $v = iconv('utf-8', 'cp1251', $v);
            }
            $this->OutTableRow($vHeight, $vRowData);
        }



        function OutTableRow($AHeight, $ARowData, $AAlign='')
        {
            $vCols   = array(10,  35, 25, 30, 30, 30, 30);
            $vAligns = array('R','L','L','L','L','L','L');
            $this->OutputTableRow($vCols, $AHeight, $ARowData, empty($AAlign)? $vAligns:$AAlign);
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
