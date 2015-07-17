<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################

require_once('HTML/QuickForm/Controller.php');

// Load some default action handlers
require_once('HTML/QuickForm/Action/Submit.php');
require_once('HTML/QuickForm/Action/Jump.php');
require_once('HTML/QuickForm/Action/Display.php');
require_once('HTML/QuickForm/Action/Direct.php');

require_once('config/config.php');
require_once('library/cases_table.php');

    function _2w(&$Src)
    {
        return iconv( "utf-8", "windows-1251", $Src );
    }

    function CopyValue(&$ADest, &$ASrc, $AName)
    {
        $ADest[$AName] = @$ASrc[$AName];
    }


    function CopyValues(&$ADest, &$ASrc, $ANames)
    {
        foreach($ANames as $vName)
        {
            $ADest[$vName] = @$ASrc[$vName];
        }
    }


    function ConstructCure(&$ACures)
    {
        for($i=0; $i<count($ACures); $i++)
        {
            if ( $ACures[$i] != '' )
                return $ACures[$i];
        }
        return '';
    }


    function DatePageName($ANum)
    {
        return 'Date'.$ANum;
    }


    function GetFormActionAddress()
    {
        static $vAddress;

        if ( $vAddress == '' )
        {
            $vParams = array();
            foreach($_GET as $vParam => $vVal )
            {
              if ( $vParam == 'id' )
                $vParams[$vParam] = $vVal;
//
//              if ( !preg_match("/^((page$)|(_qf_))/i", $vParam) )
//                $vParams[$vParam] = $vVal;
            }
            list($vBase,$vJunk) = explode('?', $_SERVER['REQUEST_URI'].'?', 2);
            $vAddress = CompoundURL($vBase, $vParams);
        }
        return $vAddress;
    }


    function GetPaytypeName($APaytype)
    {
        if ( $APaytype == 0 )
            return 'ОМС';
        elseif ( $APaytype == 1 )
            return 'ДМС';
        else
            return '{неизвестный тип финансирования}';
    }

    class MyPage extends HTML_QuickForm_Page
    {
        function __construct($aFormName, $aFormTitle=null)
        {
            $this->HTML_QuickForm($aFormName, 'post', GetFormActionAddress(),
            /* target= */ '', /* attributes= */ null, /* trackSubmit= */ true);

            $this->title = empty($aFormTitle)?$aFormName:$aFormTitle;
        }

        function MyPage($aFormName, $aFormTitle=null)
        {
           MyPage::__construct($aFormName, $aFormTitle);
        }


        function buildTab()
        {
            if ( !empty($this->controller) )
            {
                $curPageParams = array('class' => 'flat_active', 'disabled' => 'disabled', 'style'=>'width:14em');
                $otrPageParams = array('class' => 'flat_passive', 'style'=>'width:14em');
                $curCECPageParams = $curPageParams;
                $otrCECPageParams = $otrPageParams;
                $curCECPageParams['class'] = $curCECPageParams['class'].'_cec';
                $otrCECPageParams['class'] = $otrCECPageParams['class'].'_cec';

                $vPages =& $this->controller->_pages;
                foreach( $vPages as $vPageID=>$vPage )
                {
                    $vPageTitle = $vPage->title;
                    if ( strpos($vPageTitle,'(ВК)') === false )
                    {
                        $vPageParams = $vPages[$vPageID] === $this ? $curPageParams : $otrPageParams;
                    }
                    else
                    {
                        $vPageParams = $vPages[$vPageID] === $this ? $curCECPageParams : $otrCECPageParams;
                    }

                    $tabs[] =& $this->createElement('submit',
                                                    $this->getButtonName($vPageID),
                                                    $vPageTitle,
                                                    $vPageParams);
                }
//                $this->addGroup($tabs, 'tabs', null, '&nbsp;', false);
                $this->addGroup($tabs, 'tabs', null, ' ', false);
            }
        }


        function validate()
        {
            if ( count($this->_rules) == 0 && count($this->_formRules) == 0 )
                return true;
            else
                return parent::validate();
        }

/*
        function isSubmitted()
        {
            return true;
        }
*/
    }


    class MyController extends HTML_QuickForm_Controller
    {
        function __construct($aName)
        {
            // false - это признак модальности, если есть то
            // как-то по левому открываются закладки...
            $this->HTML_QuickForm_Controller($aName, false);
        }


        function MyController($aName)
        {
            MyController::__construct($aName);
        }


        function addPageEx(&$aPage)
        {
            $this->addPage($aPage);
            $this->addAction($aPage->getAttribute('id'), new HTML_QuickForm_Action_Direct());
        }


        function& getDefaults()
        {
            $vData =& $this->container();
            $vDefaults =& $vData['defaults'];
            if ( !empty($vDefaults) && @($_GET['id'] == $vDefaults['BaseInfo']['id']) )
            {
                return $vDefaults;
            }

            $this->container(true);

            $vResult = $this->loadDefaults();
            $this->setDefaults($vResult);

            $vCount = count($vResult['surgeries']);
            if ( $vCount>0 && !empty($vResult['BaseInfo']['id']) )
            {
               $this->actionName = array(DatePageName($vCount-1), 'jump');
            }

            return $vResult;
        }


        function& loadDefaults()
        {
            $vResult = attay();
            return $vResult;
        }


        function applyDefaults($pageName)
        {
            $vData =& $this->container();
            if ( @!empty($vData['defaults']) )
            {
                $vDefaults =& $vData['defaults'];
                if ( @!empty($vDefaults[$pageName]) )
                {
                    $this->_pages[$pageName]->setDefaults($vDefaults[$pageName]);
                }
            }

            if (!empty($vData['constants'])) {
                $this->_pages[$pageName]->setConstants($vData['constants']);
            }
        }
    }

/////////////////////////////////////////////////////////////////////

    class AcceptFormPage extends MyPage
    {
        function buildFormCommonItems()
        {
            $this->_formBuilt = true;
            $this->buildTab();

//            $vcaseID =& $this->addElement('text',     'id',       'ИБ №');
//            $vcaseID->freeze();
        }
    }


    class BaseInfoPage extends AcceptFormPage
    {
        function buildForm()
        {
            $vDB = GetDB();
            $this->buildFormCommonItems();

            $this->addElement('header',   'header1',         'Паспортные данные');
            $this->addElement('hidden',   'id',              '');

            $this->addElement('text',     'last_name',       'Фамилия',          array('class'=>'edt_100'));
            $this->addElement('text',     'first_name',      'Имя',              array('class'=>'edt_100'));
            $this->addElement('text',     'patr_name',       'Отчество',         array('class'=>'edt_100'));
            $this->addElement('select',   'is_male',         'Пол', array(0=>'Женский', 1=>'Мужской',2=>'-Не указан-'));
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

            $this->addElement('select',   'insurance_company_id','Полис: СМО',       $vDB->GetRBList('rb_insurance_companies','id', 'name', true));
            $this->addElement('text',     'polis_series',        'серия',            array('class'=>'edt_tiny'));
            $this->addElement('text',     'polis_number',        'номер',            array('class'=>'edt_100'));
            $this->addElement('select',   'paytype',             'тип',              array(0=>'ОМС', 1=>'ДМС'));

            /*Дата действия полиса*/
            $this->addElement('dateex',   'patient_polis_from',  'Действителен с',   array('language' => 'ru', 'format'=>'dMY', 'minYear'=>1900, 'maxYear'=>gMaxYear,'addEmptyOption'=>true));
            $this->addElement('dateex',   'patient_polis_to',    'по',               array('language' => 'ru', 'format'=>'dMY', 'minYear'=>1900, 'maxYear'=>gMaxYear,'addEmptyOption'=>true));

            $this->addElement('header',   'header2',             'Дополнительные сведения');
            $this->addElement('select',   'trauma_type_id',      'Тип травмы', $vDB->GetRBList('rb_trauma_types','id', 'name', true));
            $this->addElement('textarea', 'notes',               'Дополнительные сведения', array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));

            $this->addElement('submit',   'submit',          'OK', array('class' => 'bigred'));

            $this->setDefaultAction('submit');
        }
    }


    class FirstPassPage extends AcceptFormPage
    {
        function buildForm()
        {
            $vDB = GetDB();
            $this->buildFormCommonItems();

            $this->addElement('header',     'header', 'Первичный осмотр');
            $this->addElement('textarea',   'accident',           'Со слов пострадавшего<BR>что произошло', array('rows' => 3, 'cols' => 48, 'class'=>'edt_100'));
//            $this->addElement('textarea',   'accident',           'Со слов пострадавшего<BR>что произошло', array('rows' => 3, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('dateex',     'accident_datetime',  'Когда',         array('language' => 'ru', 'format'=>'dMY, H:i','minYear'=>gMinYear,'maxYear'=>gMaxYear,'optionIncrement'=>array('i'=>'10')));
            $this->addElement('textarea',   'accident_place',     'Место происшествия',           array('rows' => 3, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('checkbox',   'phone_message_required',       'Передать телефонограмму');
            $this->addElement('checkbox',   'ice_trauma',         'Гололёд');
            $this->addElement('checkbox',   'animal_bite_trauma', 'Укус животного');
            $this->addElement('checkbox',   'ixodes_trauma',      'Укус клеща');
            $this->addElement('text',       'message_number',     'Номер телефонограммы',   array('class'=>'edt_100'));

            $this->addElement('select',     'antitetanus_id',     'Профилактика столбняка', $vDB->GetRBList('rb_antitetanus','id', 'name', true));
            $this->addElement('text',       'antitetanus_series', 'Серия',         array('class'=>'edt_100'));

//            $this->addElement('textarea',   'complaints',         'Жалобы',        array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('text',       'complaints',         'Жалобы',        array('class'=>'edt_100'));
            $this->addElement('textarea',   'objective',          'Объективный статус',  array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));

            $this->addElement('textarea',   'diagnosis',          'Диагноз',       array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('text',       'diagnosis_mkb',      'Код МКБ',       array('class'=>'edt_100'));
            $this->addElement('select',     'manipulation_id',    'Манипуляция',   $vDB->GetRBList('rb_manipulations','id', 'name', true));
            $this->addElement('text',       'manipulation_text',  '',   array('class'=>'edt_100'));
            $this->addElement('textarea',   'cure',               'Лечение', array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('textarea',   'notes',              'Дополнительные сведения', array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));

            $this->addElement('select',     'disability',         'Трудоспособность', array('0'=>'', 1=>'Трудоспособность сохранена', 2=>'Трудоспособность утрачена'));
            $this->addElement('dateex',     'disability_from_date','Нетрудоспособность с', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));

            $this->addElement('checkbox',   'ill_refused',        'Отказ от документа');
            $this->addElement('checkbox',   'ill_sertificat',     'Справка');
            $this->addElement('text',       'ill_doc',            'Листок нетрудоспособности №',  array('class'=>'edt_norm'));
            $this->addElement('checkbox',   'ill_doc_closed',     'Закрыт');
            $this->addElement('text',       'ill_doc_new',        'Выдано продолжение №',  array('class'=>'edt_norm'));
            $this->addElement('select',     'ill_doc_is_continue','', array(0=>'Первичный', 1=>'Продление'));
            $this->addElement('dateex',     'ill_beg_date',       'с',         array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));
            $this->addElement('dateex',     'ill_end_date',       'по',        array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true, 'addDays'=>9));

            $this->addElement('select',     'clinical_outcome_id','Исход', GetClinicalOutcomesList());
            $this->addElement('text',       'clinical_outcome_notes', '', array('class'=>'edt_norm'));

            $this->addElement('dateex',     'next_cec_date',      'Дата очередной ВК',   array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true, 'addDays'=>14));
            $this->addElement('dateex',     'next_visit_date',    'Дата следующей явки',  array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true, 'addDays'=>9));
            $this->addElement('select',     'next_visit_target_id','куда', $vDB->GetRBList('rb_vistit_targets', 'id', 'name', true));

            $this->addElement('header',     'cec_header', 'Представление на ВК');
            $this->addElement('checkbox',   'is_cec',  'ВК');
//            $this->addElement('text',       'cec_number',         'протокол ВК №',  array('class'=>'edt_mid'));
            $this->addElement('dateex',     'cec_cureup_date',    'Прошу продлить лечение по', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true, 'addDays'=>14));
//            $this->addElement('textarea',   'cec_members',        'Члены ВК', array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));

            $this->addElement('submit',   $this->getButtonName('print_ill_doc'),   'напечатать листок нетрудоспособности');

            $this->addElement('submit',   'submit',          'OK', array('class' => 'bigred'));
            $this->setDefaultAction('submit');
        }
    }


    class NonFirstPassPage extends AcceptFormPage
    {
        function buildForm()
        {
            $vDB = GetDB();
            $this->buildFormCommonItems();

            $this->addElement('header',     'header', 'Дневник');
            $this->addElement('text',       'complaints',         'Жалобы', array('class'=>'edt_100'));
            $this->addElement('select',     'dynamic_id',         'Динамика',   $vDB->GetRBList('rb_dynamics','id', 'name', true));
            $this->addElement('textarea',   'objective',          'Объективный статус',  array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('textarea',   'diagnosis',          'Диагноз',       array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('text',       'diagnosis_mkb',      'Код МКБ',       array('class'=>'edt_100'));
            $this->addElement('select',     'manipulation_id',    'Манипуляция',   $vDB->GetRBList('rb_manipulations','id', 'name', true));
            $this->addElement('text',       'manipulation_text',  '',   array('class'=>'edt_100'));
            $this->addElement('textarea',   'cure',               'Лечение', array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('textarea',   'notes',              'Дополнительные сведения', array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));

            $this->addElement('select',     'disability',         'Трудоспособность', array(0=>'', 1=>'Трудоспособность сохранена', 2=>'Трудоспособность утрачена'));
            $this->addElement('checkbox',   'ill_refused',        'Отказ от документа');
            $this->addElement('checkbox',   'ill_sertificat',     'Справка');
            $this->addElement('text',       'ill_doc',            'Листок нетрудоспособности №',  array('class'=>'edt_norm'));
            $this->addElement('checkbox',   'ill_doc_closed',     'Закрыт');
            $this->addElement('text',       'ill_doc_new',        'Выдано продолжение №',  array('class'=>'edt_norm'));
            $this->addElement('select',     'ill_doc_is_continue','', array(0=>'Первичный', 1=>'Продление'));
            $this->addElement('dateex',     'ill_beg_date',       'с',         array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));
            $this->addElement('dateex',     'ill_end_date',       'по',        array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true, 'addDays'=>10));

            $this->addElement('select',     'clinical_outcome_id','Исход', GetClinicalOutcomesList());
            $this->addElement('text',       'clinical_outcome_notes', '', array('class'=>'edt_norm'));

            $this->addElement('dateex',     'next_cec_date',      'Дата очередной ВК', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true, 'addDays'=>29));
            $this->addElement('dateex',     'next_visit_date',    'Дата следующей явки',  array('language' => 'ru', 'format'=>'dMY','minYear'=>gMinYear,'maxYear'=>gMaxYear,'addEmptyOption'=>true, 'addDays'=>10));
            $this->addElement('select',     'next_visit_target_id',  'куда', $vDB->GetRBList('rb_vistit_targets', 'id', 'name', true));

            $this->addElement('header',     'cec_header', 'Представление на ВК');
            $this->addElement('checkbox',   'is_cec',  'ВК');
//            $this->addElement('text',       'cec_number',         'протокол ВК №',  array('class'=>'edt_mid'));
            $this->addElement('dateex',     'cec_cureup_date',    'Прошу продлить лечение по', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true, 'addDays'=>29));
//            $this->addElement('textarea',   'cec_members',        'Члены ВК', array('rows' => 6, 'cols' => 48));

            $this->addElement('submit',   $this->getButtonName('print_ill_doc'),   'напечатать листок нетрудоспособности');
            $this->addElement('submit',   'submit',          'OK', array('class' => 'bigred'));
//            $this->addAction('print_ill_doc', new ActionPrintIllDoc());
            $this->setDefaultAction('submit');
        }
    }


    class RGsPage extends AcceptFormPage
    {
        function buildForm()
        {
            $this->buildFormCommonItems();

            $this->addElement('header',     'header', 'Рентгенологические исследования');
            $vDB = GetDB();

            $vContainer =& $this->controller->container();
            $vDefaults  =& $vContainer['defaults'];
            $vCaseID    = @$vDefaults['BaseInfo']['id'];

            $vTab = new TTable('emst_rg',
                                'id, date, area, done, description',
                                $vDB->CondEqual('case_id', $vCaseID),
                                'date, area, done, id',
                                'id');
            $vTab->AddDateColumn('date', 'Дата');
            $vTab->AddColumn('area', 'Область исследования');
            $vTab->AddBoolColumn('done', 'Выполнено');
            $vTab->AddTextColumn('description', 'Описание');
            $vTab->AddRowAction('изменить', 'rg_dir_edit.html?id=');
            $vTab->AddTableAction('Новое направление на RG',  'rg_dir_edit.html?caseid='.$vCaseID);
            $vHTML = $vTab->ProduceHTML($vDB, @($_GET['PageIdx'])+0, 20);

            $this->addElement('static', 'table',  'table', $vHTML);
            $this->addElement('submit', 'submit', 'OK', array('class' => 'bigred'));
            $this->setDefaultAction('submit');

            if ( $this->controller->isValid() )
                ActionProcess::save($this->controller);

        }
    }


    class HospitalsPage extends AcceptFormPage
    {
        function buildForm()
        {
            $this->buildFormCommonItems();

            $this->addElement('header',     'header', 'Стационары');
            $vDB = GetDB();

            $vContainer =& $this->controller->container();
            $vDefaults  =& $vContainer['defaults'];
            $vCaseID    = @$vDefaults['BaseInfo']['id'];

            $vTab = new TTable('emst_hospitals',
                                '*',
                                $vDB->CondEqual('case_id', $vCaseID),
                                'beg_date, name, end_date, id',
                                'id');
            $vTab->AddDateColumn('beg_date', 'С');
            $vTab->AddDateColumn('end_date', 'По');
            $vTab->AddColumn('name', 'Наименование');
            $vTab->AddTextColumn('diagnosis', 'Диагноз');
            $vTab->AddTextColumn('operation', 'Операция');

            $vTab->AddRowAction('изменить', 'hospital_edit.html?id=');
            $vTab->AddTableAction('новая запись',  'hospital_edit.html?caseid='.$vCaseID);
            $vHTML = $vTab->ProduceHTML($vDB, @($_GET['PageIdx'])+0, 20);

            $this->addElement('static', 'table', 'table', $vHTML);
            $this->addElement('submit', 'submit','OK', array('class' => 'bigred'));
            $this->setDefaultAction('submit');

            if ( $this->controller->isValid() )
                ActionProcess::save($this->controller);
        }
    }


    class MiscDocsPage extends AcceptFormPage
    {
        function buildForm()
        {
            $this->buildFormCommonItems();
            $vDB = GetDB();

            $this->addElement('header', 'heavity_header', 'Заключение о степени тяжести травмы');
            $this->addElement('select', 'heavity', 'Степень тяжести',  $vDB->GetRBList('rb_trauma_heavity', 'id', 'name', false));
            $this->addElement('submit',   $this->getButtonName('print_heavity_conclusion'),   'напечатать');

            $this->addElement('header', 'direction_header', 'Направление');
            $this->addElement('select', 'direction_subject', 'Цель направления',  $vDB->GetRBList('rb_directions', 'id', 'name', false));
            $this->addElement('text',   'direction_target',  'Наименование учреждения', array('class'=>'edt_100'));
            $this->addElement('submit',  $this->getButtonName('print_direction'),   'напечатать');

            $this->addElement('header', 'physiotherapy_direction_header', 'Направление на физиотерапию');
            $this->addElement('submit',   $this->getButtonName('print_physiotherapy_direction'),   'напечатать');

            $this->addElement('header', 'remedial_gymnastics_direction_header', 'Направление на ЛФК');
            $this->addElement('submit',   $this->getButtonName('print_remedial_gymnastics_direction'),   'напечатать');

            $this->addElement('header', 'out_epicrisis_header', 'Выписной эпикриз');
//            $this->addElement('text',   'out_epicrisis_target', 'Наименование учреждения', array('class'=>'edt_100'));
            $this->addElement('textarea','out_epicrisis_recomendation', 'Лечебные и трудовые рекомендации', array('rows' => 6, 'cols' => 48, 'class'=>'edt_100'));
            $this->addElement('submit',  $this->getButtonName('print_out_epicrisis'),   'напечатать');

            $this->addElement('header',  'studinfo_header', 'Ученическая справка');
            $this->addElement('select',  'studinfo_type',   'Учащийся',  array(1=>'студент', 2=>'учащийся техникума', 3=>'учащийся профессионально-технического училища', 4=>'школьник', 5=>'дошкольник'));
            $this->addElement('text',    'studinfo_target', 'Наименование учреждения', array('class'=>'edt_100'));
            $this->addElement('checkbox','studinfo_show_diagnosis',  'Печатать диагноз');

            $this->addElement('submit',   $this->getButtonName('print_studinfo'),   'напечатать');

            $this->addElement('submit', 'submit','OK', array('class' => 'bigred'));
            $this->setDefaultAction('submit');
        }
    }



    class TPageView extends TBaseView
    {
        function DumpMe()
        {
//          var_dump( $this );
        }

        function PageIs($AName)
        {
            return strtolower(get_class($this->page)) == strtolower($AName);
        }

        function GetPrintIllDocButtonName()
        {
            return $this->page->getButtonName('print_ill_doc');
        }

        function GetPrintHeavityConclusionButtonName()
        {
            return $this->page->getButtonName('print_heavity_conclusion');
        }

        function GetPrintDirectionButtonName()
        {
            return $this->page->getButtonName('print_direction');
        }

        function GetPrintPhysiotherapyDirectionButtonName()
        {
            return $this->page->getButtonName('print_physiotherapy_direction');
        }

        function GetPrintRemedialGymnasticsDirectionButtonName()
        {
            return $this->page->getButtonName('print_remedial_gymnastics_direction');
        }


        function GetPrintOutEpicrisisButtonName()
        {
            return $this->page->getButtonName('print_out_epicrisis');
        }

        function GetPrintStudinfoButtonName()
        {
            return $this->page->getButtonName('print_studinfo');
        }
    }


    function DataAvailable(&$AArray)
    {
        return !empty($AArray) && is_array($AArray) && count($AArray)>0;
    }

    function DocsEmpty($AArray)
    {
        return
            empty($AArray['doc_series'])
         || empty($AArray['doc_number'])
         || empty($AArray['polis_series'])
         || empty($AArray['polis_number']);
    }


    class ActionDisplay extends HTML_QuickForm_Action_Display
    {
        function _renderForm(&$page)
        {
            global $gTemplateName;

            $vContainer =& $page->controller->container();

            $vTemplate =& CreateTemplate();
            $vRenderer =& CreateRenderer($vTemplate);

            $page->accept($vRenderer);
            $vView = new TPageView();
            $vView->page =& $page;
            $vView->form =& $vRenderer->toObject();

            $vDefaults  =& $vContainer['defaults'];
            $vValues    =& $vContainer['values'];

            $vBaseInfoDefaults =& $vDefaults['BaseInfo'];
            $vBaseInfoValues   =& $vValues['BaseInfo'];
            $vFirstPageDefaults=& $vDefaults[DatePageName(0)];
            $vFirstPageValues  =& $vValues[DatePageName(0)];

            $vView->case_id     = @$vBaseInfoDefaults['id'];
            $vView->create_time = @Date2ReadableLong($vBaseInfoDefaults['create_time']);
            $vBaseInfo =& $vBaseInfoDefaults;
            if ( DataAvailable($vBaseInfoValues) )
                $vBaseInfo =& $vBaseInfoValues;
        $vView->name = FormatNameEx($vBaseInfo);
        if ( DocsEmpty($vBaseInfo) )
            $vView->docs_is_empty = true;
        $vView->category = @FormatCategory($vBaseInfo['employment_category_id']);
        $vView->age      = 'полных лет '.CalcAge(DateValueToStr($vBaseInfo['born_date']));
        $vView->paytype  = @GetPaytypeName($vBaseInfo['paytype']);

            if ( DataAvailable($vFirstPageValues) )
            {
                $vView->disability_from_date = empty($vFirstPageValues['ill_doc'])?'':Date2ReadableLong(DateValueToStr($vFirstPageValues['disability_from_date']));
            }
            else
            {
                $vView->disability_from_date = empty($vFirstPageDefaults['ill_doc'])?'':Date2ReadableLong($vBaseInfoDefaults['disability_from_date']);
            }


            if ( !empty( $vContainer['_PopupURL'] ) )
            {
                $vView->popup_url = $vContainer['_PopupURL'];
                unset($vContainer['_PopupURL']);
            }

            $vTemplate->compile($gTemplateName);
            $vTemplate->outputObject($vView);
        }
    }


    class ActionPrintIllDoc extends HTML_QuickForm_Action
    {
        function perform(&$page, $actionName)
        {
            $page->isFormBuilt() or $page->buildForm();
            $vPageID     =  $page->getAttribute('id');
            $vController =& $page->controller;
            $vContainer  =& $vController->container();

            $vContainer['values'][$vPageID] = $page->exportValues();
            $vContainer['valid'][$vPageID]  = $page->validate();
            if ( $vController->isValid() )
                ActionProcess::save($vController);

            $vPageID = $page->getAttribute('id');
            $vSurgeryID = @$vContainer['defaults'][$vPageID]['id'];
            if ( !empty($vSurgeryID) )
                $vContainer['_PopupURL'] = CompoundURL('../produce_pdf.html', array('pdfpage'=>'./doc/ill_doc.pdf', 'id'=>$vSurgeryID));
            $page->handle('jump');
        }
    }


    class ActionPrintHeavityConclusion extends HTML_QuickForm_Action
    {
        function perform(&$page, $actionName)
        {
            $page->isFormBuilt() or $page->buildForm();
            $vPageID     =  $page->getAttribute('id');
            $vController =& $page->controller;
            $vContainer  =& $vController->container();

            $vContainer['values'][$vPageID] = $page->exportValues();
            $vContainer['valid'][$vPageID]  = $page->validate();
            $vController->isValid();
            $vDefaults  =& $vContainer['defaults'];
            $vValues    =& $vContainer['values'];
            $vNumDates  = count($vDefaults['surgeries']);
            $vBaseInfo  =& $vValues['BaseInfo'];
            if ( $vNumDates>0 )
                $vLastDate  =& $vValues[DatePageName($vNumDates-1)];
            else
                $vLastDate  = array();

            $vParams['pdfpage']     = 'info/conclusion.pdf';
            $vParams['create_time'] = $vDefaults['BaseInfo']['create_time'];
            $vParams['polis_series']= $vBaseInfo['polis_series'];
            $vParams['polis_number']= $vBaseInfo['polis_number'];
            $vParams['paytype']     = $vBaseInfo['paytype'];
            $vParams['last_name']   = $vBaseInfo['last_name'];
            $vParams['first_name']  = $vBaseInfo['first_name'];
            $vParams['patr_name']   = $vBaseInfo['patr_name'];
            $vParams['born_date']   = DateValueToStr($vBaseInfo['born_date']);
            $vParams['employment_place']   = $vBaseInfo['employment_place'];
            $vParams['profession']         = $vBaseInfo['profession'];
//            $vParams['addr_reg_street']    = $vBaseInfo['addr_reg_street'];
//            $vParams['addr_reg_num']       = $vBaseInfo['addr_reg_num'];
//            $vParams['addr_reg_subnum']    = $vBaseInfo['addr_reg_subnum'];
//            $vParams['addr_reg_apartment'] = $vBaseInfo['addr_reg_apartment'];
            $vParams['diagnosis']     = @$vLastDate['diagnosis'];
            $vParams['diagnosis_mkb'] = @$vLastDate['diagnosis_mkb'];
            $vParams['doctor_id']     = $_SESSION['User.ID'];
            $vParams['heavity']       = @$vValues[$vPageID]['heavity'];
            $vContainer['_PopupURL'] = CompoundURL('../produce_pdf.html', $vParams);
            $page->handle('jump');
        }
    }


    class ActionPrintDirection extends HTML_QuickForm_Action
    {
        function perform(&$page, $actionName)
        {
            $page->isFormBuilt() or $page->buildForm();
            $vPageID     =  $page->getAttribute('id');
            $vController =& $page->controller;
            $vContainer  =& $vController->container();

            $vContainer['values'][$vPageID] = $page->exportValues();
            $vContainer['valid'][$vPageID]  = $page->validate();
            $vController->isValid();
            $vDefaults  =& $vContainer['defaults'];
            $vValues    =& $vContainer['values'];
            $vNumDates  = count($vDefaults['surgeries']);
            $vBaseInfo  =& $vValues['BaseInfo'];
            if ( $vNumDates>0 )
                $vLastDate  =& $vValues[DatePageName($vNumDates-1)];
            else
                $vLastDate  = array();

            $vParams    =  array();
            $vParams['pdfpage']     = 'info/direction.pdf';
            $vParams['case_id']     = $vDefaults['BaseInfo']['id'];
            $vParams['polis_series']= $vBaseInfo['polis_series'];
            $vParams['polis_number']= $vBaseInfo['polis_number'];
            $vParams['paytype']     = $vBaseInfo['paytype'];
            $vParams['last_name']   = $vBaseInfo['last_name'];
            $vParams['first_name']  = $vBaseInfo['first_name'];
            $vParams['patr_name']   = $vBaseInfo['patr_name'];
            $vParams['born_date']   = DateValueToStr($vBaseInfo['born_date']);
            $vParams['employment_place']   = $vBaseInfo['employment_place'];
            $vParams['profession']         = $vBaseInfo['profession'];
            $vParams['addr_reg_street']    = $vBaseInfo['addr_reg_street'];
            $vParams['addr_reg_num']       = $vBaseInfo['addr_reg_num'];
            $vParams['addr_reg_subnum']    = $vBaseInfo['addr_reg_subnum'];
            $vParams['addr_reg_apartment'] = $vBaseInfo['addr_reg_apartment'];
            $vParams['diagnosis']     = @$vLastDate['diagnosis'];
            $vParams['diagnosis_mkb'] = @$vLastDate['diagnosis_mkb'];
            $vParams['doctor_id'] = $_SESSION['User.ID'];
            $vParams['direction_subject'] = @$vValues[$vPageID]['direction_subject'];
            $vParams['direction_target']  = @$vValues[$vPageID]['direction_target'];
            $vContainer['_PopupURL'] = CompoundURL('../produce_pdf.html', $vParams);
            $page->handle('jump');
        }
    }


    class ActionPrintPhysiotherapyDirection extends HTML_QuickForm_Action
    {
        function perform(&$page, $actionName)
        {
            $page->isFormBuilt() or $page->buildForm();
            $vPageID     =  $page->getAttribute('id');
            $vController =& $page->controller;
            $vContainer  =& $vController->container();

            $vContainer['values'][$vPageID] = $page->exportValues();
            $vContainer['valid'][$vPageID]  = $page->validate();
            $vController->isValid();
            $vDefaults  =& $vContainer['defaults'];
            $vValues    =& $vContainer['values'];
            $vNumDates  = count($vDefaults['surgeries']);
            $vBaseInfo  =& $vValues['BaseInfo'];
            if ( $vNumDates>0 )
                $vLastDate  =& $vValues[DatePageName($vNumDates-1)];
            else
                $vLastDate  = array();

            $vParams    =  array();
            $vParams['pdfpage']     = 'info/physiotherapy.pdf';
            $vParams['case_id']     = $vDefaults['BaseInfo']['id'];
//            $vParams['polis_series']= $vBaseInfo['polis_series'];
//            $vParams['polis_number']= $vBaseInfo['polis_number'];
            $vParams['last_name']   = $vBaseInfo['last_name'];
            $vParams['first_name']  = $vBaseInfo['first_name'];
            $vParams['patr_name']   = $vBaseInfo['patr_name'];
            $vParams['born_date']   = DateValueToStr($vBaseInfo['born_date']);
            $vParams['is_male']     = $vBaseInfo['is_male'];
//            $vParams['employment_place']   = $vBaseInfo['employment_place'];
//            $vParams['profession']         = $vBaseInfo['profession'];
            $vParams['addr_reg_street']    = $vBaseInfo['addr_reg_street'];
            $vParams['addr_reg_num']       = $vBaseInfo['addr_reg_num'];
            $vParams['addr_reg_subnum']    = $vBaseInfo['addr_reg_subnum'];
            $vParams['addr_reg_apartment'] = $vBaseInfo['addr_reg_apartment'];
            $vParams['diagnosis']     = @$vLastDate['diagnosis'];
//            $vParams['diagnosis_mkb'] = @$vLastDate['diagnosis_mkb'];
            $vParams['doctor_id'] = $_SESSION['User.ID'];
//            $vParams['direction_subject'] = @$vValues[$vPageID]['direction_subject'];
//            $vParams['direction_target']  = @$vValues[$vPageID]['direction_target'];
            $vContainer['_PopupURL'] = CompoundURL('../produce_pdf.html', $vParams);
            $page->handle('jump');
        }
    }


    class ActionPrintRemedialGymnasticsDirection extends HTML_QuickForm_Action
    {
        function perform(&$page, $actionName)
        {
            $page->isFormBuilt() or $page->buildForm();
            $vPageID     =  $page->getAttribute('id');
            $vController =& $page->controller;
            $vContainer  =& $vController->container();

            $vContainer['values'][$vPageID] = $page->exportValues();
            $vContainer['valid'][$vPageID]  = $page->validate();
            $vController->isValid();
            $vDefaults  =& $vContainer['defaults'];
            $vValues    =& $vContainer['values'];
            $vNumDates  = count($vDefaults['surgeries']);
            $vBaseInfo  =& $vValues['BaseInfo'];
            if ( $vNumDates>0 )
                $vLastDate  =& $vValues[DatePageName($vNumDates-1)];
            else
                $vLastDate  = array();

            $vParams    =  array();
            $vParams['pdfpage']     = 'info/remedialgymnastics.pdf';
            $vParams['case_id']     = $vDefaults['BaseInfo']['id'];
            $vParams['insurance_company_id'] = $vBaseInfo['insurance_company_id'];
            $vParams['polis_series']= $vBaseInfo['polis_series'];
            $vParams['polis_number']= $vBaseInfo['polis_number'];
            $vParams['paytype']     = $vBaseInfo['paytype'];
            $vParams['last_name']   = $vBaseInfo['last_name'];
            $vParams['first_name']  = $vBaseInfo['first_name'];
            $vParams['patr_name']   = $vBaseInfo['patr_name'];
            $vParams['born_date']   = DateValueToStr($vBaseInfo['born_date']);
            $vParams['is_male']     = $vBaseInfo['is_male'];
//            $vParams['employment_place']   = $vBaseInfo['employment_place'];
//            $vParams['profession']         = $vBaseInfo['profession'];
            $vParams['addr_reg_street']    = $vBaseInfo['addr_reg_street'];
            $vParams['addr_reg_num']       = $vBaseInfo['addr_reg_num'];
            $vParams['addr_reg_subnum']    = $vBaseInfo['addr_reg_subnum'];
            $vParams['addr_reg_apartment'] = $vBaseInfo['addr_reg_apartment'];
            $vParams['doc_type_id'] = $vBaseInfo['doc_type_id'];
            $vParams['doc_series']  = $vBaseInfo['doc_series'];
            $vParams['doc_number']  = $vBaseInfo['doc_number'];
            $vParams['diagnosis']   = @$vLastDate['diagnosis'];
//            $vParams['diagnosis_mkb'] = @$vLastDate['diagnosis_mkb'];
            $vParams['doctor_id'] = $_SESSION['User.ID'];
            $vContainer['_PopupURL'] = CompoundURL('../produce_pdf.html', $vParams);
            $page->handle('jump');
        }
    }



    class ActionPrintOutEpicrisis extends HTML_QuickForm_Action
    {
        function perform(&$page, $actionName)
        {
            $page->isFormBuilt() or $page->buildForm();
            $vPageID     =  $page->getAttribute('id');
            $vController =& $page->controller;
            $vContainer  =& $vController->container();

            $vContainer['values'][$vPageID] = $page->exportValues();
            $vContainer['valid'][$vPageID]  = $page->validate();
            $vController->isValid();
            $vDefaults  =& $vContainer['defaults'];
            $vValues    =& $vContainer['values'];
            $vNumDates  = count($vDefaults['surgeries']);
            $vBaseInfo  =& $vValues['BaseInfo'];
            if ( $vNumDates>0 )
            {
                $vFirstDate =& $vValues[DatePageName(0)];
                $vLastDate  =& $vValues[DatePageName($vNumDates-1)];
            }
            else
            {
                $vFirstDate = array();
                $vLastDate  = array();
            }

            $vParams    =  array();
            $vParams['pdfpage']     = 'info/out_epicrisis.pdf';
            $vParams['case_id']     = $vDefaults['BaseInfo']['id'];

            $vParams2['case_id']     = $vDefaults['BaseInfo']['id'];
            $vParams2['target']      = @$vValues[$vPageID]['out_epicrisis_target'];
            $vParams2['create_time'] = $vDefaults['BaseInfo']['create_time'];
            $vParams2['last_name']   = $vBaseInfo['last_name'];
            $vParams2['first_name']  = $vBaseInfo['first_name'];
            $vParams2['patr_name']   = $vBaseInfo['patr_name'];
            $vParams2['born_date']   = DateValueToStr($vBaseInfo['born_date']);
            $vParams2['is_male']     = $vBaseInfo['is_male'];
            $vParams2['addr_reg_street']    = $vBaseInfo['addr_reg_street'];
            $vParams2['addr_reg_num']       = $vBaseInfo['addr_reg_num'];
            $vParams2['addr_reg_subnum']    = $vBaseInfo['addr_reg_subnum'];
            $vParams2['addr_reg_apartment'] = $vBaseInfo['addr_reg_apartment'];
            $vParams2['employment_place']   = $vBaseInfo['employment_place'];
            $vParams2['profession']         = $vBaseInfo['profession'];
            $vParams2['insurance_company_id'] = $vBaseInfo['insurance_company_id'];
            $vParams2['polis_series']       = $vBaseInfo['polis_series'];
            $vParams2['polis_number']       = $vBaseInfo['polis_number'];
            $vParams2['paytype']            = $vBaseInfo['paytype'];

            $vParams2['accident']           = @$vFirstDate['accident'];
            $vParams2['accident_datetime']  = DateTimeValueToStr(@$vFirstDate['accident_datetime']);
            $vParams2['accident_place']     = @$vFirstDate['accident_place'];
            $vParams2['trauma_type_id']     = @$vBaseInfo['trauma_type_id'];
            $vParams2['complaints']         = @$vFirstDate['complaints'];
            $vParams2['diagnosis']          = @$vLastDate['diagnosis'];
            $vParams2['diagnosis_mkb']      = @$vLastDate['diagnosis_mkb'];

            $vLastCure = "";
            $vCures    = array();
            for( $i=0; $i<$vNumDates; $i++ )
            {
                $vCure = $vValues[DatePageName($i)]['cure'];
                if ( $vCure != $vLastCure )
                {
                    $vLastCure = $vCure;
                    $vCures[] = $vCure;
                }
            }
            $vParams2['cure'] = $vCure;
            $vParams2['dynamic_id'] = @$vLastDate['dynamic_id'];
            $vParams2['clinical_outcome_id']    = @$vLastDate['clinical_outcome_id'];
            $vParams2['clinical_outcome_notes'] = @$vLastDate['clinical_outcome_notes'];
            $vParams2['doctor_id']  = $_SESSION['User.ID'];

            $vParams2['recomendation']= @$vValues[$vPageID]['out_epicrisis_recomendation'];
            $_SESSION['hold']['out_epicrisis'] = $vParams2;
            $vContainer['_PopupURL'] = CompoundURL('../produce_pdf.html', $vParams);
            $page->handle('jump');
        }
    }


    class ActionPrintStudinfo extends HTML_QuickForm_Action
    {
        function perform(&$page, $actionName)
        {
            $page->isFormBuilt() or $page->buildForm();
            $vPageID     =  $page->getAttribute('id');
            $vController =& $page->controller;
            $vContainer  =& $vController->container();

            $vContainer['values'][$vPageID] = $page->exportValues();
            $vContainer['valid'][$vPageID]  = $page->validate();
            $vController->isValid();
            $vDefaults  =& $vContainer['defaults'];
            $vValues    =& $vContainer['values'];
            $vNumDates  = count($vDefaults['surgeries']);
            $vBaseInfo  =& $vValues['BaseInfo'];
            if ( $vNumDates>0 )
                $vLastDate  =& $vValues[DatePageName($vNumDates-1)];
            else
                $vLastDate  = array();

            $vParams    =  array();
            $vParams['pdfpage']    = 'info/studinfo.pdf';
            $vParams['case_id']    = $vDefaults['BaseInfo']['id'];
            $vParams['last_name']  = $vBaseInfo['last_name'];
            $vParams['first_name'] = $vBaseInfo['first_name'];
            $vParams['patr_name']  = $vBaseInfo['patr_name'];
            $vParams['born_date']  = DateValueToStr($vBaseInfo['born_date']);
            $vDate = @$vDefaults['surgeries'][$vNumDates-1]['date'];
            if ( empty($vDate) )
              $vDate = date('Y-m-d');
            else
              list($vDate,$vJunk) = explode(' ', $vDate);
            $vParams['date']       = $vDate;
            $vParams['diagnosis']  = @$vLastDate['diagnosis'];
            $vParams['doctor_id']  = $_SESSION['User.ID'];
            $vParams['studinfo_type']    = @$vValues[$vPageID]['studinfo_type'];
            $vParams['studinfo_target']  = @$vValues[$vPageID]['studinfo_target'];
            $vParams['studinfo_show_diagnosis'] = @$vValues[$vPageID]['studinfo_show_diagnosis'];
            $vContainer['_PopupURL'] = CompoundURL('../produce_pdf.html', $vParams);

            $page->handle('jump');
        }
    }


    class ActionProcess extends HTML_QuickForm_Action
    {

        function save(&$AController)
        {
            $vDB = GetDB();

            $vContainer =& $AController->container();

            $vDefaults  =& $vContainer['defaults'];
            $vValues    =& $vContainer['values'];
            $vNumDates  = count($vDefaults['surgeries']);

            $vBaseInfo  =& $vValues['BaseInfo'];
            if ( $vNumDates>0 )
            {
                $vFirstDate =& $vValues[DatePageName(0)];
                $vFirstDateDefaults =& $vDefaults[DatePageName(0)];
                $vLastDate  =& $vValues[DatePageName($vNumDates-1)];
            }
            else
            {
                $vFirstDate = array();
                $vFirstDateDefaults = array();
                $vLastDate  = array();
            }

            $vBaseInfoRecord = array();

            $vRecord = array();
            CopyRecordRefValue($vRecord, $vBaseInfo, 'id');
            $vRecord['modify_time'] = $vDB->ConvertToDateTime(time());
            CopyRecordDateValue($vRecord, $vDefaults['BaseInfo'], 'create_time');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'last_name');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'first_name');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'patr_name');
            CopyRecordBoolValue($vRecord, $vBaseInfo, 'is_male');
            CopyRecordDateValue($vRecord, $vBaseInfo, 'born_date');

            CopyRecordRefValue( $vRecord, $vBaseInfo, 'doc_type_id');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'doc_series');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'doc_number');
            CopyRecordRefValue( $vRecord, $vBaseInfo, 'insurance_company_id');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'polis_series');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'polis_number');

            CopyRecordDateValue( $vRecord, $vValues, 'patient_polis_from');
            CopyRecordDateValue( $vRecord, $vValues, 'patient_polis_to');

            CopyRecordStrValue( $vRecord, $vBaseInfo, 'paytype');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'addr_reg_street');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'addr_reg_num');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'addr_reg_subnum');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'addr_reg_apartment');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'addr_phys_street');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'addr_phys_num');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'addr_phys_subnum');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'addr_phys_apartment');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'phone');
            CopyRecordRefValue( $vRecord, $vBaseInfo, 'employment_category_id');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'employment_place');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'profession');
            CopyRecordRefValue( $vRecord, $vBaseInfo, 'trauma_type_id');
            CopyRecordStrValue( $vRecord, $vBaseInfo, 'notes');

            if ( $vNumDates>0 )
            {
                $vRecord['first_doctor_id'] = @$vFirstDateDefaults['user_id'];
                CopyRecordStrValue( $vRecord, $vFirstDate, 'accident');
                CopyRecordDateTimeValue($vRecord, $vFirstDate, 'accident_datetime');
                CopyRecordStrValue( $vRecord, $vFirstDate, 'accident_place');
                CopyRecordRefValue($vRecord,  $vFirstDate, 'antitetanus_id');
                CopyRecordStrValue($vRecord,  $vFirstDate, 'antitetanus_series');
                CopyRecordBoolValue($vRecord, $vFirstDate, 'phone_message_required');
                CopyRecordBoolValue($vRecord, $vFirstDate, 'ice_trauma');
                CopyRecordBoolValue($vRecord, $vFirstDate, 'animal_bite_trauma');
                CopyRecordBoolValue($vRecord, $vFirstDate, 'ixodes_trauma');
                CopyRecordStrValue($vRecord,  $vFirstDate, 'message_number');
                CopyRecordStrValue($vRecord,  $vFirstDate, 'diagnosis');
                CopyRecordStrValue($vRecord,  $vFirstDate, 'diagnosis_mkb');
                CopyRecordDateValue($vRecord, $vFirstDate, 'disability_from_date');
                CopyRecordDateValue($vRecord, $vLastDate,  'next_visit_date');
                CopyRecordRefValue($vRecord,  $vLastDate,  'next_visit_target_id');

                if ( empty($vLastDate['clinical_outcome_id']) )
                    $vRecord['state'] = 'progress';
                else
                    $vRecord['state'] = 'done';
            }
            else
            {
                    $vRecord['state'] = 'init';
            }
            $vCaseID = $vDB->InsertOrUpdateById('emst_cases', $vRecord);
            $vBaseInfo['id'] = $vCaseID;
            $vEmptyRec = array('id'=>'');

            for( $i=0; $i<$vNumDates; $i++ )
            {
                $vDateValues   =& $vValues[DatePageName($i)];
                $vDateDefaults =& $vDefaults['surgeries'][$i];
                $vRecord = array();
                $vRecord['case_id'] = $vCaseID;
//                $vRecord['user_id'] = $_SESSION['User.ID'];
                $vRecord['user_id'] = $vDateDefaults['user_id'];
                $vRecord['date']    = $vDateDefaults['date'];
                $vRecord['id']      = @$vDateDefaults['id'];
                CopyRecordStrValue($vRecord, $vDateValues, 'complaints');
                CopyRecordStrValue($vRecord, $vDateValues, 'objective');
                CopyRecordStrValue($vRecord, $vDateValues, 'diagnosis');
                CopyRecordStrValue($vRecord, $vDateValues, 'diagnosis_mkb');
                CopyRecordRefValue($vRecord, $vDateValues, 'dynamic_id');
                CopyRecordRefValue($vRecord, $vDateValues, 'manipulation_id');
                CopyRecordStrValue($vRecord, $vDateValues, 'manipulation_text');
                CopyRecordStrValue($vRecord, $vDateValues, 'cure');
                CopyRecordStrValue($vRecord, $vDateValues, 'notes');
                CopyRecordRefValue($vRecord, $vDateValues, 'disability');
                if ( empty($vRecord['disability']) )
                    $vRecord['disability'] = 0;

                CopyRecordStrValue($vRecord, $vDateValues, 'ill_refused');
                CopyRecordStrValue($vRecord, $vDateValues, 'ill_sertificat');
                CopyRecordStrValue($vRecord, $vDateValues, 'ill_doc');
                CopyRecordStrValue($vRecord, $vDateValues, 'ill_doc_closed');
                CopyRecordStrValue($vRecord, $vDateValues, 'ill_doc_new');
                CopyRecordBoolValue($vRecord, $vDateValues, 'ill_doc_is_continue');
                CopyRecordDateValue($vRecord, $vDateValues, 'ill_beg_date');
                CopyRecordDateValue($vRecord, $vDateValues, 'ill_end_date');

                CopyRecordRefValue($vRecord,  $vDateValues, 'clinical_outcome_id');
                CopyRecordStrValue($vRecord,  $vDateValues, 'clinical_outcome_notes');
                CopyRecordDateValue($vRecord, $vDateValues, 'next_cec_date');
                CopyRecordDateValue($vRecord, $vDateValues, 'next_visit_date');
                CopyRecordRefValue($vRecord,  $vDateValues, 'next_visit_target_id');
                CopyRecordBoolValue($vRecord, $vDateValues, 'is_cec');
                if ( $vRecord['is_cec'] && empty($vDateDefaults['cec_number']) )
                {
                    $vDateDefaults['cec_number'] = $vDB->Insert('reg_ceces', $vEmptyRec);
                }
                CopyRecordRefValue($vRecord,  $vDateDefaults, 'cec_number');
//                CopyRecordStrValue($vRecord,  $vDateValues, 'cec_number');
                CopyRecordDateValue($vRecord, $vDateValues, 'cec_cureup_date');
//                CopyRecordStrValue($vRecord, $vDateValues, 'cec_members');
                $vID = $vDB->InsertOrUpdateById('emst_surgeries', $vRecord);
                $vDateDefaults['id'] = $vID;
            }
        }

        function perform(&$page, $actionName)
        {
            $this->save($page->controller);
            $vContainer =& $page->controller->container();
            $vDefaults  =& $vContainer['defaults'];
            $vRefererPage = $vDefaults['html_referer'];
            $page->controller->container(true);
            Redirect($vRefererPage);
        }
    }


    class AcceptController extends MyController
    {
        function __construct()
        {
            $this->MyController('docAccept');
            $vDefaults = $this->getDefaults();

            $vDB    = GetDB();
            $vUsers = $vDB->GetRBList('users', 'id', 'full_name');

            $this->AddPageEx(new BaseInfoPage('BaseInfo','Титульная страница'));

            $vSurgeries = $vDefaults['surgeries'];
            for( $i=0; $i<count($vSurgeries); $i++ )
            {
                $vPageID    = DatePageName($i);
                $vPageTitle = Date2Readable($vSurgeries[$i]['date']);
                if ( @$vSurgeries[$i]['is_cec'] )
                   $vPageTitle .= '(ВК)';
                $vUserName  = trim(@$vUsers[$vSurgeries[$i]['user_id']]);
                if ( !empty($vUserName) )
                    $vPageTitle .= ': '.$vUserName;
                if ( $i == 0 )
                    $this->AddPageEx(new FirstPassPage($vPageID, $vPageTitle));
                else
                    $this->AddPageEx(new NonFirstPassPage($vPageID, $vPageTitle));
            }
            $this->AddPageEx(new RGsPage('RGs', 'RG'));
            $this->AddPageEx(new HospitalsPage('Hospitals', 'Стационары'));
            $this->AddPageEx(new MiscDocsPage('MiscDocs', 'Справки и направления'));

            // We actually add these handlers here for the sake of example
            // They can be automatically loaded and added by the controller
            $this->addAction('jump',   new HTML_QuickForm_Action_Jump());
            $this->addAction('submit', new HTML_QuickForm_Action_Submit());

            // The customized actions
            $this->addAction('display', new ActionDisplay());
            $this->addAction('process', new ActionProcess());
            $this->addAction('print_ill_doc', new ActionPrintIllDoc());
            $this->addAction('print_heavity_conclusion', new ActionPrintHeavityConclusion());
            $this->addAction('print_direction',          new ActionPrintDirection());
            $this->addAction('print_physiotherapy_direction',       new ActionPrintPhysiotherapyDirection());
            $this->addAction('print_remedial_gymnastics_direction', new ActionPrintRemedialGymnasticsDirection());
            $this->addAction('print_out_epicrisis',      new ActionPrintOutEpicrisis());
            $this->addAction('print_studinfo',           new ActionPrintStudinfo());
        }


        function& loadDefaults()
        {
            global $gAddTodaySugrery;

            $vDB = GetDB();
            $vID = @$_GET['id'];

            if ( !empty($vID) )
            {
                $vBaseInfo = $vDB->GetById('emst_cases', $vID);
                if (  !is_array($vBaseInfo) )
                    $vBaseInfo = array();
            }
            else
            {
                $vBaseInfo = array();
                $vBaseInfo['create_time'] = $vDB->ConvertToDateTime(time());
                $vBaseInfo['next_visit_date'] = $vDB->ConvertToDate(time());
                CopyRecordStrValue($vBaseInfo, $_GET, 'first_name');
                CopyRecordStrValue($vBaseInfo, $_GET, 'last_name');
                CopyRecordStrValue($vBaseInfo, $_GET, 'patr_name');
            }

            $vResult = array();
            $vResult['BaseInfo'] =& $vBaseInfo;

            if ( !empty($vID) )
            {
                $vSurgeries =& $vDB->SelectList('emst_surgeries',                  // table
                                                 '*',                               // cols
                                                 $vDB->CondEqual('case_id', $vID),  // where
                                                 'date, id');                       // order
            }
            else
                $vSurgeries = array();

            $vNow = $vDB->ConvertToDateTime(time());
            $vToday = ExtractWord($vNow, ' ', 0);
            $vCount = count($vSurgeries);
            if ( $gAddTodaySugrery &&
                 ( $vCount==0 || ExtractWord($vSurgeries[$vCount-1]['date'], ' ', 0) != $vToday )
               )
            {
                $vSurgeries[] = array('date'=>$vNow);
                if ( $vCount == 0 )
                {
                    if ( @DateIsEmpty($vBaseInfo['accident_datetime']) )
                        $vBaseInfo['accident_datetime'] = $vToday;
                    $vBaseInfo['disability_from_date'] = $vToday;
                    $vSurgeries[$vCount]['objective'] = 'Общее состояние удовлетворительное';
                }
                else
                {
                    CopyValues($vSurgeries[$vCount],
                               $vSurgeries[$vCount-1],
                               array('complaints', 'dynamic_id', 'diagnosis', 'diagnosis_mkb', 'disability', 
                                     'ill_refused', 'ill_sertificat', 'ill_doc', 'ill_doc_is_continue', 'ill_beg_date', 'ill_end_date', 'next_cec_date'));

                    if ( !empty($vSurgeries[$vCount-1]['ill_doc_new']) )
                    {
                        $vSurgeries[$vCount]['ill_doc'] = $vSurgeries[$vCount-1]['ill_doc_new'];
                        $vSurgeries[$vCount]['ill_doc_is_continue'] = true;
                    }

                    if ( $vSurgeries[$vCount-1]['ill_end_date'] == $vToday )
                    {
                        $vSurgeries[$vCount]['ill_beg_date'] = DateAddDay($vSurgeries[$vCount-1]['ill_end_date']);
                        $vSurgeries[$vCount]['ill_end_date'] = '';
                    }

                    $vObjectiveList = array();
                    $vClinicalOutcomeID = $vSurgeries[$vCount-1]['clinical_outcome_id'];

                    if ( !empty($vClinicalOutcomeID) )
                    {
                        $vList = GetClinicalOutcomesList();                        
                        $vObjectiveList[] = 'На предыдущем приёме был установлен исход "'.
                                            $vList[$vClinicalOutcomeID].
                                            '". явку объясняет тем, что ... ';
                    }

                    $vNextVisitDate = $vSurgeries[$vCount-1]['next_visit_date'];
                    if ( $vNextVisitDate != '0000-00-00' && $vNextVisitDate < $vToday )
                    {
                      $vObjectiveList[] = 'На приём '.
                              Date2ReadableLong($vNextVisitDate).
                              " не явился, объясняет это тем, что ... ";
                    }

                    $vObjectiveList[] = 'Общее состояние удовлетворительное';

                    $vSurgeries[$vCount]['objective'] = implode ( ".\n", $vObjectiveList);
                    CopyValues($vSurgeries[$vCount],
                               $vSurgeries[$vCount-1],
                               array('cure'));

/*
                    CopyValues($vSurgeries[$vCount],
                               $vSurgeries[$vCount-1],
                               array('objective',  'cure', 'notes'));
*/
                    for( $i=$vCount-1; $i>=0; $i-- )
                    {
                        if ( $vSurgeries[$i]['is_cec'] )
                        {
                            $vSurgeries[$vCount]['next_cec_date'] = $vSurgeries[$i]['cec_cureup_date'];
                            break;
                        }
                    }
                    if ( $vSurgeries[$vCount-1]['next_cec_date'] == $vToday )
                    {
                        $vSurgeries[$vCount]['is_cec'] = 1;
                    }

                }
                $vSurgeries[$vCount]['user_id'] = $_SESSION['User.ID'];

            }

            $vCount = count($vSurgeries);
            $vResult['surgeries'] =& $vSurgeries;

            for( $i=0; $i<$vCount; $i++ )
            {
                $vResult[DatePageName($i)] =& $vSurgeries[$i];
            }

//            CopyValues($vSurgeries[0], $vBaseInfo, array('accident', 'accident_datetime', 'accident_place', 'antitetanus_id', 'antitetanus_series', 'phone_message_required', 'ice_trauma', 'animal_bite_trauma', 'ixodes_trauma', 'message_number', 'diagnosis', 'diagnosis_mkb', 'disability_from_date'));
            if ( $vCount>0 )
                CopyValues($vSurgeries[0], $vBaseInfo, array('accident', 'accident_datetime', 'accident_place', 'antitetanus_id', 'antitetanus_series', 'phone_message_required', 'ice_trauma', 'animal_bite_trauma', 'ixodes_trauma', 'message_number', 'disability_from_date'));

            $vResult['MiscDocs']['studinfo_freed_beg_date'] = @$vBaseInfo['create_time'];
            $vResult['MiscDocs']['studinfo_freed_end_date'] = $vToday;

            $vResult['html_referer'] = $_SESSION['PrevPage'];
            return $vResult;
        }
    }

    $tabbed = new AcceptController();
    $tabbed->run();
?>
