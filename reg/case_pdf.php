<?php
  #####################################################################
  #
  # Травмпункт. (c) 2005, 2010 Vista
  #
  #####################################################################


require_once './library/fpdfex.php';
require_once './library/cases_table.php';

define('PGNO_FONT_SIZE', 8);
define('MAIN_FONT_SIZE', 10);


    function FormatSSI($ARequired, $ANumber)
    {
        if ( $ARequired )
        {
            if ( empty($ANumber) )
                return iconv('utf-8', 'cp1251','требуется тефонограмма, передача не отмечена');
            else
                return $ANumber;
        }
        else
            return iconv('utf-8', 'cp1251',"нет");
    }


    class TPDF_Case extends FPDFEx
    {
        private $_ForceAddPage = false;

        function TPDF_Case()
        {
            $this->FPDFEx('case');
            $this->Open();
        }

        function Header()
        {
            $vBranchInfo = GetBranchInfo();
            $vX = $this->GetX();
            $vY = $this->GetY();
            $vWidth  = $this->GetAreaWidth();

            $this->SetFont('arial_rus','',PGNO_FONT_SIZE);
            $vHeight = $this->FontSize*1.5;
            $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251',@$vBranchInfo['name']), 'B', 0, 'L');
            $this->SetXY($vX, $vY);
            $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','стр.').$this->PageNo(). iconv('utf-8', 'cp1251',' / '). $this->DocTitle, '', 0, 'R');
            $this->Ln($vHeight*2);
        }

        function ForceAddPage()
        {
            $this->_ForceAddPage = true;
        }


        function CheckAddPage()
        {
            if ( $this->_ForceAddPage )
            {
                $this->AddPage();
//                $this->_ForceAddPage = false;
            }
        }

        function AddPage()
        {
            $this->_ForceAddPage = false;
            parent::AddPage();
        }


        function Render($AID)
        {
            $vCase = $this->LoadData($AID);
//            $this->DocTitle = Trim(FormatShortName(@$vCase['last_name'], @$vCase['first_name'], @$vCase['patr_name']).' / № '.$vCase['id']);
            $this->DocTitle = Trim(FormatShortNameEx($vCase).iconv('utf-8', 'cp1251',' / № ').iconv('utf-8', 'cp1251',$vCase['id']));
            $this->SetMargins(20,3,20);
            $this->FirstPage($vCase);

            $vSequence = $vCase['sequence'];
            $vN = count($vSequence);
            $vCECNumber = 0;

            for( $i=0; $i<$vN; $i++ )
            {
                $vItemDescr =& $vSequence[$i];
                list($vDate, $vItemType, $vItemID, $vItemIndex) = explode('|', $vItemDescr);
                switch( $vItemType )
                {
                case '3ho':
                    $this->HospitalPage($vCase['hospitals'][$vItemIndex]);
                    break;
                case '2rg':
                    $this->RGPage($vCase['rgs'][$vItemIndex]);
                    break;
                case '1su':
                    $this->SurgeryPage($vCase, $vCase['surgeries'][$vItemIndex], $vItemIndex, $vCECNumber);
                    break;
                default:
                    $this->UnknownPage($vItemDescr);
                }
            }
        }


        function LoadData($AID)
        {
            $vDB = GetDB();
            if ( !empty($AID) )
              $vCase =& $vDB->GetById('emst_cases', $AID);
            if ( !is_array($vCase) )
              $vCase = array();

            $vSurgeries =& $vDB->SelectList('emst_surgeries',                   // table
                                             '*',                               // cols
                                             $vDB->CondEqual('case_id', $AID),  // where
                                             'date, id');                       // order
            $vCase['surgeries'] =& $vSurgeries;

            $vRGs =& $vDB->SelectList('emst_rg','*',$vDB->CondEqual('case_id', $AID),'date, id');
            $vCase['rgs'] =& $vRGs;
            $vHospitals   =& $vDB->SelectList('emst_hospitals','*',$vDB->CondEqual('case_id', $AID),'beg_date, id');
            $vCase['hospitals'] =& $vHospitals;

            $vSequence = array();
            $vN = count($vSurgeries);
            for( $i=0; $i<$vN; $i++ )
            {
                $vItem =& $vSurgeries[$i];
                $vSequence[] = ExtractWord($vItem['date'], ' ', 0).'|1su|'.$vItem['id'].'|'.$i;
            }
            $vN = count($vRGs);
            for( $i=0; $i<$vN; $i++ )
            {
                $vItem =& $vRGs[$i];
                $vSequence[] = $vItem['date'].'|2rg|'.$vItem['id'].'|'.$i;
            }
            $vN = count($vHospitals);
            for( $i=0; $i<$vN; $i++ )
            {
                $vItem =& $vHospitals[$i];
                $vSequence[] = $vItem['beg_date'].'|3ho|'.$vItem['id'].'|'.$i;
            }

            sort($vSequence);
            $vCase['sequence'] =& $vSequence;
            return $vCase;
        }


        function FirstPage($AData)
        {
            $this->AddPage();
            $vDB = GetDB();
            $vWidth  = $this->GetAreaWidth();
            $this->SetFont('arial_rus','',MAIN_FONT_SIZE);
            $vHeight = $this->FontSize*1.5;

            $vNumSurgeries = @count($AData['surgeries']);

            $vBlock = array();
            $vBlock[] = array( 'title'=> iconv('utf-8', 'cp1251','История болезни №'),    'text'=>@$AData['id']);
            //$vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Дата и время обращения'),'text'=>iconv('utf-8', 'cp1251',Date2ReadableLong(@$AData['create_time'])));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Ф.И.О.'),               'text'=>iconv('utf-8', 'cp1251',FormatNameEx($AData)));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Дата рождения'),        'text'=>iconv('utf-8', 'cp1251',FormatBornDateAndAgeLong(@$AData['create_time'], @$AData['born_date'])));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Пол'),                  'text'=>(@$AData['is_male'])?iconv('utf-8', 'cp1251','мужской'):iconv('utf-8', 'cp1251','женский'));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Адрес регистрации'),    'text'=>iconv('utf-8', 'cp1251',FormatAddress(@$AData['addr_reg_street'], @$AData['addr_reg_num'], @$AData['addr_reg_subnum'], @$AData['addr_reg_apartment']) ));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Адрес проживания'),     'text'=>iconv('utf-8', 'cp1251',FormatAddress(@$AData['addr_phys_street'], @$AData['addr_phys_num'], @$AData['addr_phys_subnum'], @$AData['addr_phys_apartment']) ));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Документ'),             'text'=>iconv('utf-8', 'cp1251',FormatDocument(@$AData['doc_type_id'], @$AData['doc_series'], @$AData['doc_number']) ));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Телефон(ы)'),           'text'=>iconv('utf-8', 'cp1251',@$AData['phone']));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Категория'),            'text'=>iconv('utf-8', 'cp1251',FormatCategory(@$AData['employment_category_id'])));

            $vCategory = $vDB->GetByID(iconv('utf-8', 'cp1251','rb_employment_categories'), iconv('utf-8', 'cp1251',@$AData['employment_category_id']));
            if ( @$vCategory['need_ill_doc'] ) 
            {
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Место работы'),         'text'=>iconv('utf-8', 'cp1251',@$AData['employment_place']));
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Профессия'),            'text'=>iconv('utf-8', 'cp1251',@$AData['profession']));
            }

            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Полис'),                'text'=>iconv('utf-8', 'cp1251',FormatPolis(@$AData['insurance_company_id'], @$AData['polis_series'], @$AData['polis_number'])));
	     $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Врач'),                'text'=>iconv('utf-8', 'cp1251',FormatUserName($AData['surgeries'][0]['user_id'])));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Дата обращения'),                'text'=>iconv('utf-8', 'cp1251',Date2ReadableLong($AData['create_time'])));
            if ( $vNumSurgeries>1 )
            {
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Предварительный диагноз'), 'text'=>iconv('utf-8', 'cp1251',@$AData['diagnosis']));
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Уточнённый диагноз'),  'text'=>($vNumSurgeries>0? iconv('utf-8', 'cp1251',@$AData['surgeries'][$vNumSurgeries-1]['diagnosis']): ''));
            }
            else
            {
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Диагноз'), 'text'=>iconv('utf-8', 'cp1251',@$AData['diagnosis']));
            }

            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Код диагноза по МКБ'), 'text'=>iconv('utf-8', 'cp1251',@$AData['diagnosis_mkb']));
            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Тип травмы'),          'text'=>iconv('utf-8', 'cp1251',FormatTraumaType(@$AData['trauma_type_id'])));

            if ( !empty($AData['notes']) )
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Доп. сведения'), 'text'=>iconv('utf-8', 'cp1251',@$AData['notes']));

            if ( $vNumSurgeries>0 )
            {
                $vFirstSurgery = $AData['surgeries'][0];
                $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Трудоспособность'), 'text'=>iconv('utf-8', 'cp1251',FormatDisability($vFirstSurgery['disability'])));
                if ( $vFirstSurgery['disability'] == 2 )
                {
                    $vIllDocText='';
                    $vIllDocTextTill='';
                    if ( $vFirstSurgery['ill_doc_closed'] )
                    {    
                        $vDate=explode(' ',iconv('utf-8', 'cp1251', Date2ReadableLong($vFirstSurgery['date'])));
                        $vIllDocText= iconv('utf-8', 'cp1251',' (закрыт)');
                        $vIllDocTextTill=iconv('utf-8', 'cp1251',' по ').
                        $vDate[0]." $vDate[1]"." $vDate[2]".
                        iconv('utf-8', 'cp1251',' (дней ').        
                        (1+ DateDiff(iconv('utf-8', 'cp1251',$vFirstSurgery['date']),$AData['disability_from_date'])).iconv('utf-8', 'cp1251',')');
    
                    }
                    $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Листок нетрудоспособности'), 'text'=>iconv('utf-8', 'cp1251',$vFirstSurgery['ill_doc']).$vIllDocText.iconv('utf-8', 'cp1251',' c ').iconv('utf-8', 'cp1251',Date2ReadableLong($AData['disability_from_date'])).$vIllDocTextTill);
                    $vCureUpdateRange = iconv('utf-8', 'cp1251','  с ').
                                iconv('utf-8', 'cp1251',Date2ReadableLong($vFirstSurgery['ill_beg_date'])).
                                iconv('utf-8', 'cp1251',' по ').
                                iconv('utf-8', 'cp1251',Date2ReadableLong($vFirstSurgery['ill_end_date'])).
                                iconv('utf-8', 'cp1251',' (дней ').
                                (1+ DateDiff(iconv('utf-8', 'cp1251',$vFirstSurgery['ill_end_date']), $vFirstSurgery['ill_beg_date'])).iconv('utf-8', 'cp1251',')');
                    if (! empty($vFirstSurgery['ill_doc_new']))
                    
                        $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Выдано продолжение'), 'text'=>iconv('utf-8', 'cp1251',$vFirstSurgery['ill_doc_new']).$vCureUpdateRange);
                         
                     }

            }

            $this->BlockNotes($vBlock, $vWidth);
            $this->Ln($vHeight);

            $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','Со слов пострадавшего'));
            $this->Ln($vHeight);
            $this->BlockNotes(
              array(
                array( 'title'=>iconv('utf-8', 'cp1251','что произошло'),             'text'=>iconv('utf-8', 'cp1251',@$AData['accident'])),
                array( 'title'=>iconv('utf-8', 'cp1251','дата и время происшествия'), 'text'=>iconv('utf-8', 'cp1251',Date2ReadableLong(@$AData['accident_datetime']))),
                array( 'title'=>iconv('utf-8', 'cp1251','место происшествия'),        'text'=>iconv('utf-8', 'cp1251',@($AData['accident_place']))),
                ),
              $vWidth);
            $this->Ln($vHeight);
            $vMessages = array();
            if ( @$AData['phone_message_required'] )
                $vMessages[] = array( 'title'=>iconv('utf-8', 'cp1251','Телефонограмма'), 'text'=>FormatSSI(iconv('utf-8', 'cp1251',@$AData['phone_message_required']), iconv('utf-8', 'cp1251',@$AData['message_number'])));
            if ( @$AData['ice_trauma'] )
                $vMessages[] = array( 'title'=>iconv('utf-8', 'cp1251','Гололёд'),        'text'=>FormatBoolean(iconv('utf-8', 'cp1251',@$AData['ice_trauma'])));
            if ( @$AData['animal_bite_trauma'] )
                $vMessages[] = array( 'title'=>iconv('utf-8', 'cp1251','Укус животного'), 'text'=>FormatSSI(iconv('utf-8', 'cp1251',@$AData['animal_bite_trauma']), iconv('utf-8', 'cp1251',@$AData['message_number'])));
            if ( @$AData['ixodes_trauma'] )
                $vMessages[] = array( 'title'=>iconv('utf-8', 'cp1251','Укус клеща'),     'text'=>FormatSSI(iconv('utf-8', 'cp1251',@$AData['ixodes_trauma']), iconv('utf-8', 'cp1251',@$AData['message_number'])));
            if ( !empty($AData['antitetanus_id']) || !empty($AData['antitetanus_series']) )
                $vMessages[] = array( 'title'=>iconv('utf-8', 'cp1251','профилактика столбняка'),    'text'=>iconv('utf-8', 'cp1251',FormatAntitetanus(@$AData['antitetanus_id'], @$AData['antitetanus_series'])));
            $this->BlockNotes($vMessages, $vWidth);
            $this->Ln($vHeight);
        }


        function HospitalPage(&$AHospital)
        {
            $this->CheckAddPage();
            $this->SetFont('arial_rus','',MAIN_FONT_SIZE);
            $vHeight = $this->FontSize*1.5;
            $vWidth = $this->GetAreaWidth();
            $this->CheckSpace($vHeight*7);
            $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','Сведения из стационара'));
            $this->Ln($vHeight);

            $this->BlockNotes(
              array(
                array( 'title'=>iconv('utf-8', 'cp1251','Период'),       'text'=>iconv('utf-8', 'cp1251','C ').iconv('utf-8', 'cp1251',Date2ReadableLong(@$AHospital['beg_date'])).iconv('utf-8', 'cp1251',' по ').iconv('utf-8', 'cp1251',Date2ReadableLong(@$AHospital['end_date']))),
                array( 'title'=>iconv('utf-8', 'cp1251','Наименование'), 'text'=>iconv('utf-8', 'cp1251',@$AHospital['name'])),
                array( 'title'=>iconv('utf-8', 'cp1251','Диагноз'),      'text'=>iconv('utf-8', 'cp1251',@$AHospital['diagnosis'])),
                array( 'title'=>iconv('utf-8', 'cp1251','Операция'),     'text'=>iconv('utf-8', 'cp1251',@$AHospital['operation'])),
                array( 'title'=>iconv('utf-8', 'cp1251','Рекомендации'), 'text'=>iconv('utf-8', 'cp1251',@$AHospital['recommendation'])),
                array( 'title'=>iconv('utf-8', 'cp1251','Доп. сведения'),'text'=>iconv('utf-8', 'cp1251',@$AHospital['notes']))
                ),
              $vWidth);
            $this->Ln($vHeight);
        }


        function RGPage(&$ARG)
        {
            $this->CheckAddPage();
            $this->SetFont('arial_rus','',MAIN_FONT_SIZE);
            $vHeight = $this->FontSize*1.5;
            $vWidth = $this->GetAreaWidth();
            $this->CheckSpace($vHeight*7);
            $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','Результаты рентгенологического исследования'));
            $this->Ln($vHeight);
            $this->BlockNotes(
              array(
                array( 'title'=>iconv('utf-8', 'cp1251','Дата'),               'text'=>iconv('utf-8', 'cp1251',Date2ReadableLong(@$ARG['date']))),
                array( 'title'=>iconv('utf-8', 'cp1251','Область'),            'text'=>iconv('utf-8', 'cp1251',@$ARG['area'])),
                array( 'title'=>iconv('utf-8', 'cp1251','Описание'),           'text'=>iconv('utf-8', 'cp1251',@$ARG['description']))
                ),
              $vWidth);
            $this->Ln($vHeight);
        }

function CECPage(&$ACase, &$ASurgery, $AIdx, $ACECNumber)
        {    
            $this->CheckAddPage();
            $this->SetFont('arial_rus','',MAIN_FONT_SIZE);
            $vWidth = $this->GetAreaWidth();
            $vHeight = $this->FontSize*1.5;

            $this->AddPage();
            $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251',Date2ReadableLong(@$ASurgery['date'])));
            $this->Ln($vHeight);
            $this->Cell($vWidth, $vHeight,iconv('utf-8', 'cp1251', 'Эпикриз на ВК № ').$ACECNumber);
            $this->Ln($vHeight);

            $vSurgeryDate = @ExtractWord($ASurgery['date'],' ',0);
            $vIllDocText='';
            if ( $ASurgery['ill_doc_closed'] )
                  $vIllDocText= iconv('utf-8', 'cp1251',' (закрыт)');

            $vBlock = array(
                array( 'title'=>iconv('utf-8', 'cp1251','Дата'),                      'text'=>iconv('utf-8', 'cp1251',Date2ReadableLong($vSurgeryDate))),
                array( 'title'=>iconv('utf-8', 'cp1251','Ф.И.О.'),                    'text'=>iconv('utf-8', 'cp1251',@($ACase['last_name'].' '.$ACase['first_name'].' '.$ACase['patr_name']))),
                array( 'title'=>iconv('utf-8', 'cp1251','Дата рождения'),             'text'=>iconv('utf-8', 'cp1251',FormatBornDateAndAgeLong($vSurgeryDate, @$ACase['born_date']))),
                array( 'title'=>iconv('utf-8', 'cp1251','Пол'),                       'text'=>(@$ACase['is_male'])?iconv('utf-8', 'cp1251','мужской'):iconv('utf-8', 'cp1251','женский')),
                array( 'title'=>iconv('utf-8', 'cp1251','Категория'),                 'text'=>iconv('utf-8', 'cp1251',FormatCategory(@$ACase['employment_category_id']))),
                array( 'title'=>iconv('utf-8', 'cp1251','Профессия'),                 'text'=>iconv('utf-8', 'cp1251',@$ACase['profession'])),
                array( 'title'=>iconv('utf-8', 'cp1251','Листок нетрудоспособности'), 'text'=>iconv('utf-8', 'cp1251',$ASurgery['ill_doc']).$vIllDocText.iconv('utf-8', 'cp1251',' c ').iconv('utf-8', 'cp1251',Date2ReadableLong($ACase['disability_from_date'])).iconv('utf-8', 'cp1251',' по ').iconv('utf-8', 'cp1251',Date2ReadableLong($vSurgeryDate)).iconv('utf-8', 'cp1251',' (дней ').iconv('utf-8', 'cp1251',(1+DateDiff($vSurgeryDate, $ACase['disability_from_date']))).iconv('utf-8', 'cp1251',')')),
                array( 'title'=>iconv('utf-8', 'cp1251','Диагноз'),                   'text'=>iconv('utf-8', 'cp1251',$ASurgery['diagnosis'])),
                array( 'title'=>iconv('utf-8', 'cp1251','Тип травмы'),                'text'=>iconv('utf-8', 'cp1251',FormatTraumaType(@$ACase['trauma_type_id']))),
                array( 'title'=>iconv('utf-8', 'cp1251','Жалобы'),                    'text'=>iconv('utf-8', 'cp1251',$ASurgery['complaints'])),
                array( 'title'=>iconv('utf-8', 'cp1251','Объективный статус'),        'text'=>iconv('utf-8', 'cp1251',$ASurgery['objective']))
                );
            if ( $AIdx != 0 )
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Динамика'), 'text'=>FormatDynamic(@$ASurgery['dynamic_id']));

            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Лечение'),      'text'=>iconv('utf-8', 'cp1251',@$ASurgery['cure']));

            if ( $AIdx == 0 && !empty($ASurgery['manipulation_id']) )
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Манипуляция'), 'text'=>FormatManipulation(@$ASurgery['manipulation_id'],@$ASurgery['manipulation_text']));

            if ( !empty($ASurgery['notes']) )
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Доп. сведения'),'text'=>iconv('utf-8', 'cp1251',@$ASurgery['notes']));
            $vCureUpdateRange = iconv('utf-8', 'cp1251','с ').
                                iconv('utf-8', 'cp1251',Date2ReadableLong(DateAddDay($vSurgeryDate))).
                                iconv('utf-8', 'cp1251',' по ').
                                iconv('utf-8', 'cp1251',Date2ReadableLong($ASurgery['cec_cureup_date'])).
                                iconv('utf-8', 'cp1251',' (дней ').
                                (DateDiff(iconv('utf-8', 'cp1251',$ASurgery['cec_cureup_date']), $vSurgeryDate)).iconv('utf-8', 'cp1251',')');
            $vNewDisMessage='';
            if (! empty($ASurgery['ill_doc_new']))
            {
                $vNewDisMessage=iconv('utf-8', 'cp1251','Выдано продолжение №');
            $vNewDisNumber=@$ASurgery['ill_doc_new'];
            }
            $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Нетрудоспособен'), 'text'=>iconv('utf-8', 'cp1251','Прошу продлить лечение ').$vCureUpdateRange."   $vNewDisMessage".$vNewDisNumber);
            $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Врач'),            'text'=>iconv('utf-8', 'cp1251',FormatUserName($ASurgery['user_id'])));
             
            $vBlock[] = array('title'=>'', 'text'=>'', 'rows'=>2);   
            
            $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Протокол ВК №'),  'text'=>iconv('utf-8', 'cp1251',$ASurgery['cec_number']).iconv('utf-8', 'cp1251',' от ').iconv('utf-8', 'cp1251',Date2ReadableLong($vSurgeryDate)));

            
            $vCECMembers = $ASurgery['cec_members'];
            if ( empty($vCECMembers) )
            {
                $vBranchInfo = GetBranchInfo();
                $vCECMembers = $vBranchInfo['cec_members'];
            }
            $vCECMembers = preg_split('/( *(\n|\r) *)+/', $vCECMembers);
            
                    
                    $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Комиссия в составе:'),  'text'=>iconv('utf-8', 'cp1251','председатель Колосков А.Р.,Мишин В.С.,Кузьмина О.Ю.'));
                    $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Члены ВК:'), 'text'=>iconv('utf-8', 'cp1251','Потапова А.П, Кузьмина О.Ю.,Козырева Л.П., Колосков А.Р.,Мишин В.С.'));
                    $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Обсуждаемые вопросы:'), 'text'=>iconv('utf-8', 'cp1251','продление листка нетрудоспособности свыше установленных законодательством сроков, направление на освидетельствование в бюро МСЭ,другие вопросы'));
                    $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Решение ВК:'), 'text'=>iconv('utf-8', 'cp1251','Временно нетрудоспособен(на),Л/Н №                                 продлен для лечения,окончания курса лечения,уточнения диагноза,оформления ф.088/у-06 и предоставления на МСЭ с            по               на        дней.'));
                    $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Дата явки на прием'));
                    $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Председатель подкомиссии'));
                    $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Члены ВК:'));
                    $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Дата следующей явки'), 'text'=>iconv('utf-8', 'cp1251',Date2ReadableLong($ASurgery['next_visit_date'])));
            $this->BlockNotes($vBlock, $vWidth);

//            $this->Ln($vHeight);
            $this->ForceAddPage();
        }



        function OrdinarSurgeryPage(&$ASurgery, $AIdx)
        {
            $this->CheckAddPage();
            $this->SetFont('arial_rus','',MAIN_FONT_SIZE);
            $vWidth = $this->GetAreaWidth();
            $vHeight = $this->FontSize*1.5;

            $this->CheckSpace($vHeight*7);
            $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251',Date2ReadableLong(@$ASurgery['date'])));
            $this->Ln($vHeight);

            $vBlock = array(
                array( 'title'=>iconv('utf-8', 'cp1251','Жалобы'),             'text'=>iconv('utf-8', 'cp1251',@$ASurgery['complaints'])),
                array( 'title'=>iconv('utf-8', 'cp1251','Объективный статус'), 'text'=>iconv('utf-8', 'cp1251',@$ASurgery['objective']))
                );

            if ( $AIdx != 0 )
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Динамика'),           'text'=>FormatDynamic(iconv('utf-8', 'cp1251',@$ASurgery['dynamic_id'])));


            $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Диагноз'),            'text'=>iconv('utf-8', 'cp1251',@$ASurgery['diagnosis']));
            if ( !empty($ASurgery['cure']) )
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Лечение'),            'text'=>iconv('utf-8', 'cp1251',@$ASurgery['cure']));

            if ( $AIdx == 0 && !empty($ASurgery['manipulation_id']) )
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Манипуляция'),        'text'=>FormatManipulation(iconv('utf-8', 'cp1251',@$ASurgery['manipulation_id']), iconv('utf-8', 'cp1251',@$ASurgery['manipulation_text'])));

            if ( !empty($ASurgery['notes']) )
                $vBlock[] = array( 'title'=>iconv('utf-8', 'cp1251','Доп. сведения'),      'text'=>iconv('utf-8', 'cp1251',@$ASurgery['notes']));
            if ( $ASurgery['disability'] && !empty($ASurgery['ill_doc']) )
            {
                $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Трудоспособность'), 'text'=>iconv('utf-8', 'cp1251',FormatDisability($ASurgery['disability'])));
                $vIllDocText = $ASurgery['ill_doc'];
                if ( $ASurgery['ill_doc_closed'] )
                  $vIllDocText .= iconv('utf-8', 'cp1251',' закрыт');
                if ( !empty($ASurgery['ill_doc_new']) )
                  $vIllDocText .= iconv('utf-8', 'cp1251',', выдано продолжение ') . $ASurgery['ill_doc_new'];
                $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Листок нетрудоспособности'), 'text'=>iconv('utf-8', 'cp1251',$vIllDocText));
                if ( !$ASurgery['ill_doc_closed'] ) 
                  $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Продлён'),          'text'=>iconv('utf-8', 'cp1251','с ').iconv('utf-8', 'cp1251',Date2ReadableLong($ASurgery['ill_beg_date'])).iconv('utf-8', 'cp1251',' по ').iconv('utf-8', 'cp1251',Date2ReadableLong($ASurgery['ill_end_date'])));
            }
            if ( !empty($ASurgery['clinical_outcome_id']) )
            {
               $vOutcomeText = FormatClinicalOutcome($ASurgery['clinical_outcome_id'], '');

               if ( $vOutcomeText == iconv('utf-8', 'cp1251','Выписан к труду') && $ASurgery['ill_end_date'] != '0000-00-00' )
                   $vOutcomeText .= iconv('utf-8', 'cp1251',' c ').iconv('utf-8', 'cp1251',Date2ReadableLong(DateAddDay($ASurgery['ill_end_date'])));
               $vOutcomeText .= ' ' .$ASurgery['clinical_outcome_notes'];
               $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Исход'), 'text'=>iconv('utf-8', 'cp1251',trim($vOutcomeText)));
            }
            else
               $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Дата следующей явки'), 'text'=>iconv('utf-8', 'cp1251',Date2ReadableLong($ASurgery['next_visit_date'])));
            $vBlock[] = array('title'=>iconv('utf-8', 'cp1251','Врач'),                'text'=>iconv('utf-8', 'cp1251',FormatUserName($ASurgery['user_id'])));
            $this->BlockNotes($vBlock, $vWidth);
            $this->Ln($vHeight*2);
        }


        function SurgeryPage(&$ACase, &$ASurgery, $AIdx, &$ACECNumber)
        {
            if ( $ASurgery['is_cec'] )
              $this->CECPage($ACase, $ASurgery, $AIdx, ++$ACECNumber);
            else
              $this->OrdinarSurgeryPage($ASurgery, $AIdx);
        }


        function UnknownPage($ADescr)
        {
            $this->CheckAddPage();
            $this->SetFont('arial_rus','',MAIN_FONT_SIZE);
            $vHeight = $this->FontSize*1.5;
            $vWidth = $this->GetAreaWidth();
            $this->Cell($vWidth, $vHeight, iconv('utf-8', 'cp1251','Ошибочный элемент sequence ').$ADescr);
            $this->Ln($vHeight);
        }

    }


    $vID = @$_GET['id'];
    $vDoc = new TPDF_Case;
    $vDoc->Render($vID);

    while (ob_get_level())
        ob_end_clean();

    header('Accept-Ranges: bytes');
    $vDoc->Output('case-'.$vID.'.pdf', 'I');
?>
