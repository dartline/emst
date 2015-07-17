<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
require_once 'library/fpdfex.php';
require_once 'library/cases_table.php';


    function ConstructQuery(&$ADB, $AParams)
    {
        $vFilter = array();

        $vTable = 'emst_surgeries';

        if ( array_key_exists('beg_date', $AParams) )
           $vFilter[] = $ADB->CondGE('date', $AParams['beg_date']);
        if ( array_key_exists('end_date', $AParams) )
           $vFilter[] = $ADB->CondLT('date', DateAddDay($AParams['end_date']));

        $vFilter = implode(' AND ', $vFilter);
//        $vFilter = '(case_id = 11619) or (case_id = 11591)';
        $vOrder = 'case_id, date, id';
        return array($vTable, $vFilter, $vOrder);
    }


    class TPDF_Stats extends FPDFEx
    {
        function TPDF_Stats()
        {
            $this->FPDFEx('Stats', 'P');
            $this->Open();
            $this->SetMargins(10,10,10);
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
                $this->Cell($vWidth, $vHeight, 'Список стат. талонов', '', 0, 'C');
                $this->SetX($vX);
                $this->Cell($vWidth, $vHeight, 'стр. '.$this->PageNo(), '', 0, 'R');
                $this->Ln($vHeight);

                $this->Cell($vWidth, $vHeight, 'за период с '.$this->BegDate.' г. по '.$this->EndDate.' г.', '', 0, 'C');
                $this->Ln($vHeight*2);
            }
            else
            {
                $this->Cell($vWidth, $vHeight, 'Список стат. талонов за период с '.$this->BegDate.' г. по '.$this->EndDate.' г. / стр.'.$this->PageNo(), '', 0, 'R');
                $this->Ln($vHeight*2);
            }
        }


        function Render($AParams)
        {
            $vDB = GetDB();
            list($vTable, $vFilter, $vOrder) = ConstructQuery($vDB, $AParams);
            $this->BegDate = Date2ReadableLong($AParams['beg_date']);
            $this->EndDate = Date2ReadableLong($AParams['end_date']);
            $this->AddPage();

            $vCaseID    = 'not an id';
            $vSurgeries = array();
            $vRecords   = $vDB->Select($vTable, '*', $vFilter, $vOrder);

            while( $vRecord = $vRecords->Fetch() )
            {
                $vRecCaseID = $vRecord['case_id'];
                if ( $vCaseID !== $vRecCaseID )
                {
                    if ( count($vSurgeries) > 0 )
                    {
                        $this->OutCard($vCaseID, $vSurgeries);
                        $vSurgeries = array();
                    }
                    $vCaseID = $vRecCaseID;
                }
                $vSurgeries[] = $vRecord;
            }

            if (  count($vSurgeries) > 0 )
            {
                $this->OutCard($vCaseID, $vSurgeries);
            }
        }


        function OutCard($ACaseID, &$ASurgeries)
        {
            $vDB = GetDB();
            $vCase = $vDB->GetById('emst_cases', $ACaseID);

            $this->SetFont('arial_rus','',10);
            $vHeight = $this->FontSize*1.5;
            $vWidth  = $this->GetAreaWidth();

//            $vName = trim(@$vCase['last_name'].' '.@$vCase['first_name'].' '.@$vCase['patr_name']);
            $vName = FormatNameEx($vCase);

            $this->Cell(20, $vHeight, 'Ф.И.О.');
            $this->Cell(70, $vHeight, $vName, 'B');
            $this->ExactCell($vHeight, '  И.Б. №  ');
            $this->Cell(15, $vHeight, $ACaseID, 'B');
//            $this->Ln($vHeight);
            $this->ExactCell($vHeight, '  пол  ');
            $this->Cell(5, $vHeight, FormatSex(@$vCase['is_male']), 'B');
            $this->ExactCell($vHeight, '  дата рождения  ');
            $this->Cell(30, $vHeight, Date2Readable(@$vCase['born_date']), 'B');
            $this->Ln($vHeight);

            $this->Cell(20, $vHeight, 'Категория');
            $this->Cell(20, $vHeight, '11-прочее', 'B');
            $this->ExactCell($vHeight, '  работабщий  ');
            $this->Cell(10, $vHeight, FormatBoolean((@$vCase['employment_category_id']) == 1), 'B');
            $this->Ln($vHeight);
            $this->Cell(20, $vHeight, 'Документ');
            $this->Cell(70, $vHeight, FormatDocument(@$vCase['doc_type_id'], @$vCase['doc_series'], @$vCase['doc_number']), 'B');
            $this->Ln($vHeight);
            $this->Cell(20, $vHeight, 'Полис');
            $this->Cell(70, $vHeight, FormatPolis(@$vCase['insurance_company_id'], @$vCase['polis_series'], @$vCase['polis_number']), 'B');
            $this->Ln($vHeight);
            $this->Cell(20, $vHeight, 'Адр.рег.');
            $this->Cell(70, $vHeight, FormatAddress(@$vCase['addr_reg_street'],  @$vCase['addr_reg_num'],  @$vCase['addr_reg_subnum'],  @$vCase['addr_reg_apartment']),'B');
            $this->Ln($vHeight);
            $this->Cell(20, $vHeight, 'Адр.факт.');
            $this->Cell(70, $vHeight, FormatAddress(@$vCase['addr_phys_street'], @$vCase['addr_phys_num'], @$vCase['addr_phys_subnum'], @$vCase['addr_phys_apartment']),'B');
            $this->Ln($vHeight);


            $vCount = count($ASurgeries);
            if ( $vCount > 0 )
                $vLast = $ASurgeries[$vCount-1];
            else
                $vLast = array();

            $this->Cell(20, $vHeight, 'Цель');
            $this->Cell(30, $vHeight, '1-Леч.-диагн.','B');
            $this->Cell(20, $vHeight, 'Случай');
            $this->Cell(30, $vHeight, '1-Первичный','B');
            $this->Cell(30, $vHeight, 'Законченность');
            $this->Cell(10, $vHeight, FormatBoolean(!empty($vLast['clinical_outcome_id'])), 'B');
            $this->Ln($vHeight);
            $this->Cell(20, $vHeight, 'Исход');
            $this->Cell(70, $vHeight, FormatClinicalOutcome(@$vLast['clinical_outcome_id'], @$vLast['clinical_outcome_notes']), 'B');
            $this->Ln($vHeight);
            $this->Cell(20, $vHeight, 'Диагноз');
            $vTmp = $vCase['diagnosis'];
            if ( strlen($vTmp)>80 )
                $vTmp = substr($vTmp,0,80-3).'...';
            $this->Cell(140, $vHeight, $vTmp, 'B');
            $this->Cell(10, $vHeight, 'МКБ');
            $this->Cell(20, $vHeight, @$vCase['diagnosis_mkb'],'B');
            $this->Ln($vHeight);
            $this->Cell(20, $vHeight, 'Характер');
            $this->Cell(20, $vHeight, '1-Острое','B');
            $this->Cell(20, $vHeight, 'Травма');
            $this->Cell(60, $vHeight, FormatTraumaType(@$vCase['trauma_type_id']), 'B');
            $this->Ln($vHeight);
            $this->CheckSpace($vHeight*4);
            $this->Cell(40, $vHeight, 'ПОСЕЩЕНИЯ');
            $this->Ln($vHeight);
            $this->OutSurgery($vHeight,
                               array('№', 'Дата', 'Врач', 'Специальность', 'Цель', 'Место'),
                               'C');

            for( $i=0; $i<$vCount; $i++)
            {
                $vSurgery =& $ASurgeries[$i];
                $this->OutSurgery($vHeight,
                               array(1+$i,
                                     Date2Readable(ExtractWord($vSurgery['date'],' ',0)),
                                     FormatUserName($vSurgery['user_id']),
                                     'травматолог',
                                     '1-Леч.диагн.',
                                     '1-Амбулаторно'));
            }

            $vIllDocs = array();
            $vPrevIllDoc = null;
            for( $i=0; $i<$vCount; $i++)
            {
                $vSurgery =& $ASurgeries[$i];
                if ( !empty($vSurgery['ill_doc']) )
                {
                    if ( empty($vPrevIllDoc) ||
                         $vPrevIllDoc['ill_doc'] != $vSurgery['ill_doc'] ||
                         $vPrevIllDoc['ill_beg_date'] != $vSurgery['ill_beg_date'] ||
                         $vPrevIllDoc['ill_end_date'] != $vSurgery['ill_end_date'] )
                    {
                        $vIllDocs[] = $vSurgery;
                        $vPrevIllDoc =& $vSurgery;
                    }
                }
            }

            if ( count($vIllDocs) )
            {
                $this->CheckSpace($vHeight*4);
                $this->Cell(40, $vHeight, 'НЕТРУДОСПОСОБНОСТЬ');
                $this->Ln($vHeight);
                $this->OutIllDoc($vHeight,
                                 array('№', 'Док.', 'Врач', 'Повод', 'Дата откр.', 'Дата закр.', 'Кому', 'Пол', 'Возр.'),
                                 'C');
                for( $i=0; $i<count($vIllDocs); $i++)
                {
                    $vSurgery = $vIllDocs[$i];
                    $this->OutIllDoc($vHeight,
                               array(1+$i,
                                     '1-Б/Л',
                                     FormatUserName($vSurgery['user_id']),
                                     '1-Заб.',
                                     Date2Readable($vSurgery['ill_beg_date']),
                                     Date2Readable($vSurgery['ill_end_date']),
                                     '1-Пац',
                                     FormatSex(@$vCase['is_male']),
                                     CalcAge(@$vCase['born_date'], $vSurgery['ill_beg_date'])
                                    ));

                }
                $this->Cell(20, $vHeight, 'Б/Л. ');
                $this->Cell(40, $vHeight, $vIllDocs[count($vIllDocs)-1]['ill_doc'],'B');
            }


//            $this->Ln($vHeight);
//            $this->Cell($vWidth, $vHeight, );
//            $this->Ln($vHeight);
//            $this->Cell($vWidth, $vHeight, Date2Readable(@$vCase['born_date']));
            $this->Ln($vHeight);
            $this->Ln($vHeight);
        }


        function OutSurgery($AHeight, $ARowData, $AAlign='')
        {
            $vCols   = array(12,  30, 60, 30, 30, 30);
            $vAligns = array('R','L','L','L','L','L');
            $this->OutTableRow($AHeight, $ARowData, $AAlign, $vCols, $vAligns);
        }

        function OutIllDoc($AHeight, $ARowData, $AAlign='')
        {
            $vCols   = array(12, 12, 60, 12, 30, 30, 12, 12, 12 );
            $vAligns = array('R','C','L','L','L','L','C','C','R');
            $this->OutTableRow($AHeight, $ARowData, $AAlign, $vCols, $vAligns);
        }



        function OutTableRow($AHeight, $ARowData, $AAlign, $ACols, $AAligns)
        {
            $vSplitedData = array();
            $vMaxLines = 1;

            for($i=0; $i<count($ACols); $i++)
            {
                $vSplitedData[$i] = $this->SplitText( @$ARowData[$i], $ACols[$i] );
                $vMaxLines = max($vMaxLines, count($vSplitedData[$i]));
            }
            $this->CheckSpace($AHeight*$vMaxLines);
            $vX = $this->GetX();
            $vY = $this->GetY();
            for($i=0; $i<count($ACols); $i++)
            {
                $this->Cell($ACols[$i],  $AHeight*$vMaxLines, '',  'LTRB', 0, '');
            }
            $this->SetXY($vX, $vY);
            for($j=0; $j<$vMaxLines; $j++)
            {
             for($i=0; $i<count($ACols); $i++)
                {
                    $vText = @$vSplitedData[$i][$j];
                    $this->Cell($ACols[$i],  $AHeight, $vText,  '', 0, empty($AAlign)?$AAligns[$i]:$AAlign);
                }
                $this->Ln($AHeight);
            }
        }

    }

// =======================================================================

    if ( !array_key_exists('beg_date', $_GET) )
      $_GET['beg_date'] = date('Y-m-d');
    if ( !array_key_exists('end_date', $_GET) )
      $_GET['end_date'] = date('Y-m-d');

    $vDoc = new TPDF_Stats;
    $vDoc->Render($_GET);

//    while (ob_get_level())
//        ob_end_clean();

    header('Accept-Ranges: bytes');
    $vDoc->Output('Stats.pdf', 'I');
?>
