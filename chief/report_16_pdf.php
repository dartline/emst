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
                 
  array('title'=>"Травмы, отравления\nи некоторые другие\nпоследствия воздействия\nвнешних причин", 
        'codes'=>'S00 - T98'),
  array('title'=>"в том числе\nповерхностные травмы", 
        'codes'=>'S00, S10, S20, S30, S40, S50, S60, S70, S80, S90, T00, T09.0, T11.0, T13.0, T14.0'),
  array('title'=>'переломы черепа и лицевых костей, внутричерепные травмы', 
        'codes'=>'S02, S06'),
  array('title'=>'вывихи, растяжения и перенапряжения капсульно-связочного аппарата', 
        'codes'=>'S03, S13, S23, S33, S43, S53, S63, S73, S83, T09.2, T11.2, T13.2, T14.3'),
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

function AgeToColIndex($AAge)
{
    if ( $AAge<15 )
      return -1;
    elseif ( $AAge<20 )
      return 0;
    elseif ( $AAge<25 )
      return 1;
    elseif ( $AAge<30 )
      return 2;
    elseif ( $AAge<35 )
      return 3;
    elseif ( $AAge<40 )
      return 4;
    elseif ( $AAge<45 )
      return 5;
    elseif ( $AAge<50 )
      return 6;
    elseif ( $AAge<55 )
      return 7;
    elseif ( $AAge<60 )
      return 8;
    else
      return 9;
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

        $vWidths = array(40,  35,  5, 5);
        $vAligns = array('L', 'L','C', 'R');
        for( $i=0;$i<12;$i++ )
        {
           $vWidths[] = 16;
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
        $this->Cell($vWidth, $vHeight, 'Ф.16ВН', '', 0, 'R');
        $this->Ln($vHeight);

        $this->Cell($vWidth, $vHeight, 'Дата создания: ' . iconv("utf8","windows-1251",Date2ReadableLong(date('Y-m-d H:i:s'))), '', 0, 'L');
        $this->Ln($vHeight);

        $this->SetFont('arial','B',12);
        $this->Cell($vWidth, $vHeight, 'СВЕДЕНИЯ О ТРАВМАХ, ОТРАВЛЕНИЯХ И НЕКОТОРЫХ ДРУГИХ ПОСЛЕДСТВИЯХ ВОЗДЕЙСТВИЯ ВНЕШНИХ ПРИЧИН', '', 0, 'C');
        $this->SetFont('arial_rus','',10);

        $this->Ln($vHeight);
        $this->Cell($vWidth, $vHeight, 'за период с '.
                                       iconv("utf8","windows-1251",Date2ReadableLong($vBegDate)).
                                       ' г. по '.
                                       iconv("utf8","windows-1251",Date2ReadableLong($vEndDate)).
                                       ' г.', '', 0, 'C');
        $this->Ln($vHeight*2);


        $this->CellML($vWidths[0], $vHeight*3, 'Причина нетрудоспособности', 'L','R');
        $this->CellML($vWidths[1], $vHeight*3, 'Код по МКБ X пересмотра', 'L','R');
        $this->CellML($vWidths[2], $vHeight*3, 'Пол', 'C', 'U');
        $this->CellML($vWidths[3], $vHeight*3, '№ строки', 'C', 'U');
        $this->CellML($vWidths[4], $vHeight*3, 'Число дней', 'C', 'R');
        $this->CellML($vWidths[5], $vHeight*3, 'Число случаев', 'C', 'R');
        $vX = $this->GetX();
        $vY = $this->GetY();
        $this->CellML($vWidths[6]*10, $vHeight,'В том числе по возрастам', 'C', 'R');
        $this->SetXY($vX, $vY+$vHeight);
        $this->CellML($vWidths[6],   $vHeight*2, '15-19','C', 'R');
        $this->CellML($vWidths[7],   $vHeight*2, '20-24','C', 'R');
        $this->CellML($vWidths[8],   $vHeight*2, '25-29','C', 'R');
        $this->CellML($vWidths[9],   $vHeight*2, '30-34','C', 'R');
        $this->CellML($vWidths[10],  $vHeight*2, '35-39','C', 'R');
        $this->CellML($vWidths[11],  $vHeight*2, '40-44','C', 'R');
        $this->CellML($vWidths[12],  $vHeight*2, '45-49','C', 'R');
        $this->CellML($vWidths[13],  $vHeight*2, '50-54','C', 'R');
        $this->CellML($vWidths[14],  $vHeight*2, '55-69','C', 'R');
        $this->CellML($vWidths[15],  $vHeight*2, '60 и старше','C', 'R');

        $this->Ln($vHeight*2);

        $vCodeToRow =& PrepareCodeToRowMap();
/*
        SELECT 
          emst_cases.id as id,
          emst_cases.create_time,
          emst_cases.born_date, 
          emst_cases.is_male, 
          DATEDIFF( max(emst_surgeries.ill_end_date), 
                    emst_cases.disability_from_date
                  ) as days,
          emst_cases.diagnosis_mkb
        FROM
          emst_cases 
          JOIN emst_surgeries ON emst_surgeries.case_id = emst_cases.id
        WHERE 
          emst_surgeries.disability=2 AND 
          emst_surgeries.ill_end_date != '0000-00-00' AND
          emst_cases.disability_from_date != '0000-00-00' AND
          emst_cases.create_time >= '2006-01-01' AND
          emst_cases.create_time < '2007-01-01'
        GROUP BY
          emst_cases.id  
*/

        $vTable  = 'emst_cases JOIN emst_surgeries ON emst_surgeries.case_id = emst_cases.id';
        $vFields = 'emst_cases.id as id, emst_cases.create_time, emst_cases.born_date, emst_cases.is_male, DATEDIFF( max(emst_surgeries.ill_end_date), emst_cases.disability_from_date) as days, emst_cases.diagnosis_mkb';
        $vFilterParts = array();
        $vFilterParts[] = 'emst_surgeries.disability=2';
        $vFilterParts[] = "emst_surgeries.ill_end_date != '0000-00-00'";
        $vFilterParts[] = "emst_cases.disability_from_date != '0000-00-00'";
        $vFilterParts[] = $vDB->CondGE('create_time', $vBegDate);
        $vFilterParts[] = $vDB->CondLT('create_time', DateAddDay($vEndDate));
        $vFilter = implode(' AND ', $vFilterParts) . ' GROUP BY emst_cases.id';
        $vOrder  = '';
        $vRecords= $vDB->Select($vTable, $vFields, $vFilter, $vOrder);
        $vBadCodes = array();
        $vReport = array();

        while( $vRecord = $vRecords->Fetch() )
        {
            if ( $vRecord['diagnosis_mkb'] === NULL || 
                 $vRecord['is_male'] === NULL
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
            $vAge = CalcAge($vRecord['born_date'], $vRecord['create_time']);
            $vAgeColIndex = AgeToColIndex($vAge);
            if ( $vAgeColIndex>0 ) 
            {
               foreach( $vRowIndexes as $vRowIndex )
               {
                  $vReport[$vRowIndex][$vIsMale][0] = $vRecord['days'] + 1 + @$vReport[$vRowIndex][$vIsMale][0];
                  $vReport[$vRowIndex][$vIsMale][1] = 1 + @$vReport[$vRowIndex][$vIsMale][1];
                  $vReport[$vRowIndex][$vIsMale][$vAgeColIndex+2] = 1 + @$vReport[$vRowIndex][$vIsMale][$vAgeColIndex+2];
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
/*
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
*/
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

?>

