<?php

#####################################################################
#
# EMST:Травмпункт
# (c) 2005,2006 Vista
#
# Печать направления на физиотерапию
#
#####################################################################


require_once 'library/fpdfex.php';
require_once 'library/cases_table.php';

    function _2w(&$Src)
    {
        return iconv( "utf-8", "windows-1251", $Src );
    }

    function _2u(&$Src)
    {
        return iconv( "windows-1251", "utf-8", $Src );
    }

class TPDF_Physiotherapy extends FPDFEx
{
    function TPDF_Physiotherapy()
    {
        $this->FPDFEx('Physiotherapy', 'L', 'A4');
        $this->SetAutoPageBreak(true, 10);
        $this->SetMargins(10,10,10);
        $this->Open();
    }


    function Render(&$AInfo)
    {
        $vDB = GetDB();

        $vBranchInfo = GetBranchInfo();

        $this->AddPage();

        $this->SetFont('arial_rus','',10);
        $vWidth  = $this->GetAreaWidth()/2-10;
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
//        $this->Cell($vWidth, $vHeight, @$vBranchInfo['name'], '', 0, 'L');
        $this->Cell($vWidth, $vHeight, '', '', 0, 'L');
        $this->Ln($vHeightLN);
        $this->ExactCell($vHeight, 'Код ОГРН  ');
//        $this->BoxedText($vWidth, $vHeight, $vBranchInfo['OGRN']);
        $this->BoxedText($vWidth, $vHeight, '             ');
        $this->Ln($vHeightLN);

        $this->SetXY($vX, $vY);

        $this->Cell($vWidth, $vHeight, 'Медицинская документация' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'форма № 044/у' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'УТВЕРЖДЕНА' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'Минздравом СССР' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, '04.10.80 №1030' , '', 0, 'R');
        $this->Ln($vHeightLN);
        $this->Ln($vHeightLN);

        $this->SetFont('arial_rus','',10);
        $vHeight   = $this->FontSize;
        $vHeightLN = $this->FontSize*1.5;
        $this->Cell($vWidth, $vHeight, 'КАРТА', '', 0, 'C');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'больного, лечащегося в физиотерапевтическом отделении (кабинете)', '', 0, 'C');
        $this->Ln($vHeightLN);

        $this->Notes('Карта стационарного (амб.) больного №', 0, $vWidth, 1, @$AInfo['case_id']);
        $this->Notes('Лечащий врач', 0, $vWidth, 1, _2w(FormatUserName(@$AInfo['doctor_id'])));
//        $this->Notes('Фамилия, имя, отчество', 0, $vWidth, 1, FormatShortName(@$AInfo['last_name'], @$AInfo['first_name'], @$AInfo['patr_name']));
/*
        $this->Notes('Фамилия, имя, отчество', 0, $vWidth, 1, _2w(FormatShortNameEx($AInfo)));
        $vX = $this->GetX();
        $vY = $this->GetY();
        $this->Notes('Возраст', 0, $vWidth/2, 1, CalcAge(@$AInfo['born_date']));
        $this->SetXY($vX+$vWidth/2, $vY);
        $this->Notes('Пол', 0, $vWidth/2, 1, FormatSex($AInfo['is_male']));
*/
        $this->Notes('Фамилия, имя, отчество', 0, $vWidth, 1, FormatShortNameEx($AInfo));
        $vX = $this->GetX();
        $vY = $this->GetY();
        $this->Notes('Возраст', 0, $vWidth/2, 1, CalcAge(@$AInfo['born_date']));
        $this->SetXY($vX+$vWidth/2, $vY);
        $this->Notes('Пол', 0, $vWidth/2, 1, _2w(FormatSex($AInfo['is_male'])));
        $this->Notes('Адрес', 0, $vWidth, 1, _2w(FormatAddress(@$AInfo['addr_reg_street'], @$AInfo['addr_reg_num'], @$AInfo['addr_reg_subnum'], @$AInfo['addr_reg_apartment'])));

        $this->Notes('Из какого отделения (кабинета) направлен больной', 0, $vWidth, 1, _2w($vBranchInfo['name']), false);

        $vX = $this->GetX();
        $vY = $this->GetY();
        $this->Notes('Диагноз', 0, $vWidth, 4, _2w(@$AInfo['diagnosis']), false);
        $vX1 = $this->GetX();
        $vY1 = $this->GetY();
        $this->SetFont('arial_rus','',4);
        $this->SetXY($vX, $vY+$vHeightLN*2-$this->FontSize/2);
        $this->Cell($vWidth, $vHeight, 'подчеркнуть заболевание, по поводу', '', 0, 'C');
        $this->Ln($vHeightLN);
        $this->Cell($vWidth, $vHeight, 'которого больной направлен на физиотерапию', '', 0, 'C');
        $this->SetXY($vX1, $vY1);

        $this->SetFont('arial_rus','',10);
        $this->Notes('Жалобы больного', 0, $vWidth, 2, '', false);

        $vX = $this->GetX();
        $vY = $this->GetY()+3;
        $this->SetXY($vX, $vY);

        $this->Cell(35, $vHeight, 'Назначение ');
        $this->Ln($vHeight);
        $this->Cell(35, $vHeight, 'процедуры');
        $this->Ln($vHeight);
        $this->Cell(35, $vHeight, 'лечащим врачом');
        $this->Ln($vHeight);
        $this->Cell(35, $vHeight, 'или врачом-');
        $this->Ln($vHeight);
        $this->Cell(35, $vHeight, 'физиотерапевтом');
        $this->Ln($vHeight);
        $this->Cell(35, $vHeight, '(подчеркнуть)');

        $vX += 38;
        $this->SetXY($vX, $vY);
        $this->Cell(15, $vHeight, 'Дата', 'LTR', 0, 'C');
        $this->Cell(30, $vHeight, 'Наименование', 'LTR', 0, 'C');
        $this->Cell(10, $vHeight, 'К-во', 'LTR', 0, 'R');
        $this->Cell(20, $vHeight, 'Продолжи-', 'LTR', 0, 'C');
        $this->Cell(15, $vHeight, 'Дози-', 'LTR', 0, 'C');
        $this->SetXY($vX, $vY+$vHeight);
        $this->Cell(15, $vHeight, '', 'LR', 0, 'C');
        $this->Cell(30, $vHeight, 'процедуры', 'LR', 0, 'C');
        $this->Cell(10, $vHeight, '', 'LR', 0, 'C');
        $this->Cell(20, $vHeight, 'тельность', 'LR', 0, 'C');
        $this->Cell(15, $vHeight, 'ровка', 'LR', 0, 'C');
        for( $i=0; $i<3; $i++)
        {
            $this->SetXY($vX, $vY+$vHeight*2+$i*$vHeightLN);
            $this->Cell(15, $vHeightLN, '', 'LTRB', 0, 'C');
            $this->Cell(30, $vHeightLN, '', 'LTRB', 0, 'C');
            $this->Cell(10, $vHeightLN, '', 'LTRB', 0, 'C');
            $this->Cell(20, $vHeightLN, '', 'LTRB', 0, 'C');
            $this->Cell(15, $vHeightLN, '', 'LTRB', 0, 'C');
        }
       
        $this->Ln($vHeightLN);
        $vX = $this->GetX();
        $vY = $this->GetY();
        $this->Line($vX, $vY+$vHeight/2, $vX+$vWidth, $vY+$vHeight/2);
        $this->Ln($vHeight);

        $this->Cell($vWidth*0.6, $vHeight, 'Место проведения процедуры: кабинет,');
        $this->Ln($vHeight);
        $this->Cell($vWidth*0.6, $vHeight, 'перевязочная, на дому (подчеркнуть)');
        $this->Ln($vHeight);

        $this->Cell($vWidth*0.6, $vHeight, 'Виды лечения, назначенные помимо');
        $this->Ln($vHeight);
        $this->Cell($vWidth*0.6, $vHeight, 'физиотерапии (в том числе и медикаментозные)');
        $this->Ln($vHeight);
        $this->Notes('', 0, $vWidth*0.6, 3, '', false);

        $this->Notes('Эпикриз', 0, $vWidth*0.6, 2, '', false);
        $this->Notes('Врач-физиотерапевт', 0, $vWidth*0.6, 1, '');
        $this->Image('images/angels.jpeg', $vX+$vWidth*0.65, $vY+$vHeight, $vWidth*0.35);
    }
}


// Trace($_GET);
$vDoc = new TPDF_Physiotherapy;
$vDoc->Render($_GET);

//while (ob_get_level())
//    ob_end_clean();

header('Accept-Ranges: bytes');
$vDoc->Output('Physiotherapy.pdf', 'I');

?>
