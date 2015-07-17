<?php

#####################################################################
#
# Травмпункт. (c) 2006 Vista
#
#####################################################################

require_once 'library/fpdfex.php';
require_once 'library/cases_table.php';


function ConstructSurgeriesQuery(&$ADB, $AParams)
{
    $vFilter = array();

    $vTable = 'emst_surgeries '.
              '  LEFT JOIN emst_cases ON emst_surgeries.case_id = emst_cases.id'.
              '  LEFT JOIN rb_employment_categories ON rb_employment_categories.id = emst_cases.employment_category_id'.
              '  LEFT JOIN rb_clinical_outcomes     ON emst_surgeries.clinical_outcome_id = rb_clinical_outcomes.id';

    $vFilter[] = '(emst_surgeries.disability=2 OR '.
                 '  (emst_surgeries.disability=0 AND rb_employment_categories.need_ill_doc!=0))';
    $vFilter[] = 'emst_surgeries.ill_refused=0';
    $vFilter[] = 'ill_sertificat=0';
    $vFilter[] = '(emst_surgeries.clinical_outcome_id IS NULL OR rb_clinical_outcomes.can_skip_ill_doc_on_disability=0)';
    $vFilter[] = 'emst_cases.disability_from_date = DATE(emst_surgeries.date)';



    if ( array_key_exists('beg_date', $AParams) && IsValidDate($AParams['beg_date']) )
        $vFilter[] = $ADB->CondGE('date', $AParams['beg_date']);
    if ( array_key_exists('end_date', $AParams) && IsValidDate($AParams['end_date']) )
        $vFilter[] = $ADB->CondLT('date', DateAddDay($AParams['end_date']));

    $vFilter = implode(' AND ', $vFilter);

    $vOrder = 'emst_surgeries.date, emst_surgeries.id';
    return array($vTable, $vFilter, $vOrder);
}



class TPDF_Report extends FPDFEx
{
    function TPDF_Report()
    {
        $this->FPDFEx('Stats', 'L');
        $this->Open();
        $this->SetMargins(10,10,10);
    }

    function Header()
    {
        $vBranchInfo = GetBranchInfo();
        $this->SetFont('arial_rus','',9);
        $vHeight = $this->FontSize*1.5;
        $vWidth  = $this->GetAreaWidth();
        $vX = $this->GetX();
        $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251',@$vBranchInfo['name']), '', 0, 'L');
        $this->SetX($vX);
        $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','стр. ').$this->PageNo(), '', 0, 'R');

        $this->Ln($vHeight);


        if ( $this->PageNo() > 1 )
        {
            $this->OutColNumbers();
        }
    }

    function OutColNumbers()
    {
        $this->SetFont('arial_rus','',7);
        $vHeight = $this->FontSize*1.5;
        $vRowData = array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15');

        $this->OutTableRow($vHeight, $vRowData, 'C');
        $this->SetFont('arial_rus','',9);
    }



    function Render($AParams)
    {
        global $RowDescr;


        $this->SetFont('arial_rus','',9);
        $vHeight = $this->FontSize*1.5;
        $vWidth  = $this->GetAreaWidth();


        $vDB = GetDB();
        $vBegDate = $AParams['beg_date'];
        $vEndDate = $AParams['end_date'];

        list($vTable, $vFilter, $vOrder) = ConstructSurgeriesQuery($vDB, $AParams);

        $vSurgeries = $vDB->Select($vTable,
                                   'emst_surgeries.case_id, emst_surgeries.date, emst_surgeries.user_id,'.
                                   'emst_surgeries.diagnosis, emst_surgeries.diagnosis_mkb,'.
                                   'emst_surgeries.ill_doc, '.
                                   'emst_cases.first_name, emst_cases.last_name, emst_cases.patr_name, emst_cases.born_date, emst_cases.is_male,'.
                                   'emst_cases.addr_reg_street, emst_cases.addr_reg_num, emst_cases.addr_reg_subnum, emst_cases.addr_reg_apartment, '.
                                   'emst_cases.employment_place, emst_cases.profession',
                                   $vFilter, 
                                   $vOrder);
        foreach($vSurgeries as &$v2)
        {
            $v2 = iconv('utf-8', 'cp1251', $v2);
        }
        $this->AddPage();

        $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','КНИГА'), '', 0, 'C');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','учета выборочного контроля'), '', 0, 'C');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','за выданными листками нетрудоспособности'), '', 0, 'C');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','с ').iconv('utf-8', 'cp1251',Date2ReadableLong($vBegDate)).iconv('utf-8', 'cp1251',' г. по ').iconv('utf-8', 'cp1251',Date2ReadableLong($vEndDate)).iconv('utf-8', 'cp1251',' г.'), '', 0, 'C');
        $this->Ln($vHeight*2);

        $vCols = array('№',
                       'дата проверки',
                       'врач',
                       "ФИО,\nдата рождения,\nадрес",
                       'место работы',
                       'диагноз',
                       "Л/Н",
                       'д/к',
                       "допо-\nлнено к обсле-\nдованию",
                       "допо-\nлнено к лече-\nнию",
                       "допо-\nлнено к диаг-\nнозу",
                       "Л/Н выдан необос-\nнованно",
                       "нару-\nшение инстру-\nкций",
                       "дефек-\nты в докумен-\nтации",
                       'подписи врачей');
        foreach($vCols as &$v)
        {
            $v = iconv('utf-8', 'cp1251', $v);
        }
        $this->OutTableRow($vHeight, $vCols);
        $this->OutColNumbers();


        while( $vRecord = $vSurgeries->Fetch() )
        {
            $vRowData = array(
                $vRecord['case_id'],                    // № п.п
                Date2Readable($vRecord['date']),        // дата проверки
                FormatUserName($vRecord['user_id']),    // врач
                FormatNameEx($vRecord)."\n".            // фио, дата рождения, адрес
                Date2Readable($vRecord['born_date'])."\n".
                FormatAddress($vRecord['addr_reg_street'],
                              $vRecord['addr_reg_num'], 
                              $vRecord['addr_reg_subnum'], 
                              $vRecord['addr_reg_apartment']),
                $vRecord['employment_place']."\n". $vRecord['profession'], // место работы
                $vRecord['diagnosis'],                  // диагноз
                $vRecord['ill_doc'],                    // бл.
                1,                                      // день контроля
                '',                                     // обследование
                '',                                     // лечение
                '',                                     // диагноз
                '',                                     // б/л выдан необоснованно
                '',                                     // нарушение инструкций
                '',                                     // дефекты в документации
                '');                                    // подписи врачей
            foreach($vRowData as &$vq)
            {
                $vq = iconv('utf-8', 'cp1251', $vq);
            }
            $this->OutTableRow($vHeight, $vRowData);
        }
    }

    function OutTableRow($AHeight, $ARowData, $AAlign='')
    {
// 1. № п.п
// 2. дата проверки
// 3. врач
// 4. фио, дата рождения, адрес
// 5. место работы
// 6. диагноз
// 7. бл.
// 8. день контроля
// 9. обследование
// 10. лечение
// 11. диагноз
// 12. б/л выдан необоснованно
// 13. нарушение инструкций
// 14. дефекты в документации
// 15. подписи врачей
        //                     1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12, 13, 14, 15
            $vCols   = array( 10, 15, 20, 35, 30, 35, 20,  7, 15, 15, 15, 15, 15, 15, 15);
            $vAligns = array('R','L','L','L','L','L','L','R','L','L','L','L','L','L','L');
            $this->OutputTableRow($vCols, $AHeight, $ARowData, empty($AAlign)? $vAligns:$AAlign);
    }
}

// =======================================================================

if ( !array_key_exists('beg_date', $_GET) )
  $_GET['beg_date'] = date('Y-m-d');
if ( !array_key_exists('end_date', $_GET) )
  $_GET['end_date'] = date('Y-m-d');

$vDoc = new TPDF_Report;
$vDoc->Render($_GET);

header('Accept-Ranges: bytes');
$vDoc->Output('illdocs_check.pdf', 'I');


?>