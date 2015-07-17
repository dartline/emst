<?php

#####################################################################
#
# EMST:Травмпункт
# (c) 2005,2006 Vista
#
# Печать выписного эпикриза
#
#####################################################################


require_once './library/fpdfex.php';
require_once './library/cases_table.php';


class TPDF_Epicrisis extends FPDFEx
{
    function TPDF_Epicrisis()
    {
        $this->FPDFEx('Out Epicrisis');
        $this->Open();
    }


    function Render(&$AEpicrisis)
    {
        $vDB = GetDB();
        $this->SetMargins(20,20,20);
        $this->SetAutoPageBreak(true, 30);

        $vBranchInfo = GetBranchInfo();

        $this->AddPage();

        $this->SetFont('arial_rus','',10);
        $vWidth  = $this->GetAreaWidth();
        $vHeight = $this->FontSize*1.5;

        $vX = $this->GetX();
        $vY = $this->GetY();
        $vPos = $vWidth*3/4;
        $vSmallWidth = $vWidth-$vPos;

        $vX = $this->GetX();
        $vY = $this->GetY();

        $this->SetXY($vX, $vY);
        $this->Cell($vPos, $this->FontSize, 'Министерство здравоохранения', '', 0, 'L');
        $this->Ln($vHeight);
        $this->Cell($vPos, $this->FontSize, 'и социального развития', '', 0, 'L');
        $this->Ln($vHeight);
        $this->Cell($vPos, $this->FontSize, 'Российской Федерации', '', 0, 'L');
        $this->Ln($vHeight);
        $this->Cell($vPos, $this->FontSize, iconv("utf8","windows-1251",@$vBranchInfo['name']), '', 0, 'L');
        $this->Ln($vHeight);
        $this->ExactCell($this->FontSize, 'Код ОГРН  ');
        $this->BoxedText($vPos, $this->FontSize, iconv("utf8","windows-1251",$vBranchInfo['OGRN']));
        $this->Ln($vHeight);


        $this->SetXY($vPos, $vY);
        $this->Cell($vSmallWidth, $this->FontSize, 'Медицинская документация', '', 0, 'L');
        $this->Ln($vHeight);
        $this->SetX($vPos);
        $this->Cell($vSmallWidth, $this->FontSize, 'Форма № 027/у' , '', 0, 'L');
        $this->Ln($vHeight);
        $this->SetX($vPos);
        $this->Cell($vSmallWidth, $this->FontSize, 'Утв. Минздравом СССР' , '', 0, 'L');
        $this->Ln($vHeight);
        $this->SetX($vPos);
        $this->Cell($vSmallWidth, $this->FontSize, '04.10.80 № 1030' , '', 0, 'L');
        $this->Ln($vHeight*4);

        $this->SetFont('arial_rus','',14);
        $vHeight = $this->FontSize*1.5;
        $this->Cell($vWidth, $vHeight, 'ВЫПИСКА', '', 0, 'C');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, 'из медицинской карты амбулаторного больного № '.$AEpicrisis['case_id'], '', 0, 'C');
        $this->Ln($vHeight*2);

        $this->SetFont('arial_rus','',10);
        $vHeight = $this->FontSize*1.5;
        $vBlock = array();
//            $vBlock[] = array('title'=>'В',                         'text'=>@$AEpicrisis['target']);
//        $vBlock[] = array('title'=>'Фамилия, имя, отчество', 'text'=>FormatName(@$AEpicrisis['last_name'], @$AEpicrisis['first_name'], @$AEpicrisis['patr_name']));
        $vBlock[] = array('title'=>'Фамилия, имя, отчество', 'text'=>iconv("utf8","windows-1251",FormatNameEx($AEpicrisis)));
        $vBlock[] = array('title'=>'Дата рождения',          'text'=>iconv("utf8","windows-1251",FormatBornDateAndAgeLong($vDB->ConvertToDate(time()), @$AEpicrisis['born_date'])));
        $vBlock[] = array('title'=>'Пол',                    'text'=>(@$AEpicrisis['is_male'])?'мужской':'женский');
        $vBlock[] = array('title'=>'Домашний адрес',         'text' =>iconv("utf8","windows-1251",@FormatAddress($AEpicrisis['addr_reg_street'], $AEpicrisis['addr_reg_num'], $AEpicrisis['addr_reg_subnum'], $AEpicrisis['addr_reg_apartment'])));
        $vBlock[] = array('title'=>'Место работы и род занятий', 'text' =>iconv("utf8","windows-1251",FormatProfession(@$AEpicrisis['employment_place'], @$AEpicrisis['profession'])));
        $vBlock[] = array('title'=>'Полис',                  'text'=>iconv("utf8","windows-1251",FormatPolis(@$AEpicrisis['insurance_company_id'], @$AEpicrisis['polis_series'], @$AEpicrisis['polis_number'])));
        $vBlock[] = array('title'=>'Дата обращения',         'text'=>iconv("utf8","windows-1251",Date2ReadableLong(@$AEpicrisis['create_time'])));
        $vBlock[] = array('title'=>'Тип травмы',             'text'=>iconv("utf8","windows-1251",FormatTraumaType(@$AEpicrisis['trauma_type_id'])));
        $vBlock[] = array('title'=>'Со слов пострадавшего',  'text'=>iconv("utf8","windows-1251",@$AEpicrisis['accident']));
        $vBlock[] = array('title'=>'Дата и время происшествия', 'text'=>iconv("utf8","windows-1251",Date2ReadableLong(@$AEpicrisis['accident_datetime'])));
//            $vBlock[] = array('title'=>'Жалобы',                 'text'=>@$AEpicrisis['complaints']);
        $vBlock[] = array('title'=>'Диагноз',                'text'=>iconv("utf8","windows-1251",@$AEpicrisis['diagnosis']));
        $vBlock[] = array('title'=>'Лечение',                'text'=>iconv("utf8","windows-1251",@$AEpicrisis['cure']));
        if ( !empty($AEpicrisis['dynamic_id']) )
            $vBlock[] = array('title'=>'Динамика',               'text'=>iconv("utf8","windows-1251",FormatDynamic(@$AEpicrisis['dynamic_id'])));
        if ( !empty($AEpicrisis['clinical_outcome_id']) )
            $vBlock[] = array('title'=>'Исход', 'text'=>iconv("utf8","windows-1251",FormatClinicalOutcome($AEpicrisis['clinical_outcome_id'])));
        else
            $vBlock[] = array('title'=>'Исход', 'text'=>'В настоящее время продолжает лечение');
        $vBlock[] = array('title'=>'Лечебные и трудовые рекомендации', 'text' =>iconv("utf8","windows-1251",@$AEpicrisis['recomendation']));
        $this->BlockNotes($vBlock, $vWidth);
        $this->Ln($vHeight*2);

        $vBlock = array();
        $vBlock[] = array( 'title'=>'Врач',                   'text'=>iconv("utf8","windows-1251",FormatUserName(@$AEpicrisis['doctor_id'])));
        $vBlock[] = array( 'title'=>'Дата',                   'text'=>iconv("utf8","windows-1251",Date2ReadableLong($vDB->ConvertToDate(time()))));
        $this->BlockNotes($vBlock, 70);
        $this->Ln($vHeight*2);
        $this->ExactCell($vHeight, ' М.П.');
    }
}


$vDoc = new TPDF_Epicrisis;
$vParams = @$_SESSION['hold']['out_epicrisis'];

if ( empty($vParams) )
    $vParams = array();

$vDoc->Render($vParams);


header('Accept-Ranges: bytes');
$vDoc->Output('Epicrisis.pdf', 'I');
?>
