<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
    require_once('config/config.php');

    class TEditor extends HTML_QuickFormEx
    {
        function TEditor($AId='')
        {
            $vDB = GetDB();

            if ( !empty($AId) )
            {
                $vRecord = $vDB->GetById('emst_cases', $AId);
                if (  !is_array($vRecord) )
                   $vRecord = array();
                else
                {
                }
            }
            else
            {
                $vRecord = array();
                $vRecord['accident_datetime'] = date('Y-m-d');
            }


            $this->HTML_QuickForm('frmItem', 'post', $_SERVER['REQUEST_URI']);
            $this->setMyRequiredNote();
            $this->addElement('header',   'header1',         'Паспортные данные');
            $this->addElement('hidden',   'id',              '');
            $this->addElement('text',     'last_name',       'Фамилия',          array('class'=>'edt_100'));
            $this->addElement('text',     'first_name',      'Имя',              array('class'=>'edt_100'));
            $this->addElement('text',     'patr_name',       'Отчество',         array('class'=>'edt_100'));
            $this->addElement('select',   'is_male',         'Пол', array(0=>'-Не указан-', 1=>'Мужской',2=>'Женский'));
            $this->addElement('dateex',   'born_date',       'Дата рождения',    array('language' => 'ru', 'format'=>'dMY', 'minYear'=>1900, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));

            $this->addElement('select',   'doc_type_id',     'Документ', $vDB->GetRBList('rb_doc_types','id', 'name', true));
            $this->addElement('text',     'doc_series',      'серия',         array('class'=>'edt_tiny'));
            $this->addElement('text',     'doc_number',      'номер',         array('class'=>'edt_100'));

            $this->addElement('text',     'addr_reg_street',    'Адрес регистрации', array('class'=>'edt_100'));
            $this->addElement('text',     'addr_reg_num',       'дом',               array('class'=>'edt_tiny'));
            $this->addElement('text',     'addr_reg_subnum',    'корп',              array('class'=>'edt_tiny'));
            $this->addElement('text',     'addr_reg_apartment', 'кв',                array('class'=>'edt_tiny'));
            $this->addElement('text',     'addr_phys_street',   'Адрес пребывания',  array('class'=>'edt_100'));
            $this->addElement('text',     'addr_phys_num',      'дом',               array('class'=>'edt_tiny'));
            $this->addElement('text',     'addr_phys_subnum',   'корп',              array('class'=>'edt_tiny'));
            $this->addElement('text',     'addr_phys_apartment','кв',                array('class'=>'edt_tiny'));
            $this->addElement('text',     'phone',              'Телефон(ы)',        array('class'=>'edt_100'));
            $this->addElement('select',   'employment_category_id', 'Категория',     $vDB->GetRBList('rb_employment_categories','id', 'name', true));
            $this->addElement('text',     'employment_place',   'Место работы',      array('class'=>'edt_100'));
            $this->addElement('text',     'profession',         'Профессия',         array('class'=>'edt_100'));

            $this->addElement('select',   'insurance_company_id','Полис: СМО',       $vDB->GetRBList('rb_insurance_companies','id', 'long_name', true));
            $this->addElement('text',     'polis_series',        'серия',            array('class'=>'edt_tiny'));
            $this->addElement('text',     'polis_number',        'номер',            array('class'=>'edt_100'));
            $this->addElement('select',   'paytype',             'тип',              array(0=>'ОМС', 1=>'ДМС', 2=>'Платные услуги'));

     /*Дата действия полиса*/
            $this->addElement('dateex',   'patient_polis_from',  'Действителен с',   array('language' => 'ru', 'format'=>'dMY', 'minYear'=>1900, 'maxYear'=>gMaxYear,'addEmptyOption'=>true));
            $this->addElement('dateex',   'patient_polis_to',    'по',               array('language' => 'ru', 'format'=>'dMY', 'minYear'=>1900, 'maxYear'=>gMaxYear,'addEmptyOption'=>true));

            $this->addElement('header',   'header2',             'Дополнительные сведения');
            $this->addElement('select',   'trauma_type_id',      'Тип травмы', $vDB->GetRBList('rb_trauma_types','id', 'name', true,'','code'));
            $this->addElement('textarea', 'accident',            'Со слов пострадавшего<BR>что произошло', array('rows' => 3, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('dateex',   'accident_datetime',   'Когда',              array('language' => 'ru', 'format'=>'dMY, H:i','minYear'=>gMinYear,'maxYear'=>gMaxYear,'optionIncrement'=>array('i'=>'10')));
            $this->addElement('textarea', 'accident_place',      'Место происшествия', array('rows' => 3, 'cols' => 48, 'class'=>'edt_100'));

            $this->addElement('checkbox', 'phone_message_required',       'Передать телефонограмму');
            $this->addElement('checkbox', 'ice_trauma',          'Гололёд');
            $this->addElement('checkbox', 'animal_bite_trauma',  'Укус животного');
            $this->addElement('checkbox', 'ixodes_trauma',       'Укус клеща');
            $this->addElement('text',     'message_number',      'Номер телефонограммы',   array('class'=>'edt_100'));
            $this->addElement('textarea', 'notes',               'Дополнительные сведения', array('rows' => 6, 'cols' => 44));

            $this->addElement('submit',   'Submit',              'Ok');
            $this->applyFilter('_ALL_',   'trim');

            $this->addRule('last_name',  'Это поле обязательно для заполнения', 'required');
            $this->addRule('first_name', 'Это поле обязательно для заполнения', 'required');
//          $this->addRule('address',    'Это поле обязательно для заполнения', 'required');

            $this->setDefaults($vRecord);
        }

        function Save()
        {
            if ( !$this->validate() )
                return FALSE;

            $vDB = GetDB();
            $vValues = $this->getSubmitValues(TRUE);
            $vId = @$vValues['id'];
            $vRecord = array();

            if ( !empty($vId) )
            {
              $vRecord['id'] = $vId;
            }
            else
            {
              $vRecord['create_time'] = $vDB->ConvertToDateTime(time());
              $vRecord['next_visit_date'] = $vDB->ConvertToDate(time());
            }

            $vRecord['modify_time'] = $vDB->ConvertToDateTime(time());
            CopyRecordStrValue( $vRecord, $vValues, 'last_name');
            CopyRecordStrValue( $vRecord, $vValues, 'first_name');
            CopyRecordStrValue( $vRecord, $vValues, 'patr_name');
            CopyRecordBoolValue($vRecord, $vValues, 'is_male');
            CopyRecordDateValue($vRecord, $vValues, 'born_date');
            CopyRecordRefValue( $vRecord, $vValues, 'doc_type_id');
            CopyRecordStrValue( $vRecord, $vValues, 'doc_series');
            CopyRecordStrValue( $vRecord, $vValues, 'doc_number');
            CopyRecordRefValue( $vRecord, $vValues, 'insurance_company_id');
            CopyRecordStrValue( $vRecord, $vValues, 'polis_series');
            CopyRecordStrValue( $vRecord, $vValues, 'polis_number');

            CopyRecordDateValue( $vRecord, $vValues, 'patient_polis_from');
            CopyRecordDateValue( $vRecord, $vValues, 'patient_polis_to');

            CopyRecordStrValue( $vRecord, $vValues, 'paytype');
            CopyRecordStrValue( $vRecord, $vValues, 'addr_reg_street');
            CopyRecordStrValue( $vRecord, $vValues, 'addr_reg_num');
            CopyRecordStrValue( $vRecord, $vValues, 'addr_reg_subnum');
            CopyRecordStrValue( $vRecord, $vValues, 'addr_reg_apartment');
            CopyRecordStrValue( $vRecord, $vValues, 'addr_phys_street');
            CopyRecordStrValue( $vRecord, $vValues, 'addr_phys_num');
            CopyRecordStrValue( $vRecord, $vValues, 'addr_phys_subnum');
            CopyRecordStrValue( $vRecord, $vValues, 'addr_phys_apartment');
            CopyRecordStrValue( $vRecord, $vValues, 'phone');
            CopyRecordRefValue( $vRecord, $vValues, 'employment_category_id');
            CopyRecordStrValue( $vRecord, $vValues, 'employment_place');
            CopyRecordStrValue( $vRecord, $vValues, 'profession');

            CopyRecordRefValue($vRecord, $vValues, 'trauma_type_id');
            CopyRecordStrValue($vRecord, $vValues, 'notes');
            CopyRecordStrValue($vRecord, $vValues, 'accident');
            CopyRecordDateTimeValue($vRecord, $vValues, 'accident_datetime');
            CopyRecordStrValue($vRecord, $vValues,  'accident_place');
            CopyRecordBoolValue($vRecord, $vValues, 'phone_message_required');
            CopyRecordBoolValue($vRecord, $vValues, 'ice_trauma');
            CopyRecordBoolValue($vRecord, $vValues, 'animal_bite_trauma');
            CopyRecordBoolValue($vRecord, $vValues, 'ixodes_trauma');
            CopyRecordStrValue($vRecord,  $vValues, 'message_number');
          /* Здесь нужна проверка введенных данных в поле ПОЛИС СМО*/
            $vResult = $vDB->InsertOrUpdateById('emst_cases', $vRecord);

//       var_dump($vRecord);
//       var_dump($vDB);

           return $vResult;
        }

    }


    $vId = @$_GET['id'];
    $vForm = new TEditor($vId);
    if ( !$vForm->Save() )
    {
        $vTemplate =& CreateTemplate();
        $vRenderer =& CreateRenderer($vTemplate);
        $vForm->accept($vRenderer);
        $vView =& new TBaseView();
        $vView->form = $vRenderer->toObject();
        $vTemplate->compile('reg/case_edit.html');
        $vTemplate->outputObject($vView);
    }
    else
    {
        RedirectToList('cases.html');
    }

?>
