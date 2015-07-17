<?php

#####################################################################
#
# EMST:Травмпункт
# (c) 2005,2006 Vista
#
# Печать направления
#
#####################################################################


require_once './library/fpdfex.php';
require_once './library/cases_table.php';


class TPDF_Direction extends FPDFEx
{
    function TPDF_Direction()
    {
        $this->FPDFEx('Direction');
        $this->Open();
    }

    function Header()
    {
    }

    function Footer()
    {
    }



    function Render(&$ADirection)
    {
        $vDB = GetDB();
        $vSubjectList = $vDB->GetRBList('rb_directions','id', 'name', true);

        $this->SetMargins(20,20,20);
        $this->SetAutoPageBreak(true, 30);

        $vBranchInfo = GetBranchInfo();

        $this->AddPage();

        $this->SetFont('arial_rus','',10);
        $vWidth  = $this->GetAreaWidth();
        $vHeight   = $this->FontSize;
        $vHeightLN = $this->FontSize*1.5;

        $vX = $this->GetX();
        $vY = $this->GetY();

        $this->SetXY($vX, $vY);
        $this->Cell($vWidth, $vHeight, 'Министерство здравоохранения', '', 0, 'L');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'и социального развития', '', 0, 'L');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'Российской Федерации', '', 0, 'L');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, iconv("utf8","windows-1251",@$vBranchInfo['name']), '', 0, 'L');
        $this->Ln($vHeightLN);
        $this->ExactCell($vHeight, 'Код ОГРН  ');
        $this->BoxedText($vWidth, $vHeight, $vBranchInfo['OGRN']);
        $this->Ln($vHeightLN);

        $this->SetXY($vX, $vY);

        $this->Cell($vWidth, $vHeight, 'Медицинская документация' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'форма № 057/у-04' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'УТВЕРЖДЕНА' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'Приказом Минздравсоцразвития России' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'От 22 ноября 2004 г. №255' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Ln($vHeightLN);

        $this->SetFont('arial_rus','',14);
        $vHeight   = $this->FontSize;
        $vHeightLN = $this->FontSize*1.5;
        $this->Cell($vWidth, $vHeight, 'НАПРАВЛЕНИЕ', '', 0, 'C');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, iconv("utf8","windows-1251",@$vSubjectList[$ADirection['direction_subject']]), '', 0, 'C');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, iconv("utf8","windows-1251",@$ADirection['direction_target']), 'B', 0, 'C');
        $this->Ln($vHeightLN);
        $this->Ln($vHeightLN);

        $this->SetFont('arial_rus','',10);
        $vHeight   = $this->FontSize;
        $vHeightLN = $this->FontSize*1.5;
        $this->ExactCell($vHeight, '1. Номер страхового полиса ОМС');
        $this->SetX(90);
//            $this->Ln($vHeight);
        $this->BoxedText($vWidth, $vHeight, iconv("utf8","windows-1251",@$ADirection['polis_series']).' '.@$ADirection['polis_number'], 24);
        $this->Ln($vHeightLN);
        $this->ExactCell($vHeight, '2. Код льготы');
        $this->SetX(90);
        $this->BoxedText($vWidth, $vHeight, '', 3);
        $this->Ln($vHeightLN);

        $vBlock = array();
        $vBlock[] = array( 'title'=>'3. Фамилия, Имя, Отчество',
//                           'text' => FormatName(@$ADirection['last_name'], @$ADirection['first_name'], @$ADirection['patr_name']) );
                           'text' => iconv("utf8","windows-1251",FormatNameEx($ADirection) ));
        $vBlock[] = array( 'title'=>'4. Дата рождения',          'text'=>iconv("utf8","windows-1251",Date2ReadableLong(@$ADirection['born_date'])) );
        $vBlock[] = array( 'title'=>'5. Адрес постоянного места жительства',
                           'text' =>iconv("utf8","windows-1251",@FormatAddress($ADirection['addr_reg_street']), iconv("utf8","windows-1251",$ADirection['addr_reg_num']), iconv("utf8","windows-1251",$ADirection['addr_reg_subnum']), iconv("utf8","windows-1251",$ADirection['addr_reg_apartment'])));
        $vBlock[] = array( 'title'=>'6. Место работы, должность',
                           'text'=>FormatProfession(iconv("utf8","windows-1251",@$ADirection['employment_place']), iconv("utf8","windows-1251",@$ADirection['profession'])));
        $vBlock[] = array( 'title'=>'7. Диагноз',                'text'=>iconv("utf8","windows-1251",@$ADirection['diagnosis']));
        $this->BlockNotes($vBlock, $vWidth);
        $this->Ln($vHeightLN);
        $this->ExactCell($vHeight, '8. Код диагноза по МКБ');
        $this->SetX(90);
        $this->BoxedText($vWidth, $vHeight, iconv("utf8","windows-1251",@$ADirection['diagnosis_mkb']), 5);
        $this->Ln($vHeightLN);

        $vBlock = array();
        $this->Ln($vHeightLN);
        $this->Ln($vHeightLN);
        $vBlock[] = array( 'title'=>'Врач',                   'text'=>iconv("utf8","windows-1251",FormatUserName(@$ADirection['doctor_id'])));
        $vBlock[] = array( 'title'=>'Дата',                   'text'=>iconv("utf8","windows-1251",Date2ReadableLong($vDB->ConvertToDate(time()))));
        $this->BlockNotes($vBlock, 70);
        $this->Ln($vHeightLN*2);
        $this->ExactCell($vHeight, ' М.П.');

    }
}


// Trace($_GET);
$vDoc = new TPDF_Direction;
$vDoc->Render($_GET);

//while (ob_get_level())
//    ob_end_clean();

header('Accept-Ranges: bytes');
$vDoc->Output('Direction.pdf', 'I');

?>
