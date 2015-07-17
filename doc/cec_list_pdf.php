<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
require_once 'library/fpdfex.php';
require_once 'library/cases_table.php';


    function ConstructCaseQuery(&$ADB, $AParams)
    {
        $vFilter = array();

        $vTable = 'emst_surgeries '.
                  ' JOIN emst_cases ON emst_surgeries.case_id = emst_cases.id';

        $vFilter[] = $ADB->CondEqual('emst_surgeries`.`is_cec', 1);
        if ( array_key_exists('beg_date', $AParams) )
           $vFilter[] = $ADB->CondGE('emst_surgeries`.`date', $AParams['beg_date']);
        if ( array_key_exists('end_date', $AParams) )
           $vFilter[] = $ADB->CondLT('emst_surgeries`.`date', DateAddDay($AParams['end_date']));

        $vFilter = implode(' AND ', $vFilter);
        $vOrder = 'emst_surgeries.cec_number, emst_surgeries.date, emst_surgeries.case_id';
        return array($vTable, $vFilter, $vOrder);
    }


    function GetNumCECes($ACaseID, $ACECDate, $ACECID)
    {
        $vDB = GetDB();
//  SELECT COUNT(*)
//  FROM `emst_surgeries`
//  WHERE
//      `is_cec` AND
//      `case_id`='$ACaseID' AND
//      `date` < '$ACECDate'+1

        $vFilter = $vDB->CondAnd(
            $vDB->CondEqual('is_cec', 1),
            $vDB->CondEqual('case_id', $ACaseID),
            $vDB->CondLT('date', DateAddDay($ACECDate))
        );
        return $vDB->CountRows('emst_surgeries', 'id', $vFilter);
    }


    class TPDF_CECes extends FPDFEx
    {
        function TPDF_CECes()
        {
            $this->FPDFEx('CECes list', 'L');
            $this->Open();
            $this->SetMargins(5,5,5);
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
                $this->Cell($vWidth, $vHeight,iconv('utf-8', 'cp1251', 'Журнал ВК'), '', 0, 'C');
                $this->SetX($vX);
                $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','стр. ').$this->PageNo(), '', 0, 'R');
                $this->Ln($vHeight);

                $this->Cell($vWidth, $vHeight,iconv('utf-8', 'cp1251', 'с ').$this->BegDate.iconv('utf-8', 'cp1251',' г. по ').$this->EndDate.iconv('utf-8', 'cp1251',' г.'), '', 0, 'C');
                $this->Ln($vHeight*2);

            }
            else
            {
                $this->Cell($vWidth, $vHeight,iconv('utf-8', 'cp1251', 'Журнал ВК с ').$this->BegDate.iconv('utf-8', 'cp1251',' г. по ').$this->EndDate.iconv('utf-8', 'cp1251',' г. / стр.').$this->PageNo(), '', 0, 'R');
                $this->Ln($vHeight*2);
            }

            $vRowData= array( iconv('utf-8', 'cp1251','№'),
                              iconv('utf-8', 'cp1251','Дата'),
                              iconv('utf-8', 'cp1251','Врач'),
                              iconv('utf-8', 'cp1251','Ф.И.О. больного, дата рождения'),
                              iconv('utf-8', 'cp1251','Адрес'),
                              iconv('utf-8', 'cp1251','Место работы, профессия'),
                              iconv('utf-8', 'cp1251','Диагноз'),
                              iconv('utf-8', 'cp1251','№ листка нетрудо- способности'),
                              iconv('utf-8', 'cp1251','Заключение комиссии'),
                              iconv('utf-8', 'cp1251','Подписи членов комиссии'));
            $this->OutTableRow($vHeight, $vRowData, 'C');
        }


        function Render($AParams)
        {
            $vDB = GetDB();
            list($vTable, $vFilter, $vOrder) = ConstructCaseQuery($vDB, $AParams);
            $this->BegDate =  iconv('utf-8', 'cp1251',Date2ReadableLong($AParams['beg_date']));
            $this->EndDate =  iconv('utf-8', 'cp1251',Date2ReadableLong($AParams['end_date']));
            $vRecords = $vDB->Select($vTable, 'emst_surgeries.*, emst_cases.first_name, emst_cases.last_name, emst_cases.patr_name, emst_cases.is_male, emst_cases.born_date, emst_cases.doc_type_id, emst_cases.doc_series, emst_cases.doc_number, emst_cases.polis_series, emst_cases.polis_number, emst_cases.addr_reg_street, emst_cases.addr_reg_num, emst_cases.addr_reg_subnum, emst_cases.addr_reg_apartment, emst_cases.phone, emst_cases.employment_category_id, emst_cases.employment_place, emst_cases.profession, emst_cases.disability_from_date', $vFilter, $vOrder);

            $this->AddPage();
            while( $vRecord = $vRecords->Fetch() )
            {
                $this->DrawLine($vRecord);
            }

        }



        function DrawLine($ACEC)
        {
            $vBranchInfo = GetBranchInfo();

            $vHeight = $this->FontSize*1.5;
            $vRowData = array();

            $vCECDate = ExtractWord($ACEC['date'],' ',0);
            $vCaseID = $ACEC['case_id'];
            $vNumCECesBefore = GetNumCECes($vCaseID, $vCECDate, $ACEC['id']);
            $vCECNumber = $ACEC['cec_number'];
            if ( $vCECNumber == 0 )
                $vCECNumber = '';
            $vRowData[] = $vCECNumber."\n(".($vNumCECesBefore+1).')';

            //   'Дата',
            $vRowData[] = Date2Readable($vCECDate);
            //   'Врач',
            $vRowData[] = FormatUserName($ACEC['user_id']);
            //   'Ф.И.О. больного' & 'Дата рождения'
//            $vRowData[] = FormatName($ACEC['last_name'], $ACEC['first_name'],$ACEC['patr_name'])
            $vRowData[] = FormatNameEx($ACEC)
                        . "\n"
                        . FormatBornDateAndAge($vCECDate, $ACEC['born_date'])
                        . "\nи.б. "
                        . $vCaseID;
//            $vRowData[] = FormatSex($ACase['is_male']);
            //   'Адрес',
            $vRowData[] = FormatAddress($ACEC['addr_reg_street'],  $ACEC['addr_reg_num'],  $ACEC['addr_reg_subnum'],  $ACEC['addr_reg_apartment']);
//            $vRowData[] = $ACase['phone'];
            //   'Место работы' & 'Профессия'
            $vTmpList = array();
            $vTmp = FormatWorkableAge($vCECDate, $ACEC['born_date'], $ACEC['is_male']);
            if ( !empty($vTmp) )
                $vTmpList[] = $vTmp;
            $vTmp = FormatCategory($ACEC['employment_category_id']);
            if ( !empty($vTmp) )
                $vTmpList[] = $vTmp;
            $vTmp = $ACEC['employment_place'];
            if ( !empty($vTmp) )
                $vTmpList[] = $vTmp;
            $vTmp = $ACEC['profession'];
            if ( !empty($vTmp) )
                $vTmpList[] = $vTmp;
            $vRowData[] = implode(",\n", $vTmpList);
            //   'Диагноз',
            $vRowData[] = $ACEC['diagnosis'];
            //   '№ листка нетрудоспособности',
            $vTmp = $ACEC['ill_doc'];
            if ( !empty($vTmp) )
            {
                $vTmp .= "\n".Date2Readable($ACEC['disability_from_date']);
                $vTmp .= "\n".Date2Readable($vCECDate);
                $vTmp .= "\n(".(DateDiff($vCECDate, $ACEC['disability_from_date'])+1).')';
            }
            $vRowData[] = $vTmp;
            //   'Заключение комиссии'
            $vRowData[] = "лечение продлено\nс "
                        . Date2Readable(DateAddDay($vCECDate))
                        . "\nпо "
                        . Date2Readable($ACEC['cec_cureup_date'])
                        . "\n("
                        . (DateDiff($ACEC['cec_cureup_date'], $vCECDate))
                        . ')';
            //   'Подписи членов комиссии'
            $vTmp = $ACEC['cec_members'];
            if ( empty($vTmp) )
              $vTmp = $vBranchInfo['cec_members'];
            $vRowData[] = $vTmp;
            foreach($vRowData as &$v)
            {
                $v = iconv('utf-8', 'cp1251', $v);
            }
            $this->OutTableRow($vHeight, $vRowData);
        }



        function OutTableRow($AHeight, &$ARowData, $AAlign='')
        {
            $vCols   = array(10,  22, 22, 30, 30, 40, 40, 22, 40, 30);
            $vAligns = array('R','L','L','L','L','L','L','L','L','L');

/*
1) '№',
2) 'Дата',
3) 'Врач',
4) 'Ф.И.О. больного' & 'Дата рождения',
5) 'Адрес',
6) 'Место работы' & 'Профессия'
7) 'Диагноз',
8) '№ листка нетрудоспособности',
9) 'Заключение комиссии'
10) 'Подписи членов комиссии');
*/
            $this->OutputTableRow($vCols, $AHeight, $ARowData, empty($AAlign)? $vAligns:$AAlign);
        }
    }

// =======================================================================

    if ( !array_key_exists('beg_date', $_GET) )
      $_GET['beg_date'] = date('Y-m-d');
    if ( !array_key_exists('end_date', $_GET) )
      $_GET['end_date'] = date('Y-m-d');

    $vDoc = new TPDF_CECes;
    $vDoc->Render($_GET);

//    while (ob_get_level())
//        ob_end_clean();

//    header('Accept-Ranges: bytes');
    $vDoc->Output('CECes.pdf', 'I');
?>
