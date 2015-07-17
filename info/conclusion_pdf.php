<?php

#####################################################################
#
# EMST:Травмпункт
# (c) 2005,2006 Vista
#
# Печать закличения о состоянии здоровья
#
#####################################################################


require_once './library/fpdfex.php';
require_once './library/cases_table.php';


class TPDF_Conclusion extends FPDFEx
{
    function TPDF_Conclusion()
    {
        $this->FPDFEx('Conclusion');
        $this->Open();
    }


    function Header()
    {
    }


    function Footer()
    {
    }


    function Render(&$AConclusion)
    {
        $vDB = GetDB();
        $vHeavitiesList = $vDB->GetRBList('rb_trauma_heavity','id', 'name', true);

        $this->SetMargins(20,20,20);
        $this->SetAutoPageBreak(true, 30);

        $vBranchInfo = GetBranchInfo();

        $this->AddPage();

        $this->SetFont('arial_rus','',10);
        $vWidth  = $this->GetAreaWidth();
        $vHeight = $this->FontSize*1.5;

        $vX = $this->GetX();
        $vY = $this->GetY();

        $this->SetXY($vX, $vY);
        $this->Cell($vWidth, $vHeight, iconv("utf8","windows-1251",@$vBranchInfo['name']), 'B', 0, 'L');
        $this->Ln($vHeight);

        $this->Cell($vWidth, $vHeight, 'Медицинская документация' , '', 0, 'R');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, 'Учетная форма № 315/у' , '', 0, 'R');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, 'УТВЕРЖДЕНА' , '', 0, 'R');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, 'Приказом Минздравсоцразвития России' , '', 0, 'R');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, 'От 15 апреля 2005 г. №275' , '', 0, 'R');
        $this->Ln($vHeight);
        $this->Ln($vHeight);

        $this->SetFont('arial_rus','',14);
        $vHeight = $this->FontSize*1.5;
        $this->Cell($vWidth, $vHeight, 'МЕДИЦИНСКОЕ ЗАКЛЮЧЕНИЕ', '', 0, 'C');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, 'о характере полученных повреждений здоровья в результате несчастного', '', 0, 'C');
        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, 'случая на производстве и степени их тяжести', '', 0, 'C');
        $this->Ln($vHeight);
        $this->Ln($vHeight);


        $this->SetFont('arial_rus','',10);
        $vHeight = $this->FontSize*1.5;

        $vBlock = array();
        $vBlock[] = array( 'title'=>'Выдано',                 'text'=>iconv("utf8","windows-1251",@$AConclusion['employment_place']));
        $vBlock[] = array( 'title'=>'О том, что пострадавший',
//                           'text' => FormatName(@$AConclusion['last_name'], @$AConclusion['first_name'], @$AConclusion['patr_name']) );
                           'text' => iconv("utf8","windows-1251",FormatNameEx($AConclusion)) );
        $vBlock[] = array( 'title'=>'дата рождения',          'text'=>iconv("utf8","windows-1251",Date2ReadableLong(@$AConclusion['born_date'])));
        $vBlock[] = array( 'title'=>'должность (профессия)',  'text'=>iconv("utf8","windows-1251",@$AConclusion['profession']));
        $vBlock[] = array( 'title'=>'поступил в',             'text'=>iconv("utf8","windows-1251",@$vBranchInfo['name']));
        $vBlock[] = array( 'title'=>'дата и время обращения', 'text'=>iconv("utf8","windows-1251",Date2ReadableLong(@$AConclusion['create_time'])));

        $vBlock[] = array( 'title'=>'Диагноз',                'text'=>iconv("utf8","windows-1251",@$AConclusion['diagnosis']));
        $vBlock[] = array( 'title'=>'Код диагноза по МКБ',    'text'=>iconv("utf8","windows-1251",@$AConclusion['diagnosis_mkb']));
        $this->BlockNotes($vBlock, $vWidth);
        $this->Cell($vWidth, $vHeight, 'Согласно схеме определения степени тяжести повреждения здоровья при несчастных случаях', '', 0, 'L');
        $this->Ln($vHeight);
        $vBlock = array();
        $vBlock[] = array( 'title'=>'на производстве указанное повреждение относится к категории', 'text'=>iconv("utf8","windows-1251",@$vHeavitiesList[$AConclusion['heavity']]));
        $this->BlockNotes($vBlock, $vWidth);
        $this->Ln($vHeight);

        $vBlock = array();
        $vBlock[] = array( 'title'=>'Врач',                   'text'=>iconv("utf8","windows-1251",FormatUserName(@$AConclusion['doctor_id'])));
        $vBlock[] = array( 'title'=>'Дата',                   'text'=>iconv("utf8","windows-1251",Date2ReadableLong($vDB->ConvertToDate(time()))));
        $this->BlockNotes($vBlock, $vWidth);
        $this->Ln($vHeight);
    }
}


//Trace($_GET);

$vDoc = new TPDF_Conclusion;
$vDoc->Render($_GET);

while (ob_get_level())
    ob_end_clean();

header('Accept-Ranges: bytes');
$vDoc->Output('Conclusion.pdf', 'I');
?>
