<?php

#####################################################################
#
# EMST:Травмпункт
# (c) 2005,2006 Vista
#
# Форма редактирования закличения о состоянии здоровья
#
#####################################################################

require_once('./config/config.php');


class TEditor extends HTML_QuickFormEx
{
    function TEditor($ACaseID)
    {
        $vDB = GetDB();
        if ( !empty($ACaseID) )
        {
            $vCase = $vDB->GetById('emst_cases', $ACaseID);
            if ( is_array($vCase) )
            {
               $vRecord['case_id']    = $ACaseID;
               $vRecord['create_time']= $vCase['create_time'];
               $vRecord['last_name']  = $vCase['last_name'];
               $vRecord['first_name'] = $vCase['first_name'];
               $vRecord['patr_name']  = $vCase['patr_name'];
//               $vRecord['is_male']    = $vCase['is_male'];
               $vRecord['born_date']  = $vCase['born_date'];
//               $vRecord['doc_type_id']= $vCase['doc_type_id'];
//               $vRecord['doc_series'] = $vCase['doc_series'];
//               $vRecord['doc_number'] = $vCase['doc_number'];
//               $vRecord['addr_phys_street'] = $vCase['addr_phys_street'];
//               $vRecord['addr_phys_num'] = $vCase['addr_phys_num'];
//               $vRecord['addr_phys_subnum'] = $vCase['addr_phys_subnum'];
//               $vRecord['addr_phys_apartment'] = $vCase['addr_phys_apartment'];
//               $vRecord['phone'] = $vCase['phone'];
               $vRecord['employment_place'] = $vCase['employment_place'];
               $vRecord['profession']    = $vCase['profession'];
               $vRecord['diagnosis']     = $vCase['diagnosis'];
               $vRecord['diagnosis_mkb'] = $vCase['diagnosis_mkb'];
               $vRecord['doctor_id']     = $vCase['first_doctor_id'];
//               $vRecord['insurance_company_id'] = $vCase['insurance_company_id'];
//               $vRecord['polis_series'] = $vCase['polis_series'];
//               $vRecord['polis_number'] = $vCase['polis_number'];
            }
        }
        $vRecord['html_referer'] = $_SESSION['PrevPage'];

        $this->HTML_QuickForm('frmItem', 'post', $_SERVER['REQUEST_URI']);
        $this->setMyRequiredNote();
        $this->addElement('header',   'header',          'Заключение о степени тяжести травмы');
        $this->addElement('hidden',   'doctor_id',       '');
        $this->addElement('hidden',   'create_time',     '');
        $this->addElement('hidden',   'html_referer',    '');


        $vElement =& $this->addElement('text',     'case_id',         'История болезни №', array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'last_name',       'Фамилия',          array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'first_name',      'Имя',              array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'patr_name',       'Отчество',         array('class'=>'edt_wide'));
        $vElement->freeze();
//        $vElement =& $this->addElement('select',   'is_male',         'Пол', array(0=>'Женский', 1=>'Мужской'));
//        $vElement->freeze();
        $vElement =& $this->addElement('dateex',   'born_date',       'Дата рождения', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>1900, 'maxYear'=>gMaxYear));
        $vElement->freeze();

//        $vElement =& $this->addElement('select',   'doc_type_id',     'Документ', $vDB->GetRBList('rb_doc_types','id', 'name', true));
//        $vElement->freeze();
//        $vElement =& $this->addElement('text',     'doc_series',      'серия',         array('class'=>'edt_tiny'));
//        $vElement->freeze();
//        $vElement =& $this->addElement('text',     'doc_number',      'номер',         array('class'=>'edt_100'));
//        $vElement->freeze();
//        $vElement =& $this->addElement('text',     'addr_phys_street',   'Адрес пребывания',  array('class'=>'edt_norm'));
//        $vElement->freeze();
//        $vElement =& $this->addElement('text',     'addr_phys_num',      'дом',               array('class'=>'edt_tiny'));
//        $vElement->freeze();
//        $vElement =& $this->addElement('text',     'addr_phys_subnum',   'корп',              array('class'=>'edt_tiny'));
//        $vElement->freeze();
//        $vElement =& $this->addElement('text',     'addr_phys_apartment','кв',                array('class'=>'edt_tiny'));
//        $vElement->freeze();
//        $vElement =& $this->addElement('text',     'phone',           'Телефон',          array('class'=>'edt_wide'));
//        $vElement->freeze();
        $vElement =& $this->addElement('text',     'employment_place','Место работы',     array('class'=>'edt_wide'));
//         $vElement->freeze();
        $vElement =& $this->addElement('text',     'profession',      'Профессия',        array('class'=>'edt_wide'));
        $vElement =& $this->addElement('textarea', 'diagnosis',       'Диагноз',  array('rows' => 6, 'cols' => 44));
        $vElement->freeze();
        $vElement =& $this->addElement('text',     'diagnosis_mkb',   'Код диагноза по МКБ',   array('class'=>'edt_wide'));
        $vElement->freeze();
        $vElement =& $this->addElement('select',   'heavity',         'Степень тяжести',  $vDB->GetRBList('rb_trauma_heavity','id', 'name', false));

//         $vElement->freeze();
//         $vElement =& $this->addElement('select',   'insurance_company_id','Полис: СМО',       $vDB->GetRBList('rb_insurance_companies','id', 'name', true));
//         $vElement->freeze();
//         $vElement =& $this->addElement('text',     'polis_series',        'серия',            array('class'=>'edt_tiny'));
//         $vElement->freeze();
//         $vElement =& $this->addElement('text',     'polis_number',        'номер',            array('class'=>'edt_100'));
//         $vElement->freeze();

//         $this->addElement('dateex',   'date',            'Дата', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear));
//         $this->addElement('textarea', 'objective',       'Объективный статус',  array('rows' => 6, 'cols' => 44));
//         $this->addElement('text',     'area',            'Область',   array('class'=>'edt_wide'));
//         $this->addElement('checkbox', 'done',            'Выполнено');
//         $this->addElement('textarea', 'description',     'Описание',  array('rows' => 6, 'cols' => 44));

        $this->addElement('submit',   'do_Print',     'Печать', array('width'=>'100px'));
//          $this->addElement('submit',   'do_Save',      'Ok', array('width'=>'100px'));
        $this->addElement('submit',   'do_Cancel',    'Ok', array('width'=>'100px'));
        $this->applyFilter('_ALL_', 'trim');
        $this->setDefaults($vRecord);
    }


    function ActionDispatcher()
    {
        $vValues = $this->getSubmitValues();
        if ( array_key_exists('do_Print', $vValues) )
          return $this->PrintAction();
//          elseif ( array_key_exists('do_Save', $vValues) )
//            return $this->SaveAction();
        elseif ( array_key_exists('do_Cancel', $vValues) )
          return $this->CancelAction();
        else
          return false;
    }


    function SaveAction()
    {
        return false;
    }


    function PrintAction()
    {
        return 2;
    }

    function CancelAction()
    {
        return 1;
    }
}


$vCaseID = @$_GET['id'];
$vForm = new TEditor($vCaseID);
switch( $vForm->ActionDispatcher() )
{
case 1:
    unset($_SESSION['_rg_dir_backup']);
    Redirect( $vForm->_submitValues['html_referer']);
    break;

case 2:
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TBaseView();
    $vView->form = $vRenderer->toObject();
    $vValues =& $vForm->_submitValues;
    $vParams = array();
    $vParams['pdfpage'] = 'info/conclusion.pdf';
    CopyRecordRefValue($vParams, $vValues, 'case_id');
    CopyRecordRefValue($vParams, $vValues, 'doctor_id');
    CopyRecordStrValue($vParams, $vValues, 'create_time');
    CopyRecordStrValue($vParams, $vValues, 'last_name');
    CopyRecordStrValue($vParams, $vValues, 'first_name');
    CopyRecordStrValue($vParams, $vValues, 'patr_name');
    CopyRecordDateValue($vParams, $vValues, 'born_date');
    CopyRecordStrValue($vParams, $vValues, 'employment_place');
    CopyRecordStrValue($vParams, $vValues, 'profession');
    CopyRecordStrValue($vParams, $vValues, 'diagnosis');
    CopyRecordStrValue($vParams, $vValues, 'diagnosis_mkb');
    CopyRecordRefValue($vParams, $vValues, 'heavity');
    $vView->popup_url = CompoundURL('produce_pdf.html', $vParams);
    $vTemplate->compile('info/conclusion.html');
    $vTemplate->outputObject($vView);
    break;

default:
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TBaseView();
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('info/conclusion.html');
    $vTemplate->outputObject($vView);
    break;
}

?>
