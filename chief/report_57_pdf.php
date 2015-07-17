<?php

#####################################################################
#
# Травмпункт. (c) 2005 Vista
#
#####################################################################

require_once 'library/fpdfex.php';
require_once 'library/cases_table.php';

/*
CREATE
DEFINER=`root`@`%`
FUNCTION `Age`(BornDate DATE, CalcDate DATE) RETURNS int(11)
  COMMENT 'Calc age of person borned at BornDate at date CalcDate'
BEGIN
  DECLARE vResult INTEGER;
  SET vResult = YEAR(CalcDate) - YEAR(BornDate)- 1;

  IF ( MONTH(BornDate) > MONTH(CalcDate) ) OR ( (MONTH(BornDate) = MONTH(CalcDate)) AND (DAY(BornDate) > DAY(CalcDate)) ) THEN
    SET vResult = vResult-1;
  END IF;
  RETURN vResult;
END;

=======================================================

SELECT count(id) AS cnt, diagnosis_mkb, is_male, DATE_ADD( born_date, INTERVAL 18 YEAR ) > create_time AS is_child, trauma_type_id
FROM emst_cases
WHERE create_time >= '2006-01-01'
AND create_time < '2007-01-01'
GROUP BY diagnosis_mkb, is_male, trauma_type_id, is_child
LIMIT 0 , 1600 

=======================================================
*/


$RowDescr = array(
  array('title'=>"Всего,\nв том числе:", 'codes'=>'S00 - T98'),
  array('title'=>'поверхностные травмы', 'codes'=>'S00, S10, S20, S30, S40, S50, S60, S70, S80, S90, T00, T09.0, T11.0, T13.0, T14.0'),
  array('title'=>'открытые  раны, травмы кровеносных сосудов', 
        'codes'=>'S01, S09.0, S11, S15, S21, S25, S31, S35, S41, S45, S51, S55, S61, S65, S71, S75, S81, S85, S91, S95, T01, T06.3, T09.1, T11.1, T11.4, T13.1, T13.4, T14.1, T14.5'),
  array('title'=>'переломы черепа и лицевых костей', 'codes'=>'S02'),
  array('title'=>'травмы глаза  и глазницы', 'codes'=>'S05'),
  array('title'=>'внутричерепные травмы', 'codes'=>'S06'),
  array('title'=>'переломы костей верхней конечности', 'codes'=>'S42, S52, S62, T02.2, T02.4, T10'),
  array('title'=>'в том числе перелом нижнего конца лучевой кости, сочетанный перелом нижних концов локтевой и лучевой кости', 'codes'=>'S52.5, 6'),
  array('title'=>'переломы костей нижней конечности', 'codes'=>'S72, S82, S92, T02.3, T02.5, T12'),
  array('title'=>'в том числе перелом нижнего конца бедренной кости', 'codes'=>'S72.4'),
  array('title'=>'переломы позвоночника, костей туловища, других и неуточненных областей тела', 'codes'=>'S12, S22, S32, T02.0, T02.1, T02.7-9, T08, T14.2'),
  array('title'=>'вывихи, растяжения и перенапряжения капсульно-связочного аппарата суставов, травмы мышц и сухожилий', 'codes'=>'S03, S09.1, S13, S16, S23, S29.0, S33, S39.0, S43, S46, S53, S56, S63, S66, S73, S76, S83, S86, S93, S96, T03, T06.4, T09.2, T09.5, T11.2, T11.5, T13.2, T13.5, T14.3, T14.6'),
  array('title'=>'травмы нервов и спинного мозга', 'codes'=>'S04, S14, S24, S34, S44, S54, S64, S74, S84, S94, T06.0-T06.2, T09.3, T09.4, T11.3, T13.3, T14.4'),
  array('title'=>'размозжения (раздавливание), травматические ампутации', 'codes'=>'S07, S08, S17, S18, S28, S38, S47, S48, S57, S58, S67, S68, S77, S78, S87, S88, S97, S98, T04, T05, T09.6, T11.6, T13.6, T14.7'),
  array('title'=>'травмы внутренних органов грудной и брюшной областей, таза', 'codes'=>'S26, S27, S36, S37, S39.6-9, T06.5'),
  array('title'=>'термические и химические ожоги', 'codes'=>'T20 - T32'),
  array('title'=>'отравления лекарственными средствами, медикаментами и биологическими веществами, токсическое действие веществ, преимущественно немедицинского назначения', 'codes'=>'T36 - T65'),
  array('title'=>'осложнения хирургических и терапевтических вмешательств, не классифицированные в других рубриках', 'codes'=>'T80 - T88'),
  array('title'=>'последствия травм, отравлений, других воздействий внешних причин', 'codes'=>'T90 - T98'),
  array('title'=>'прочие', 'codes'=>'S09.2, 7-9, S19, S29.7 - 9, S49, S59, S69, S79, S89, S99, T02.6, T06.8, T07, T09.8 - 9, T11.8 - 9, T13.8 - 9, T14.8 - 9, T15 - T19, T33 - T35, T66 - T79'),
  );



function NormalizeCode(&$APrefix, &$ACode)
{
  $ACode = strtoupper(trim($ACode));
  if ( preg_match('/^[A-Z]/i', $ACode) )
  {
    $vCodeParts = explode('.',$ACode);
    $APrefix = $vCodeParts[0];
  }
  else
  {
    assert( $APrefix != '' );
    $ACode = $APrefix.'.'.$ACode;
  }

  assert( preg_match('/^[A-Z][0-9][0-9](\.[0-9])?$/i', $ACode) ); 
}


function AddCodeX($ARowIdx, $ACode, &$ACodesToRow)
{
  if ( empty($ACodesToRow[$ACode]) )
    $ACodesToRow[$ACode] = array($ARowIdx);
  else
    $ACodesToRow[$ACode][] = $ARowIdx;
}


function AddCode($ARowIdx, $ACode, &$ACodesToRow)
{
  if ( preg_match('/^[A-Z][0-9][0-9]\.[0-9]$/i', $ACode) )
  {
    AddCodeX($ARowIdx, $ACode, $ACodesToRow);
  }
  elseif ( preg_match('/^[A-Z][0-9][0-9]$/i', $ACode) )
  {
    AddCodesRange($ARowIdx, $ACode, $ACode, $ACodesToRow);
  }
}


function AddCodesRange($ARowIdx, $ALowCode, $AHighCode, &$ACodesToRow)
{
  assert( $ALowCode <= $AHighCode );

  if ( preg_match('/^[A-Z][0-9][0-9]$/i', $ALowCode) )
  {
    $ALowCode = $ALowCode . '.0';
  }
  if ( preg_match('/^[A-Z][0-9][0-9]$/i', $AHighCode) )
  {
    $AHighCode = $AHighCode . '.9';
  }

  $vLow  = (((ord($ALowCode[0])-ord('A') )*10+ ord($ALowCode[1])-ord('0') )*10 + ord($ALowCode[2])-ord('0'))*10+ord($ALowCode[4])-ord('0');
  $vHigh = (((ord($AHighCode[0])-ord('A') )*10+ ord($AHighCode[1])-ord('0') )*10 + ord($AHighCode[2])-ord('0'))*10+ord($AHighCode[4])-ord('0');

  for( $i=$vLow; $i<=$vHigh; $i++ )
  {
    $vCode = sprintf('%c%c%c.%c', $i/1000+ord('A'), ($i/100)%10+ord('0'), ($i/10)%10+ord('0'), ($i)%10+ord('0')); 
    AddCodeX($ARowIdx, $vCode, $ACodesToRow);
  }
}



function ParseRowCodes($ARowIdx, $ACodes, &$ACodesToRow)
{
    $vRanges = explode(',', $ACodes);
    $vPrefix = '';
    foreach( $vRanges as $vRange )
    {
        $vLimits = explode( '-', $vRange);
        switch ( count($vLimits) )
        {
        case 1: 
            NormalizeCode($vPrefix, $vLimits[0]);
            AddCode($ARowIdx, $vLimits[0], $ACodesToRow);
            break;
        case 2:
            NormalizeCode($vPrefix, $vLimits[0]);
            NormalizeCode($vPrefix, $vLimits[1]);
            AddCodesRange($ARowIdx, $vLimits[0], $vLimits[1], $ACodesToRow);
            break;
        default:
            assert(false);
        }
    }
}


function& PrepareCodeToRowMap()
{
    global $RowDescr;
    $vCodeToRow = array();
    for( $i=0; $i<count($RowDescr); $i++ )
    {
        ParseRowCodes($i, $RowDescr[$i]['codes'], $vCodeToRow);
    }
    return $vCodeToRow;
}

/* ==================================================================== */

class TPDF_Report extends FPDFEx
{
    function TPDF_Report()
    {
        $this->FPDFEx('Stats', 'L');
        $this->Open();
        $this->SetMargins(10,10,10);
    }


    function TextWithDirection($x,$y,$txt,$direction='R')
    {
        $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
        if ($direction=='R')
            $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$txt);
        elseif ($direction=='L')
            $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$txt);
        elseif ($direction=='U')
            $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$txt);
        elseif ($direction=='D')
            $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$txt);
        else
            $s=sprintf('BT %.2f %.2f Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$txt);
        if ($this->ColorFlag)
            $s='q '.$this->TextColor.' '.$s.' Q';
        $this->_out($s);
    }

/*
    function TextWithRotation($x,$y,$txt,$txt_angle,$font_angle=0)
    {
        $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));

        $font_angle+=90+$txt_angle;
        $txt_angle*=M_PI/180;
        $font_angle*=M_PI/180;

        $txt_dx=cos($txt_angle);
        $txt_dy=sin($txt_angle);
        $font_dx=cos($font_angle);
        $font_dy=sin($font_angle);

        $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',
                 $txt_dx,$txt_dy,$font_dx,$font_dy,
                 $x*$this->k,($this->h-$y)*$this->k,$txt);
        if ($this->ColorFlag)
            $s='q '.$this->TextColor.' '.$s.' Q';
        $this->_out($s);
    }
*/

    function CellML($AWidth, $AHeight, $AText, $AHAlign='L', $ADirection='R')
    {   
        $vNormDirection = ($ADirection=='R' || $ADirection=='L');
        if ( $vNormDirection )
        {
            $vTextWidth  = $AWidth;
            $vTextHeight = $AHeight;
        }
        else
        {
            $vTextWidth  = $AHeight;
            $vTextHeight = $AWidth;
        }
        $vSplited = $this->SplitText( @$AText, $vTextWidth);
        $vLines   = count($vSplited);
        if ( $vLines == 0 )
          $vLines = 1;

        $vLineHeight  = min($vTextHeight/$vLines, $this->FontSize*1.5);

        $vX = $this->GetX();
        $vY = $this->GetY();
        if ( $vNormDirection )
        {
            $vX0    = $vX+$this->LineWidth;
            $vY0    = $vY+$this->FontSize;
            $vStepX = 0;
            $vStepY = $vLineHeight;
        }
        else
        {
            $vX0    = $vX+$this->FontSize;
            if ( $AHAlign == 'C' )
              $vX0 += ($AWidth-$this->FontSize*$vLines)/2;
            elseif ( $AHAlign == 'R' )
              $vX0 += ($AWidth-$this->FontSize*$vLines);

            $vY0    = $vY+$AHeight-$this->LineWidth;
            $vStepX = $vLineHeight;
            $vStepY = 0;
        }

        $this->Cell($AWidth,  $AHeight, '',  'LTRB', 0, '');
        if ( $vNormDirection )
        {
           for($i=0; $i<$vLines; $i++)
           {
               $vText = @$vSplited[$i];
               if ( $AHAlign == 'C' )
                 $vAlignOffsetX = ($AWidth-$this->GetStringWidth($vText))/2;
               elseif ( $AHAlign == 'R' )
                 $vAlignOffsetX = $AWidth-$this->GetStringWidth($vText);
               else
                 $vAlignOffsetX = 0;

               $this->GetStringWidth($vText);
               $this->TextWithDirection( $vX0+$vAlignOffsetX+$vStepX*$i, $vY0+$vStepY*$i, $vText, $ADirection);
           }
        }
        else
        {
           for($i=0; $i<$vLines; $i++)
           {
               $vText = @$vSplited[$i];
               $this->TextWithDirection( $vX0+$vStepX*$i, $vY0+$vStepY*$i, $vText, $ADirection);
           }
        }
        $this->SetXY($vX+$AWidth, $vY);
    }


    function Render($AParams)
    {
        global $RowDescr;
        $vBranchInfo = GetBranchInfo();

        $vWidths = array(35,  25,  5, 5);
        $vAligns = array('L', 'L','C', 'R');
        for( $i=0;$i<=20;$i++ )
        {
           $vWidths[] = 10;
           $vAligns[] = 'R';
        }

        $vDB = GetDB();
        $vBegDate = $AParams['beg_date'];
        $vEndDate = $AParams['end_date'];

        $this->SetFont('arial_rus','',10);
        $vHeight = $this->FontSize*1.5;
        $vWidth  = $this->GetAreaWidth();
        $this->AddPage();

        $vX = $this->GetX();
        $vY = $this->GetY();
        $this->Cell($vWidth, $vHeight, iconv("utf8","windows-1251",@$vBranchInfo['name']), '', 0, 'L');
        $this->SetXY($vX, $vY);
        $this->Cell($vWidth, $vHeight, 'Ф.57', '', 0, 'R');
        $this->Ln($vHeight);

        $this->Cell($vWidth, $vHeight, 'Дата создания: ' . iconv("utf8","windows-1251",Date2ReadableLong(date('Y-m-d H:i:s'))), '', 0, 'L');
        $this->Ln($vHeight);

        $this->SetFont('arial','B',12);
        $this->Cell($vWidth, $vHeight, 'СВЕДЕНИЯ О ТРАВМАХ, ОТРАВЛЕНИЯХ И НЕКОТОРЫХ ДРУГИХ ПОСЛЕДСТВИЯХ ВОЗДЕЙСТВИЯ ВНЕШНИХ ПРИЧИН', '', 0, 'C');
        $this->SetFont('arial_rus','',7);

        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, 'за период с '.
                                       iconv("utf8","windows-1251",Date2ReadableLong($vBegDate)).
                                       ' г. по '.
                                       iconv("utf8","windows-1251",Date2ReadableLong($vEndDate)).
                                       ' г.', '', 0, 'C');
        $this->Ln($vHeight*2);


        $this->CellML($vWidths[0], $vHeight*6, 'Травмы, отравления и некоторые другие последствия воздействия внешних причин', 'L','R');
        $this->CellML($vWidths[1], $vHeight*6, 'Код по МКБ X пересмотра', 'L','R');
        $this->CellML($vWidths[2], $vHeight*6, 'Пол', 'C', 'U');
        $this->CellML($vWidths[3], $vHeight*6, '№ строки', 'C', 'U');
        $vX = $this->GetX();
        $vY = $this->GetY();
        $this->CellML($vWidths[4]*12, $vHeight,   'У взрослых и подростков (18 лет и старше)', 'C', 'R');
        $this->CellML($vWidths[4]*8,  $vHeight,   'У детей (0 - 17 лет включительно)', 'C', 'R');
        $this->CellML($vWidths[24],   $vHeight*6, 'ВСЕГО', 'C', 'U');
        $this->SetXY($vX, $vY+$vHeight);
        $this->CellML($vWidths[4]*5,  $vHeight,   'связанные с производством', 'C', 'R');
        $this->CellML($vWidths[9]*6,  $vHeight,   'несвязанные с производством', 'C', 'R');
        $this->CellML($vWidths[15],   $vHeight*5, 'ИТОГО','C', 'U');

        $this->CellML($vWidths[16],   $vHeight*5, 'бытовые','C', 'U');
        $this->CellML($vWidths[17],   $vHeight*5, 'уличные','C', 'U');
        $this->CellML($vWidths[18]*2, $vHeight*2, 'транспорт-ные', 'C');
        $this->CellML($vWidths[20],   $vHeight*5, 'школьные','C', 'U');
        $this->CellML($vWidths[21],   $vHeight*5, 'спортивные','C', 'U');
        $this->CellML($vWidths[22],   $vHeight*5, 'прочие','C', 'U');
        $this->CellML($vWidths[22],   $vHeight*5, 'ИТОГО','C', 'U');

        $this->SetXY($vX, $vY+$vHeight*2);

        $this->CellML($vWidths[4],    $vHeight*4, 'в промышлен-ности','C', 'U');
        $this->CellML($vWidths[5],    $vHeight*4, 'в сельском хозяйстве','C', 'U');
        $this->CellML($vWidths[6]*2,  $vHeight*2, 'транспорт-ные','C','R');
        $this->CellML($vWidths[8],    $vHeight*4, 'прочие','C', 'U');

        $this->CellML($vWidths[9],    $vHeight*4, 'бытовые','C', 'U');
        $this->CellML($vWidths[10],   $vHeight*4, 'уличные','C', 'U');
        $this->CellML($vWidths[11]*2, $vHeight*2, 'транспорт-ные','C','R');
        $this->CellML($vWidths[13],   $vHeight*4, 'спортивные','C', 'U');
        $this->CellML($vWidths[14],   $vHeight*4, 'прочие','C', 'U');

        $this->SetXY($vX+$vWidths[4]*2, $vY+$vHeight*4);
        $this->CellML($vWidths[6],    $vHeight*2, 'всего','C', 'U');
        $this->CellML($vWidths[7],    $vHeight*2, 'авто','C', 'U');

        $this->SetXY($vX+$vWidths[4]*7, $vY+$vHeight*4);
        $this->CellML($vWidths[11],   $vHeight*2, 'всего','C', 'U');
        $this->CellML($vWidths[12],   $vHeight*2, 'авто','C', 'U');

        $this->SetXY($vX+$vWidths[4]*14, $vY+$vHeight*3);
        $this->CellML($vWidths[18],   $vHeight*3, 'всего','C', 'U');
        $this->CellML($vWidths[19],   $vHeight*3, 'авто','C', 'U');
        $this->Ln($vHeight*3);

        $vCodeToRow =& PrepareCodeToRowMap();

        $vTable  = 'emst_cases LEFT JOIN rb_trauma_types ON rb_trauma_types.id = emst_cases.trauma_type_id';
        $vFields = 'count(emst_cases.id) AS cnt, diagnosis_mkb, is_male, if( DATE_ADD( born_date, INTERVAL 18 YEAR ) > create_time, rb_trauma_types.f57_col_child, rb_trauma_types.f57_col_adult) as cols';
        $vFilter = $vDB->CondGE('create_time', $vBegDate) .
                   ' AND '.
                   $vDB->CondLT('create_time', DateAddDay($vEndDate)).
                   '  GROUP BY diagnosis_mkb, is_male, cols';
        $vOrder  = '';
        $vRecords= $vDB->Select($vTable, $vFields, $vFilter, $vOrder);
        $vBadCodes = array();
        $vReport = array();
        while( $vRecord = $vRecords->Fetch() )
        {
            if ( $vRecord['diagnosis_mkb'] === NULL || 
                 $vRecord['is_male'] === NULL ||
                 $vRecord['cols'] === NULL
               )
            {
                continue;
            }
            $vCode = str_replace(' ', '', $vRecord['diagnosis_mkb']);
            if ( $vCode == '' ) 
                continue;

            if ( preg_match('/^[A-Z][0-9][0-9]$/i', $vCode) )
                $vCode = $vCode .'.0';
            else if ( preg_match('/^[A-Z][0-9][0-9]\.[0-9].*$/i', $vCode) ) 
                $vCode = substr($vCode,0,5);
            if ( empty($vCodeToRow[$vCode]) )
            {
                $vBadCodes[$vRecord['diagnosis_mkb']] = $vRecord['diagnosis_mkb'];
                continue;
            }
            $vRowIndexes = $vCodeToRow[$vCode];
            $vIsMale     = $vRecord['is_male']?1:0;
            $vCols       = explode(',', $vRecord['cols']);

            foreach(  $vCols as $vCol )
            {
               $vColIdx = trim($vCol);
               foreach( $vRowIndexes as $vRowIndex )
               {
                  $vReport[$vRowIndex][$vIsMale][$vColIdx] = $vRecord['cnt'] + @($vReport[$vRowIndex][$vIsMale][$vColIdx]);
               }
            }
        }



        $vRowNum = 0;
        for( $i=0; $i<count($RowDescr); $i++ )
        {
            $vRow = array($RowDescr[$i]['title'], $RowDescr[$i]['codes'], 'М', ++$vRowNum);
            for( $j=0; $j<=20; $j++ )
               $vRow[] = @$vReport[$i][1][$j];
            $this->OutputTableRow($vWidths, $vHeight, $vRow, $vAligns);


            $vRow = array('', '', 'Ж', ++$vRowNum);
            for( $j=0; $j<=20; $j++ )
               $vRow[] = @$vReport[$i][0][$j];
            $this->OutputTableRow($vWidths, $vHeight, $vRow, $vAligns);
        }

        if ( @$AParams['show_unlisted_cases'] )
        {
            $vTable  = 'emst_cases';
            $vFields = 'id, diagnosis_mkb, trauma_type_id, born_date';
            $vFilter = $vDB->CondGE('create_time', $vBegDate) .
                       ' AND '.
                       $vDB->CondLT('create_time', DateAddDay($vEndDate)).
                       ' AND ('.
                         $vDB->CondIn('diagnosis_mkb', $vBadCodes).
                          ' OR diagnosis_mkb IS NULL'.
                          ' OR trauma_type_id IS NULL'.
                          ' OR born_date IS NULL'.
                          ' OR born_date = 0000-00-00'.
                          ')';

            $vOrder  = 'id';
            $vRecords= $vDB->Select($vTable, $vFields, $vFilter, $vOrder);
            if ( $vRecords->Count() > 0 )
            {
                $this->AddPage();
                $this->Cell(30, $vHeight,  '№ по порядку', 1, 0, 'R');
                $this->Cell(30, $vHeight,  '№ истории', 1, 0, 'R');
                $this->Cell(180, $vHeight, 'причина', 1, 0, 'L');
                $this->Ln($vHeight);

                $i = 0;
                while ( $vRecord = $vRecords->Fetch() )
                {
                   $vMessage = array();
                   $vCode = str_replace(' ', '', $vRecord['diagnosis_mkb']);
                   if ( $vCode != '' &&
                        !( preg_match('/^[A-Z][0-9][0-9](\.[0-9])?$/i', $vCode) &&
                           $vCode>='S00' &&
                           $vCode<='T98.9'
                         )   
                      )
                   {
                      $vMessage[] = 'Код МКБ "'.$vRecord['diagnosis_mkb'].'"';
                   }
                   if ( $vRecord['trauma_type_id'] === NULL || $vRecord['trauma_type_id'] === '' )
                      $vMessage[] = 'не указан тип травмы';
                   if ( $vRecord['born_date'] === NULL || $vRecord['born_date'] === '' || $vRecord['born_date'] === '0000-00-00' )
                      $vMessage[] = 'дата рождения не указана';

                   $this->Cell(30, $vHeight, ++$i, 1, 0, 'R');
                   $this->Cell(30, $vHeight, $vRecord['id'], 1, 0, 'R');
                   $this->Cell(180, $vHeight, implode(', ', $vMessage), 1, 0, 'L');
                   $this->Ln($vHeight);
                }
            }
        }
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
$vDoc->Output('Surgeries_report.pdf', 'I');

