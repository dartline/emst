<?php
  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################


require_once './library/fpdfex.php';
require_once './library/cases_table.php';


    class TPDF_Ill_Doc extends FPDFEx
    {
        function TPDF_Ill_Doc()
        {
            $this->FPDFEx('Листок нетрудоспособности');
            $this->Open();
        }


        function Render($AID)
        {
            $vData = $this->LoadData($AID);
//            $this->DocTitle = Trim('№ '.$vData['case_id'].' / '.$vData['case']['last_name'].' '.$vData['case']['first_name'][0].'.'.$vData['case']['patr_name'][0].'.');
            $this->SetMargins(0,0,0);
            $this->SetAutoPageBreak(true, 30);
            $this->FirstPage($vData);
        }


        function LoadData($AID)
        {
            $vDB = GetDB();
            $vSurgery = false;
            if ( !empty($AID) )
              $vSurgery =& $vDB->GetById('emst_surgeries', $AID);
            if ( !is_array($vSurgery) )
              $vSurgery = array();

            $vCaseID = @$vSurgery['case_id'];
            if ( !empty($vCaseID) )
              $vCase =& $vDB->GetById('emst_cases', $vCaseID);
            if ( !is_array($vCase) )
              $vCase = array();
            $vSurgery['case'] =& $vCase;

            return $vSurgery;
        }


        function FirstPage($ASurgery)
        {
            $this->AddPage();

            $this->SetFont('arial_rus','',10);
            $vWidth  = $this->GetAreaWidth();
            $vHeight = $this->FontSize;

//            $this->Image('images/ill.jpeg', 0, 0, 140, 207);

            if ( $ASurgery['ill_doc_is_continue'] )
                $this->MarkContinue();
            else
                $this->MarkPrimary();

            $vCase =& $ASurgery['case'];

            if ( DateIsEmpty($ASurgery['ill_beg_date']) && 
                 !DateIsEmpty($vCase['disability_from_date']) )
            {
                $ASurgery['ill_beg_date'] = $vCase['disability_from_date'];
            }
            else if ( !DateIsEmpty($ASurgery['ill_beg_date']) && 
                 DateIsEmpty($vCase['disability_from_date']) )
            {
                $vCase['disability_from_date'] = $ASurgery['ill_beg_date'];
            }

//            print_r($ASurgery);

//            $vName = trim($vCase['last_name'].' '.$vCase['first_name'].' '.$vCase['patr_name']);
            $vName = iconv('utf-8', 'cp1251', FormatNameEx($vCase));
            $this->Text(18, 19, $vName);

            $vDocName = iconv('utf-8', 'cp1251',FormatUserName($ASurgery['user_id']));
            $this->Text(95, 19, $vDocName);

            $vAddress = iconv('utf-8', 'cp1251', @FormatAddress($vCase['addr_reg_street'], $vCase['addr_reg_num'], $vCase['addr_reg_subnum'], $vCase['addr_reg_apartment']));
            $this->Text(18, 24, $vAddress);
            $this->Text(120, 24, $vCase['id']);
            $this->Text(18, 29, $vCase['employment_place']);
            $vDate = explode(' ',iconv('utf-8', 'cp1251',Date2ReadableLong($ASurgery['ill_beg_date'])));
            if ( !empty($vDate[2]) )
                $vDate[2] = substr($vDate[2], -2);
            $this->Text(27, 34, @($vDate[0].' '.$vDate[1]));
            $this->Text(75, 34, @($vDate[2]));

// -----------------------------------------------------------------

            $vIllFromDate = iconv('utf-8', 'cp1251', Date2ReadableLong($vCase['disability_from_date']));
            if ( !empty($vIllFromDate) )
            $this->Text(80, 48, 'C '.$vIllFromDate);
//          $this->Text(15, 59, 'СПб ГУЗ ГП №51, Космонавтов 35');
            $vBranchInfo = GetBranchInfo();
            $this->Text(15, 59, @$vBranchInfo['ill_doc_name']);

            $this->Text(24, 63.5, $vDate[0].' '.$vDate[1]);
            $this->Text(70, 63.5, $vDate[2]);
            $this->Text(15, 69, $vName);
            $this->Text(110, 69, CalcAge($vCase['born_date'], $ASurgery['ill_beg_date']));
            if ( $vCase['is_male'] )
                $this->Ellipse(124, 68, 3, 2);
            else
                $this->Ellipse(131, 68, 3, 2);
            $this->Text(15, 73.5, $vCase['employment_place']);
        }


        function MarkPrimary()
        {
            $this->SetLineWidth(0.7);
            $this->Line(27, 14, 38, 14);
            $this->Line(14, 52.5, 25, 52.5);
        }


        function MarkContinue()
        {
            $this->SetLineWidth(0.7);
            $this->Line(41, 14, 55, 14);
            $this->Line(28, 52.5, 42, 52.5);
        }


        function OutName($AName)
        {
            $vHeight = $this->FontSize;
        }



    }


    $vID = @$_GET['id'];
    $vDoc = new TPDF_Ill_Doc;
    $vDoc->Render($vID);

    while (ob_get_level())
        ob_end_clean();

    header('Accept-Ranges: bytes');
    $vDoc->Output('Ill_Doc.pdf', 'I');
?>
