<?php
define('FPDF_FONTPATH','fpdf/cp1251/');

require_once './fpdf/fpdf.php';


class TRTFItem
{
    public $x;
    public $y;
    public $Width;
    public $Height;
    public $Font;
    public $Style;
    public $Size;
    public $Text;
    public $SpaceWidth;
}

class TRTF
{
    private $_Doc;
    private $_Width;
    private $_Height;
    private $_TabStop = array(10,20,30,40,50,60,70,80,90,100);
    private $_FontStack = array();


    function TRTF(&$ADoc, $AWidth=0)
    {
        $this->_Doc    =& $ADoc;
        $this->_Width  =  $AWidth;
        $this->_Height =  0;

        $this->_Font  = $ADoc->FontFamily;
        $this->_Style = $ADoc->FontStyle.($ADoc->underline ? 'U' : '');
        $this->_Size  = $ADoc->FontSizePt;
    }


    function SetFont($AFont, $AStyle='', $ASize=0)
    {
        $this->_Font  = $AFont;
        $this->_Style = $AStyle;
        $this->_Size  = $ASize;
    }


    function PushFont()
    {
        array_push($this->_FontStack, array($this->_Font, $this->_Style, $this->_Size));
    }


    function PopFont()
    {
        list($this->_Font, $this->_Style, $this->_Size) = array_pop($this->_FontStack);
        $this->_Doc->SetFont($this->_Font, $this->_Style, $this->_Size);
    }


    function SetTabStop( $ATabStop = array() )
    {
        $this->_TabStop = $ATabStop;
    }


    function Text($AText)
    {
        $this->_Height = 0;
        $this->_Doc->SetFont($this->_Font, $this->_Style, $this->_Size);
        $vFontSize = $this->_Doc->FontSizePt;
        $vHeight   = $this->_Doc->FontSize*1.5;
        $vSpaceWidth = $this->_Doc->GetStringWidth(' ');

        $vLines = explode("\n",$AText);
        $vForceNl = False;
        for( $i=0; $i<count($vLines); $i++)
        {
            $vSegments = explode("\t",$vLines[$i]);
            for( $j=0; $j<count($vSegments); $j++ )
            {
                $vWords = explode(" ", $vSegments[$j]);
                for ( $k=0; $k<count($vWords); $k++ )
                {
                    $vWord = $vWords[$k];
                    $vItem = new TRTFItem;
                    $vItem->Width    = $this->_Doc->GetStringWidth($vWord);
                    $vItem->Height   = $vHeight;
                    $vItem->Font     = $this->_Font;
                    $vItem->Style    = $this->_Style;
                    $vItem->Size     = $this->_Size;
                    $vItem->Text     = $vWord;
                    $vItem->BasLine  = $vFontSize;
                    $vItem->ForceNl  = $k==0 && $j==0 && $i>0;
                    $vItem->ForceTab = $k==0 && $j>0;
                    $vItem->SpaceWidth = $vSpaceWidth;
                    $this->_Items[]  = $vItem;
                }
            }
        }
    }



    function _CalcHeight_FinishLine($AFirstLineItem, $ALineHeight, $AY)
    {
        for( $i=$AFirstLineItem; $i<count($this->_Items);  $i++ )
        {
            $vItem =& $this->_Items[$i];
            $vItem->y = $AY + $ALineHeight;
//          $vItem->y = $AY + $vLineHeight - $vItem->Height;
        }

        return $AFirstLineItem<count($this->_Items) ? $ALineHeight : 0;
    }


    function _NextTab($X)
    {
        $vCount = count($this->_TabStop);
        for( $i=0; $i<$vCount; $i++ )
        {
            if ( $this->_TabStop[$i]>=$X )
                return $this->_TabStop[$i];
        }
        if ( $vCount > 0 )
            $vPos = $this->_TabStop[$vCount-1];
        else
            $vPos = 0;

        $vN = ceil(($X-$vPos)/10)*10+$vPos;
    }


    function _CalcHeight()
    {
        $vX = 0;
        $vY = 0;

        $vFirstLineItem = 0;
        $vLineHeight    = 0;

        $vCount = count($this->_Items);
        $vItemsInLineCnt = 0;
        for( $i=0; $i<$vCount; $i++ )
        {
            $vItem =& $this->_Items[$i];
            $vItemWidth = $vItem->Width;
            if ( $vItem->ForceTab )
              $vX = $this->_NextTab($vX);

            $vItemRightMargin = $vX+$vItemWidth;
            if ( ($vItemRightMargin <= $this->_Width || $vItemsInLineCnt == 0 ) && !$vItem->ForceNl )
            {
                $vItem->x = $vX;
                $vLineHeight = max($vLineHeight, $vItem->Height);
                $vX = $vItemRightMargin + $vItem->SpaceWidth;
            }
            else
            {
                $vY += $this->_CalcHeight_FinishLine($vFirstLineItem, $vLineHeight, $vY);

                $vFirstLineItem = $i;
                $vLineHeight    = 0;

                $vX = 0;
                $vItem->x = $vX;
                $vLineHeight = max($vLineHeight, $vItem->Height);
            }
        }
        $vY += $this->_CalcHeight_FinishLine($vFirstLineItem, $vLineHeight, $vY);
        $this->_Height = $vY;
    }


    function GetHeight()
    {
        if ( $this->_Height == 0 )
            $this->_CalcHeight();

        return $this->_Height;
    }


    function Output($AHeight=0, $AFrame=0)
    {
        if ( $this->_Height == 0 )
            $this->_CalcHeight();

        if ( $AHeight == 0 )
            $AHeight = $this->_Height;

        $vX = $this->_Doc->GetX();
        $vY = $this->_Doc->GetY();

        $vCount = count($this->_Items);
        for( $i=0; $i<$vCount; $i++ )
        {
            $vItem =& $this->_Items[$i];
            $this->_Doc->SetXY( $vX+$vItem->x, $vY+$vItem->y );
            $this->_Doc->SetFont($vItem->Font, $vItem->Style, $vItem->Size);
            $this->_Doc->Cell($vItem->Width, $vItem->Height, $vItem->Text);
        }

        $this->_Doc->SetXY($vX, $vY);
        $this->_Doc->Cell($this->_Width, $AHeight, '', $AFrame);
    }
  }


class FPDFEx extends FPDF
{
    private $_RusFonts = array();

    function FPDFEx($ATitle, $AOrientation='P', $APaperSize='A4')
    {
        $this->FPDF($AOrientation, 'mm', $APaperSize);
        $this->SetTitle($ATitle);
        $this->SetAutoPageBreak(TRUE, 15);

        $this->AddFont('arial_rus','', 'arial_rus.php');
//      $this->AddFont('arial_rus','B','arial_rus_b.php');
//      $this->AddFont('arial_rus','I','arial_rus_i.php');
//      $this->AddFont('arial_rus','BI','arial_rus_bi.php');
    }


    function SetFont($AFamily, $AStyle='',$ASize=0)
    {
        $AFamily = strtolower($AFamily);
        $AStyle  = strtoupper($AStyle);

        $vStyle  = '';

        if ( strpos($AStyle,'B') !== false )
            $vStyle .= 'B';

        if ( strpos($AStyle,'I') !== false )
            $vStyle .= 'I';

        if ( !empty($vStyle) )
            $vSuffix = '_'.strtolower($vStyle);
        else
            $vSuffix = '';

        switch( $AFamily )
        {
            case 'arial'  :
            case 'courier':
            case 'times'  : $AFamily .= '_rus';
                            $vFont = $AFamily.$vSuffix;
                            if ( empty($this->_RusFonts[$vFont]) )
                            {
                                $this->AddFont($AFamily, $vStyle, $vFont.'.php');
                                $this->_RusFonts[$vFont] = true;
                            }
                            break;
            default:        break;
        }
        parent::SetFont($AFamily, $AStyle, $ASize);
    }


    function GetAreaWidth()
    {
        return $this->w - ($this->lMargin+$this->rMargin);
    }


    function CheckSpace($AHeight)
    {
        if($this->y+$AHeight>$this->PageBreakTrigger && !$this->InFooter && $this->AcceptPageBreak())
            $this->AddPage();
    }


    function SplitText($text, $maxwidths)
    {
        $vResult = array();
        if ($text==='')
            return $vResult;

        if ( is_array($maxwidths) )
            $maxwidth = $maxwidths[0];
        else
            $maxwidth = $maxwidths;

        $lines = explode("\n", $text);
        $count = 0;

        foreach ($lines as $line)
        {
            while( $line !== false )
            {
                $len = strlen($line);
                $width = 0;
                $breakpos1 = 0;
                $breakpos2 = 0;
                $text = false;
                for( $i=0; $i<$len; $i++ )
                {
                    $c = $line{$i};
                    $cwidth = $this->GetStringWidth($c);
                    if ( ($width + $cwidth > $maxwidth)  )
                    {
                        if ( $breakpos1 > 0 )
                        {
                            $text = substr($line, 0, $breakpos1+1);
                            $line = substr($line, $breakpos1+1);
                        }
                        elseif ( $breakpos2 > 0 )
                        {
                            $text = substr($line, 0, $breakpos2+1);
                            $line = substr($line, $breakpos2+1);
                        }
                        else
                        {   
                            $text = substr($line, 0, $i);
                            $line = substr($line, $i);
                        }
                        break;
                    }
                    else
                    {
                        $width += $cwidth;
                        if ( $c == ' ' )
                        {
                            $breakpos1 = $i;
                        }
                        elseif ( strchr ('-,.;:!?)]}\|/', $c) !== false )
                        {
                            $breakpos2 = $i;
                        }

                    }
                }

                if ( $text === false )
                {
                    $text = $line;
                    $line = false;
                }
                $vResult[] = rtrim($text);
                $count++;
                if ( is_array($maxwidths) && count($maxwidths)>$count )
                  $maxwidth = $maxwidths[$count];
            }
        }
        return $vResult;
    }


    function BoxedText($AWidth, $AHeight, $AText, $ANumBoxes=0)
    {
        $n = strlen($AText);
        if ( $ANumBoxes == 0 )
          $ANumBoxes = $n;
        $vCharWidth = $this->GetStringWidth('W');
        for( $i=0; $i<$ANumBoxes; $i++ )
        {
            $vChar = substr($AText, $i, 1);
            $vX    = $this->GetX();
            $this->Cell($vCharWidth, $AHeight, $vChar, '1', 0, 'C');
            $this->SetX($vX+$vCharWidth+1);
        }

    }


    function Notes($ATitle, $ATitleWidth, $AWidth, $ARows=1, $AText='', $AIndentation=true)
    {
        $vHeight = $this->FontSize*1.5;
        if ( $ATitleWidth <= 0 )
        {
            $vTitleWidth = $this->GetStringWidth($ATitle);
            if ( $vTitleWidth > 0 )
                $vTitleWidth += $this->GetStringWidth('o');
        }
        else
            $vTitleWidth = $ATitleWidth;

        if ( $AIndentation )
            $vText = $this->SplitText($AText, array($AWidth-$vTitleWidth));
        else
            $vText = $this->SplitText($AText, array($AWidth-$vTitleWidth, $AWidth));

        $ARows = max( count($vText), $ARows );
        $vTextWidth = $AWidth-$vTitleWidth;

        $vX = $this->GetX();
        for( $i=0; $i<$ARows; $i++ )
        {
            if ( $i==0 )
                $this->Cell($vTitleWidth, $vHeight, $ATitle, 0, 0, 'L');

            $this->SetX($vX+$vTitleWidth);
            $this->Cell($vTextWidth, $vHeight, @$vText[$i], 'B', 0, 'L');
            $this->Ln();

            if ( !$AIndentation )
            {
                $vTitleWidth = 0;
                $vTextWidth = $AWidth; 
            }
        }
    }


    function BlockNotes($AList, $AWidth)
    {
        $vTitleWidth = 0;

        foreach($AList as $vNote)
        {
            $vTitle = @$vNote['title'];
            $vTitleWidth = max( $vTitleWidth, $this->GetStringWidth($vTitle) );
        }

        if ( $vTitleWidth>0 )
            $vTitleWidth += $this->GetStringWidth('o');

        foreach($AList as $vNote)
        {
            $vTitle = @$vNote['title'];
            $vText  = @$vNote['text'];
            $vRows  = max(@$vNote['rows'],1);
//            if ( empty($vRows) )
//                  $vRows = 1;
            $this->Notes($vTitle, $vTitleWidth, $AWidth, $vRows, $vText);
        }
    }


    function OutputTableRow(&$AWidths, $AHeight, &$ARowData, $AAligns)
    {
        $vNumCols     = count($AWidths);
        $vSplitedData = array();
        $vMaxLines = 1;

        for($i=0; $i<$vNumCols; $i++)
        {
            $vSplitedData[$i] = $this->SplitText( @$ARowData[$i], $AWidths[$i] );
            $vMaxLines = max($vMaxLines, count($vSplitedData[$i]));
        }
        $this->CheckSpace($AHeight*$vMaxLines);
        $vX = $this->GetX();
        $vY = $this->GetY();
        $vOffset = 0;
        for($i=0; $i<$vNumCols; $i++)
        {
            $this->SetXY($vX+$vOffset, $vY);
            $vWidth = $AWidths[$i];
            $this->Cell($vWidth,  $AHeight*$vMaxLines, '',  'LTRB', 0, '');
            $vAlign = is_array($AAligns)? @$AAligns[$i] : $AAligns;
            $this->SetX($vX+$vOffset);
            for($j=0; $j<$vMaxLines; $j++)
            {
                $vText = @$vSplitedData[$i][$j];
                $this->Cell($vWidth, $AHeight, $vText,  '', 2, $vAlign);
            }
            $vOffset += $vWidth;
        }
        $this->SetX($vX);
    }


    function Circle($x,$y,$r,$style='D')
    {
        $this->Ellipse($x,$y,$r,$r,$style);
    }


    function Ellipse($x,$y,$rx,$ry,$style='D')
    {
        if($style=='F')
            $op='f';
        elseif($style=='FD' or $style=='DF')
            $op='B';
        else
            $op='S';

        $lx=4/3*(M_SQRT2-1)*$rx;
        $ly=4/3*(M_SQRT2-1)*$ry;
        $k=$this->k;
        $h=$this->h;
        $this->_out(sprintf('%.2f %.2f m %.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x+$rx)*$k,($h-$y)*$k,
            ($x+$rx)*$k,($h-($y-$ly))*$k,
            ($x+$lx)*$k,($h-($y-$ry))*$k,
            $x*$k,($h-($y-$ry))*$k));
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x-$lx)*$k,($h-($y-$ry))*$k,
            ($x-$rx)*$k,($h-($y-$ly))*$k,
            ($x-$rx)*$k,($h-$y)*$k));
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x-$rx)*$k,($h-($y+$ly))*$k,
            ($x-$lx)*$k,($h-($y+$ry))*$k,
            $x*$k,($h-($y+$ry))*$k));
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c %s',
            ($x+$lx)*$k,($h-($y+$ry))*$k,
            ($x+$rx)*$k,($h-($y+$ly))*$k,
            ($x+$rx)*$k,($h-$y)*$k,
            $op));
    }


    function ExactCell($AHeight=0, $AString='')
    {
        $vWidth = $this->GetStringWidth($AString);
        $this->Cell($vWidth, $AHeight, $AString);
        return $vWidth;
    }

    function EAN13($x,$y,$barcode,$h=16,$w=.35)
    {
        $this->Barcode($x,$y,$barcode,$h,$w,13);
    }


        function UPC_A($x,$y,$barcode,$h=16,$w=.35)
    {
      $this->Barcode($x,$y,$barcode,$h,$w,12);
    }


    function GetCheckDigit($barcode)
    {
      //Compute the check digit
      $sum=0;
      for($i=1;$i<=11;$i+=2)
          $sum+=3*$barcode{$i};
      for($i=0;$i<=10;$i+=2)
          $sum+=$barcode{$i};
      $r=$sum%10;
      if($r>0)
          $r=10-$r;
      return $r;
    }


    function TestCheckDigit($barcode)
    {
      //Test validity of check digit
      $sum=0;
      for($i=1;$i<=11;$i+=2)
          $sum+=3*$barcode{$i};
      for($i=0;$i<=10;$i+=2)
          $sum+=$barcode{$i};
      return ($sum+$barcode{12})%10==0;
    }


    function Barcode($x,$y,$barcode,$h,$w,$len)
    {
      //Padding
      $barcode=str_pad($barcode,$len-1,'0',STR_PAD_LEFT);
      if($len==12)
        $barcode='0'.$barcode;
      //Add or control the check digit
      if(strlen($barcode)==12)
        $barcode.=$this->GetCheckDigit($barcode);
      elseif(!$this->TestCheckDigit($barcode))
        $this->Error('Incorrect check digit');
      //Convert digits to bars
      $codes=array(
          'A'=>array(
              '0'=>'0001101','1'=>'0011001','2'=>'0010011','3'=>'0111101','4'=>'0100011',
              '5'=>'0110001','6'=>'0101111','7'=>'0111011','8'=>'0110111','9'=>'0001011'),
          'B'=>array(
              '0'=>'0100111','1'=>'0110011','2'=>'0011011','3'=>'0100001','4'=>'0011101',
              '5'=>'0111001','6'=>'0000101','7'=>'0010001','8'=>'0001001','9'=>'0010111'),
          'C'=>array(
              '0'=>'1110010','1'=>'1100110','2'=>'1101100','3'=>'1000010','4'=>'1011100',
              '5'=>'1001110','6'=>'1010000','7'=>'1000100','8'=>'1001000','9'=>'1110100')
          );
      $parities=array(
          '0'=>array('A','A','A','A','A','A'),
          '1'=>array('A','A','B','A','B','B'),
          '2'=>array('A','A','B','B','A','B'),
          '3'=>array('A','A','B','B','B','A'),
          '4'=>array('A','B','A','A','B','B'),
          '5'=>array('A','B','B','A','A','B'),
          '6'=>array('A','B','B','B','A','A'),
          '7'=>array('A','B','A','B','A','B'),
          '8'=>array('A','B','A','B','B','A'),
          '9'=>array('A','B','B','A','B','A')
          );
      $code='101';
      $p=$parities[$barcode{0}];
      for($i=1;$i<=6;$i++)
          $code.=$codes[$p[$i-1]][$barcode{$i}];
      $code.='01010';
      for($i=7;$i<=12;$i++)
          $code.=$codes['C'][$barcode{$i}];
      $code.='101';
      //Draw bars
      for($i=0;$i<strlen($code);$i++)
      {
        if($code{$i}=='1')
          $this->Rect($x+$i*$w,$y,$w,$h,'F');
      }
      //Print text uder barcode
      $this->SetFont('Arial','',12);
      $this->Text($x,$y+$h+11/$this->k,substr($barcode,-$len));
    }

  }

?>