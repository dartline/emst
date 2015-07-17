<?php

#####################################################################
#
# EMST:Травмпункт
# (c) 2005,2006 Vista
#
# Печать направления на RG
#
#####################################################################


require_once './library/fpdfex.php';
require_once './library/cases_table.php';


class TPDF_RGDir extends FPDFEx
{
    function TPDF_RGDir()
    {
        $this->FPDFEx('RG direction');
        $this->Open();
    }

    function Header()
    {
        $vX = $this->GetX();
        $vY = $this->GetY();
        $vWidth  = $this->GetAreaWidth();

        $this->SetFont('arial_rus','',10);
        $vHeight = $this->FontSize*1.5;
        $this->Cell($vWidth, $vHeight, 'стр.'.$this->PageNo(), '', 0, 'R');
        $this->Ln($vHeight);
/*
        $this->SetXY($vX, $vY);
        $this->SetFont('arial_rus','',40);
        $vHeight = $this->FontSize*1.5;
        $this->Cell($vWidth, $vHeight, $this->DocTitle, 'B', 0, 'L');
        $this->Ln($vHeight);
*/
    }

    function Footer()
    {
        $vWidth  = $this->GetAreaWidth();

        //Go to 2.5 cm from bottom
        $this->SetY(-25);
        $this->SetFont('arial_rus','',40);
        $vHeight = $this->FontSize*1.5;
        $this->Cell($vWidth, $vHeight, $this->DocTitle, 'T', 0, 'L');
        $this->Ln($vHeight);
    }



    function Render($AID)
    {
        $vData = $this->LoadData($AID);
//        $this->DocTitle = Trim('№ '.$vData['case_id'].' / '.$vData['case']['last_name'].' '.$vData['case']['first_name'][0].'.'.$vData['case']['patr_name'][0].'.');
//        $this->DocTitle = Trim($vData['case_id'].' / '.FormatShortName($vData['case']['last_name'],$vData['case']['first_name'],$vData['case']['patr_name']));
        $this->DocTitle = Trim($vData['case_id'].' / '.FormatShortNameEx($vData['case']));
        $this->SetMargins(10,10,5);
        $this->SetAutoPageBreak(true, 30);
        $this->FirstPage($vData);
    }


    function Steal(&$ACase, $AField)
    {
        $vVal = @$ACase[$AField];

        // hack :(
        $vTmp = @$_SESSION['_docAccept_container'];

        if ( !empty($vTmp) )
        {
            $vDates = $vTmp['defaults']['surgeries'];
            if (  !empty($vDates) && is_array($vDates) )
              $vDatesCount = count($vDates);
            if ( $vDatesCount > 0 )
            {
               $vVal = @$vTmp['defaults']['Date0'][$AField];
               $vTmp =& $vTmp['values']['Date0'];
               if ( is_array($vTmp) && array_key_exists($AField, $vTmp) )
               {
                  $vVal = $vTmp[$AField];
                  if ( is_array($vVal) )
                  {
                       $vVal = DateTimeValueToStr($vVal);
                  }
               }
            }
        }
        // hack end
        $ACase[$AField] = $vVal;
    }

    function LoadData($AID)
    {
        $vDB = GetDB();
        $vRGDir = false;
        if ( !empty($AID) )
          $vRGDir =& $vDB->GetById('emst_rg', $AID);
        if ( !is_array($vRGDir) )
          $vRGDir = array();

        $vCaseID = @$vRGDir['case_id'];
        if ( !empty($vCaseID) )
          $vCase =& $vDB->GetById('emst_cases', $vCaseID);
        if ( !is_array($vCase) )
          $vCase = array();
        $vRGDir['case'] =& $vCase;

        $this->Steal($vCase, 'diagnosis');
        $this->Steal($vCase, 'accident');
        $this->Steal($vCase, 'accident_datetime');
        return $vRGDir;
    }


    function FirstPage($AData)
    {
        $vBranchInfo = GetBranchInfo();

        $this->AddPage();

        $this->SetFont('arial_rus','',10);
        $vWidth  = $this->GetAreaWidth();
        $vHeight = $this->FontSize*1.5;

        $vX = $this->GetX();
        $vY = $this->GetY();

        $this->SetXY($vX+0, $vY);
//        $this->Cell($vWidth, $vHeight, 'СПб ГУЗ ГП №51 Травматологическое отделение', 'B', 0, 'L');
        $this->Cell($vWidth, $vHeight, iconv("utf8","windows-1251",@$vBranchInfo['name']), 'B', 0, 'L');
        $this->Ln($vHeight);

        $this->SetFont('arial_rus','',14);
        $vHeight = $this->FontSize*1.5;
        $this->Cell($vWidth, $vHeight, 'Направление на рентгенологическое исследование');
        $this->Ln($vHeight);

        $this->SetFont('arial_rus','',10);
        $vHeight = $this->FontSize*1.5;
/*
        $vTitles = array('Дата', 'История болезни №', 'Фамилия Имя Отчество', 'Дата рождения', 'Объективный статус', 'Область исследования', 'Описание');
        $vTitleWidth = 0;
        foreach( $vTitles as $vTitle )
        {
            $vTitleWidth = max($vTitleWidth, $this->GetStringWidth($vTitle)+3);
        }

        $this->Notes($vTitles[0], $vTitleWidth, $vWidth,  1, Date2ReadableLong($AData['date']));
        $this->Notes($vTitles[1], $vTitleWidth, $vWidth,  1, $AData['case_id']);
        $this->Notes($vTitles[2], $vTitleWidth, $vWidth,  1, $AData['case']['last_name'].' '.$AData['case']['first_name'].' '.$AData['case']['patr_name']);
        $this->Notes($vTitles[3], $vTitleWidth, $vWidth,  1, Date2ReadableLong($AData['case']['born_date']));
        $this->Notes($vTitles[4], $vTitleWidth, $vWidth,  3, $AData['objective']);
        $this->Notes($vTitles[5], $vTitleWidth, $vWidth,  1, $AData['area']);
*/

        $vCase =& $AData['case'];
        $vBlock = array(
            array( 'title'=>'Дата',                'text'=>iconv("utf8","windows-1251",Date2ReadableLong($AData['date']))),
            array( 'title'=>'История болезни №',   'text'=>$AData['case_id']),
            array( 'title'=>'Фамилия Имя Отчество','text'=>iconv("utf8","windows-1251",$vCase['last_name']).' '.iconv("utf8","windows-1251",$vCase['first_name']).' '.iconv("utf8","windows-1251",$vCase['patr_name'])),
            array( 'title'=>'Дата рождения',        'text'=>iconv("utf8","windows-1251",FormatBornDateAndAgeLong($AData['date'], @$vCase['born_date']))),
            array( 'title'=>'Пол',                  'text'=>(@$vCase['is_male'])?'мужской':'женский'),
            array( 'title'=>'Адрес регистрации',    'text'=>iconv("utf8","windows-1251",FormatAddress(@$vCase['addr_reg_street'], @$vCase['addr_reg_num'], @$vCase['addr_reg_subnum'], @$vCase['addr_reg_apartment']) )),
            array( 'title'=>'Адрес проживания',     'text'=>iconv("utf8","windows-1251",FormatAddress(@$vCase['addr_phys_street'], @$vCase['addr_phys_num'], @$vCase['addr_phys_subnum'], @$vCase['addr_phys_apartment']) )),
            array( 'title'=>'Документ',             'text'=>iconv("utf8","windows-1251",FormatDocument(@$vCase['doc_type_id'], @$vCase['doc_series'], @$vCase['doc_number']) )),
            array( 'title'=>'Телефон(ы)',           'text'=>@$vCase['phone']),
            array( 'title'=>'Категория',            'text'=>iconv("utf8","windows-1251",FormatCategory(@$vCase['employment_category_id']))),
            array( 'title'=>'Место работы',         'text'=>iconv("utf8","windows-1251",@$vCase['employment_place'])),
            array( 'title'=>'Профессия',            'text'=>iconv("utf8","windows-1251",@$vCase['profession'])),
            array( 'title'=>'Полис',                'text'=>iconv("utf8","windows-1251",FormatPolis(@$vCase['insurance_company_id'], @$vCase['polis_series'], @$vCase['polis_number']))),
            array( 'title'=>'что произошло',        'text'=>iconv("utf8","windows-1251",@$vCase['accident'])),
            array( 'title'=>'дата и время',         'text'=>iconv("utf8","windows-1251",Date2ReadableLong(@$vCase['accident_datetime']))),

            array( 'title'=>'Диагноз предварительный','text'=>iconv("utf8","windows-1251",$AData['diagnosis']), 'rows'=>2),
//            array( 'title'=>'Код диагноза по МКБ',  'text'=>@$AData['diagnosis_mkb']),
//            array( 'title'=>'Тип травмы',           'text'=>FormatTraumaType(@$vCase['trauma_type_id'])),
//            array( 'title'=>'Доп. сведения',        'text'=>@$vCase['notes']),
//            array( 'title'=>'Объективный статус',   'text'=>$AData['objective'], 'rows'=>3),
            array( 'title'=>'Область исследования', 'text'=>iconv("utf8","windows-1251",$AData['area']), 'rows'=>2),
            array( 'title'=>'Направил',             'text'=>iconv("utf8","windows-1251",FormatUserName($AData['user_id']))),
//            array( 'title'=>'',                     'text'=>'', 'rows'=>1),
            array( 'title'=>'Описание',             'text'=>iconv("utf8","windows-1251",$AData['description']), 'rows'=>1),
        );
        $this->BlockNotes($vBlock, $vWidth);
        $this->Ln($vHeight*5);
        $vBlock = array(
            array( 'title'=>'Диагноз заключительный','text'=>'', 'rows'=>2),
        );
        $this->BlockNotes($vBlock, $vWidth);
    }

}


$vID = @$_GET['id'];
$vDoc = new TPDF_RGDir;
$vDoc->Render($vID);

header('Accept-Ranges: bytes');
$vDoc->Output('RG.pdf', 'I');

?>
