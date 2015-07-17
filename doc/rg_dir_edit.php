<?php

#####################################################################
#
# EMST:Травмпункт
# (c) 2005,2006 Vista
#
# Форма редактирования направления на RG
#
#####################################################################

require_once('./config/config.php');


function GetVal(&$ADefaults, &$AValues, $AName)
{
    if ( is_array($AValues) && array_key_exists($AName, $AValues) )
        return $AValues[$AName];
    if ( is_array($ADefaults) && array_key_exists($AName, $ADefaults) )
        return $ADefaults[$AName];
    return '';
}


class TEditor extends HTML_QuickFormEx
{
    function TEditor($AID, $ACaseID)
    {
        $vDB = GetDB();

        if ( !empty($_SESSION['_rg_dir_backup']) &&
             is_array($_SESSION['_rg_dir_backup']) &&
             ( $_SESSION['_rg_dir_backup']['id'] === $AID ||
               $_SESSION['_rg_dir_backup']['case_id'] === $ACaseID ) )
        {
           $vRecord = $_SESSION['_rg_dir_backup'];
           $AID     = $vRecord['id'];
           $ACaseID = $vRecord['case_id'];
        }
        else
        {
            if ( !empty($AID) )
            {
                $vRecord = $vDB->GetById('emst_rg', $AID);
                if ( !is_array($vRecord) )
                   $vRecord = array();
            }
            else
            {
                $vRecord = array();
                $vRecord['date']    = $vDB->ConvertToDate(time());
                $vRecord['user_id'] = $_SESSION['User.ID'];
                $vObjective = '';
                $vDiagnosis = '';
                // hack :(
                $vTmp = @$_SESSION['_docAccept_container'];
                if ( !empty($vTmp) )
                {
                    $vDatesCount = 0;
                    $vDates = $vTmp['defaults']['surgeries'];
                    if (  !empty($vDates) && is_array($vDates) )
                        $vDatesCount = count($vDates);
                    if ( $vDatesCount > 0 )
                    {
                        $vPageName = 'Date'.($vDatesCount-1);
                        $vDefaults = @$vTmp['defaults'][$vPageName];
                        $vValues   = @$vTmp['values'][$vPageName];
//                            $vObjective = GetVal($vDefaults, $vValues, 'objective');
                        $vDiagnosis = GetVal($vDefaults, $vValues, 'diagnosis');
                    }
                }
                // hack end
//                    $vRecord['objective'] = $vObjective;
                $vRecord['diagnosis'] = $vDiagnosis;
            }
        }

        if ( !empty($ACaseID) )
           $vRecord['case_id'] = $ACaseID;
        $vCaseID = @$vRecord['case_id'];
        if ( !empty($vCaseID) )
        {
            $vCase = $vDB->GetById('emst_cases', $vCaseID);
            if ( is_array($vCase) )
            {
               $vRecord['last_name']  = $vCase['last_name'];
               $vRecord['first_name'] = $vCase['first_name'];
               $vRecord['patr_name']  = $vCase['patr_name'];
               $vRecord['is_male']    = $vCase['is_male'];
               $vRecord['born_date']  = $vCase['born_date'];
               $vRecord['doc_type_id']= $vCase['doc_type_id'];
               $vRecord['doc_series'] = $vCase['doc_series'];
               $vRecord['doc_number'] = $vCase['doc_number'];
               $vRecord['addr_phys_street'] = $vCase['addr_phys_street'];
               $vRecord['addr_phys_num'] = $vCase['addr_phys_num'];
               $vRecord['addr_phys_subnum'] = $vCase['addr_phys_subnum'];
               $vRecord['addr_phys_apartment'] = $vCase['addr_phys_apartment'];
               $vRecord['phone'] = $vCase['phone'];
               $vRecord['employment_place'] = $vCase['employment_place'];
               $vRecord['profession'] = $vCase['profession'];
               $vRecord['insurance_company_id'] = $vCase['insurance_company_id'];
               $vRecord['polis_series'] = $vCase['polis_series'];
               $vRecord['polis_number'] = $vCase['polis_number'];
            }
        }
        $vRecord['html_referer'] = $_SESSION['PrevPage'];

        $this->HTML_QuickForm('frmItem', 'post', $_SERVER['REQUEST_URI']);
        $this->setMyRequiredNote();
        $this->addElement('header',   'header',          'Направление на рентгенологическое исследование');
        $this->addElement('hidden',   'id',              '');
        $this->addElement('hidden',   'user_id',         '');
        $this->addElement('hidden',   'html_referer',    '');

        $vElement =& $this->addElement('text',     'case_id',         'История болезни №', array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'last_name',       'Фамилия',          array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'first_name',      'Имя',              array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'patr_name',       'Отчество',         array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('select',   'is_male',         'Пол', array(0=>'Женский', 1=>'Мужской',2=>'-Не указан-'));
        $vElement->freeze();
        $vElement =& $this->addElement('dateex',   'born_date',       'Дата рождения', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>1900, 'maxYear'=>gMaxYear));
        $vElement->freeze();
        $vElement =& $this->addElement('select',   'doc_type_id',     'Документ', $vDB->GetRBList('rb_doc_types','id', 'name', true));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'doc_series',      'серия',         array('class'=>'edt_tiny'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'doc_number',      'номер',         array('class'=>'edt_100'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'addr_phys_street',   'Адрес пребывания',  array('class'=>'edt_norm'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'addr_phys_num',      'дом',               array('class'=>'edt_tiny'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'addr_phys_subnum',   'корп',              array('class'=>'edt_tiny'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'addr_phys_apartment','кв',                array('class'=>'edt_tiny'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'phone',           'Телефон',          array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'employment_place','Место работы',     array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'profession',      'Профессия',        array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('select',   'insurance_company_id','Полис: СМО',       $vDB->GetRBList('rb_insurance_companies','id', 'name', true));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'polis_series',        'серия',            array('class'=>'edt_tiny'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'polis_number',        'номер',            array('class'=>'edt_100'));
        $vElement->freeze();

        $this->addElement('dateex',   'date',            'Дата', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear));
//            $this->addElement('textarea', 'objective',       'Объективный статус',  array('rows' => 6, 'cols' => 44));
        $this->addElement('text',     'area',            'Область',   array('class'=>'edt_wide'));
        $this->addElement('textarea', 'diagnosis',       'Диагноз при направлении на RG',  array('rows' => 6, 'cols' => 44));
        $this->addElement('checkbox', 'done',            'Выполнено');
        $this->addElement('textarea', 'description',     'Описание',  array('rows' => 6, 'cols' => 44));

        $this->addElement('submit',   'do_Print',     'Печать', array('width'=>'100px'));
        $this->addElement('submit',   'do_Save',      'Ok', array('width'=>'100px'));
        $this->addElement('submit',   'do_Cancel',    'Отмена', array('width'=>'100px'));
        $this->applyFilter('_ALL_', 'trim');

        $this->setDefaults($vRecord);
    }



    function ActionDispatcher()
    {
        $vValues = $this->getSubmitValues();
        if ( array_key_exists('do_Print', $vValues) )
          return $this->PrintAction();
        elseif ( array_key_exists('do_Save', $vValues) )
          return $this->SaveAction();
        elseif ( array_key_exists('do_Cancel', $vValues) )
          return $this->CancelAction();
        else
          return false;
    }


    function SaveAction()
    {
        if ( $this->Save() )
            return 1;
        else
            return false;
    }


    function PrintAction()
    {
        if ( $this->Save() )
            return 2;
        else
            return false;
    }

    function CancelAction()
    {
        return 1;
    }

    function Save()
    {
        if ( !$this->validate() )
            return false;

        $vDB = GetDB();
        $vValues =& $this->getSubmitValues();
        $vID = @$vValues['id'];
        $vRecord = array();

        if ( !empty($vID) )
        {
            $vRecord['id'] = $vID;
        }

        CopyRecordRefValue($vRecord, $vValues, 'user_id');
        CopyRecordRefValue($vRecord, $vValues, 'case_id');
        CopyRecordDateValue($vRecord, $vValues, 'date');
//        CopyRecordStrValue($vRecord, $vValues, 'objective');
        CopyRecordStrValue($vRecord, $vValues, 'area');
        CopyRecordStrValue($vRecord, $vValues, 'diagnosis');
        CopyRecordBoolValue($vRecord, $vValues, 'done');
        CopyRecordStrValue($vRecord, $vValues, 'description');
        $vResult = $vDB->InsertOrUpdateById('emst_rg', $vRecord);
        if ( empty($vID) && !empty($vResult) )
        {
            $this->_submitValues['id']  = $vResult;
            $this->getElement('id')->setValue($vResult);

            $vRecord['id'] = $vResult;
            $_SESSION['_rg_dir_backup'] = $vRecord;
        }

        return $vResult;
    }

}


$vID = @$_GET['id'];
$vCaseID = @$_GET['caseid'];
$vForm = new TEditor($vID, $vCaseID);
switch( $vForm->ActionDispatcher() )
{
case 1:
//    print "redirect to prev page";
    unset($_SESSION['_rg_dir_backup']);
    Redirect( $vForm->_submitValues['html_referer']);
    break;

case 2:
//    Redirect( CompoundURL('/doc/rg_dir.pdf', array('id'=>$vForm->_submitValues['id'])) );
//    break;
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TBaseView();
    $vView->form = $vRenderer->toObject();
//    $vView->popup_url = CompoundURL('/doc/rg_dir.pdf', array(session_name()=>session_id(), 'id'=>$vForm->_submitValues['id']));
//    $vView->popup_url = CompoundURL('/doc/rg_dir.pdf', array('id'=>$vForm->_submitValues['id']));
    $vView->popup_url = CompoundURL('../produce_pdf.html', array('pdfpage'=>'./doc/rg_dir.pdf', 'id'=>$vForm->_submitValues['id']));
    $vTemplate->compile('doc/rg_dir_edit.html');
    $vTemplate->outputObject($vView);
    break;

default:
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TBaseView();
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('doc/rg_dir_edit.html');
    $vTemplate->outputObject($vView);
    break;
}

?>
