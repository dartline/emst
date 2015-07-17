<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
    require_once('config/config.php');

    class TEditor extends HTML_QuickFormEx
    {
        function TEditor($AID, $ACaseID)
        {
            $vDB = GetDB();
            if ( !empty($AID) )
            {
                $vRecord = $vDB->GetById('emst_hospitals', $AID);
                if ( !is_array($vRecord) )
                   $vRecord = array();
            }
            else
            {
                $vRecord = array();
                $vRecord['date'] = $vDB->ConvertToDate(time());
            }

            if ( !empty($ACaseID) )
               $vRecord['case_id'] = $ACaseID;
            $vCaseID = @$vRecord['case_id'];
/*
            if ( !empty($vCaseID) )
            {
                $vCase = $vDB->GetById('emst_cases', $vCaseID);
                if ( is_array($vCase) )
                {
                   $vRecord['last_name'] = $vCase['last_name'];
                   $vRecord['first_name'] = $vCase['first_name'];
                   $vRecord['patr_name'] = $vCase['patr_name'];
                   $vRecord['is_male'] = $vCase['is_male'];
                   $vRecord['born_date'] = $vCase['born_date'];
                   $vRecord['passport'] = $vCase['passport'];

                   $vRecord['address'] = $vCase['address'];
                   $vRecord['phone'] = $vCase['phone'];
                   $vRecord['employment_place'] = $vCase['employment_place'];
                   $vRecord['profession'] = $vCase['profession'];
                   $vRecord['insurance_company_id'] = $vCase['insurance_company_id'];
                   $vRecord['polis'] = $vCase['polis'];
                }
            }
*/
            $vRecord['html_referer'] = $_SESSION['PrevPage'];

            $this->HTML_QuickForm('frmItem', 'post', $_SERVER['REQUEST_URI']);
            $this->setMyRequiredNote();
            $this->addElement('header',   'header',          'Сведения из стационара');
            $this->addElement('hidden',   'id',              '');
            $this->addElement('hidden',   'case_id',         '');
            $this->addElement('hidden',   'html_referer',    '');
/*

            $vElement =& $this->addElement('text',     'case_id',         'История болезни №', array('class'=>'edt_wide'));
            $vElement->freeze();
            $vElement =& $this->addElement('text',     'last_name',       'Фамилия',          array('class'=>'edt_wide'));
            $vElement->freeze();
            $vElement =& $this->addElement('text',     'first_name',      'Имя',              array('class'=>'edt_wide'));
            $vElement->freeze();
            $vElement =& $this->addElement('text',     'patr_name',       'Отчество',         array('class'=>'edt_wide'));
            $vElement->freeze();
            $vElement =& $this->addElement('select',   'is_male',         'Пол', array(0=>'Женский', 1=>'Мужской'));
            $vElement->freeze();
            $vElement =& $this->addElement('date',     'born_date',       'Дата рождения');
            $vElement->freeze();
            $vElement =& $this->addElement('text',     'passport',        'Паспорт',          array('class'=>'edt_wide'));
            $vElement->freeze();
//            $vElement =& $this->addElement('text',     'reg_address',     'Адрес регистрации',array('class'=>'edt_wide'));
//            $vElement->freeze();
            $vElement =& $this->addElement('text',     'address',         'Адрес пребывания', array('class'=>'edt_wide'));
            $vElement->freeze();
            $vElement =& $this->addElement('text',     'phone',           'Телефон',          array('class'=>'edt_wide'));
            $vElement->freeze();
            $vElement =& $this->addElement('text',     'employment_place','Место работы',     array('class'=>'edt_wide'));
            $vElement->freeze();
            $vElement =& $this->addElement('text',     'profession',      'Профессия',        array('class'=>'edt_wide'));
            $vElement->freeze();
            $vElement =& $this->addElement('select',   'insurance_company_id',  'Страховая компания', $vDB->GetRBList('rb_insurance_companies','id', 'name', true));
            $vElement->freeze();
            $vElement =& $this->addElement('text',     'polis',           'Полис',            array('class'=>'edt_wide'));
            $vElement->freeze();
*/
            $this->addElement('dateex',   'beg_date',        'С',  array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear));
            $this->addElement('dateex',   'end_date',        'По', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear));
            $this->addElement('text',     'name',            'Наименование',  array('class'=>'edt_wide'));
            $this->addElement('textarea', 'diagnosis',       'Диагноз',       array('rows' => 6, 'cols' => 44));
            $this->addElement('textarea', 'operation',       'Операция',      array('rows' => 6, 'cols' => 44));
            $this->addElement('textarea', 'recommendation',  'Рекомендации',  array('rows' => 6, 'cols' => 44));
            $this->addElement('textarea', 'notes',           'Примечания',    array('rows' => 6, 'cols' => 44));

//            $this->addElement('submit',   'do_Print',     'Печать', array('width'=>'100px'));
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
            CopyRecordRefValue($vRecord, $vValues,  'case_id');
            CopyRecordDateValue($vRecord, $vValues, 'beg_date');
            CopyRecordDateValue($vRecord, $vValues, 'end_date');
            CopyRecordStrValue($vRecord, $vValues,  'name');
            CopyRecordStrValue($vRecord, $vValues,  'diagnosis');
            CopyRecordStrValue($vRecord, $vValues,  'operation');
            CopyRecordStrValue($vRecord, $vValues,  'recommendation');
            CopyRecordStrValue($vRecord, $vValues,  'notes');
//
//            var_dump($this);
//
            $vResult = $vDB->InsertOrUpdateById('emst_hospitals', $vRecord);
            if ( empty($vID) && $vResult )
                $this->_submitValues['id'] = $vResult;

            return $vResult;
        }

    }


    $vID = @$_GET['id'];
    $vCaseID = @$_GET['caseid'];
    $vForm = new TEditor($vID, $vCaseID);
    switch( $vForm->ActionDispatcher() )
    {
    case 1:
        Redirect( $vForm->_submitValues['html_referer']);
        break;

    case 2:
        Redirect( CompoundURL('/index.php', array('id'=>$vForm->_submitValues['id'], 'page'=>'doc/hospital_pdf')));
//      Redirect('/index.php?page=doc/rg_dir_pdf&id='.$vID);
        break;

    default:
        $vTemplate =& CreateTemplate();
        $vRenderer =& CreateRenderer($vTemplate);
        $vForm->accept($vRenderer);
        $vView =& new TBaseView();
        $vView->form = $vRenderer->toObject();
        $vTemplate->compile('doc/hospital_edit.html');
        $vTemplate->outputObject($vView);
        break;
    }

?>