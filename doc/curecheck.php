<?php

#####################################################################
#
# Травмпункт. (c) 2005 Vista
#
#####################################################################

require_once('config/config.php');
require_once('library/cases_table.php');


function ConstructSurgeriesQuery(&$ADB, $AParams)
{
    $vFilter = array();

    $vTable = 'emst_surgeries LEFT JOIN emst_cases ON emst_cases.id=emst_surgeries.case_id';
    $vCaseID = @$AParams['case_id'];

    if ( !empty($vCaseID) )
        $vFilter[] = $ADB->CondEqual('case_id', $vCaseID);

    AddQueryParamLike( $ADB, $vFilter, $AParams, 'first_name');
    AddQueryParamLike( $ADB, $vFilter, $AParams, 'last_name');
    AddQueryParamLike( $ADB, $vFilter, $AParams, 'patr_name');
    if ( array_key_exists('beg_date', $AParams) && IsValidDate($AParams['beg_date']) )
        $vFilter[] = $ADB->CondGE('date', $AParams['beg_date']);
    if ( array_key_exists('end_date', $AParams) && IsValidDate($AParams['end_date']) )
        $vFilter[] = $ADB->CondLT('date', DateAddDay($AParams['end_date']));
    if ( array_key_exists('doctor_id', $AParams) && !empty($AParams['doctor_id']) )
        $vFilter[] = $ADB->CondEqual('user_id', $AParams['doctor_id']);

    $vFilter = implode(' AND ', $vFilter);
    $vOrder = 'emst_surgeries.case_id, emst_surgeries.date, emst_surgeries.id';
    return array($vTable, $vFilter, $vOrder);
}


class TSearchForm extends HTML_QuickFormEx
{
    function TSearchForm()
    {
        $vDB = GetDB();
        $this->HTML_QuickForm('frmSearch', 'post', $_SERVER['REQUEST_URI']);
        $this->addElement('header',   'Header',          'Фильтр');

        $this->addElement('text',     'case_id',         'Номер истории болезни',    array('class'=>'edt_100'));
        $this->addElement('select',   'doctor_id',       'Врач', $vDB->GetRBList('users','id', 'full_name', true));

        $this->addElement('text',     'last_name',       'Фамилия',                  array('class'=>'edt_100'));
        $this->addElement('text',     'first_name',      'Имя',                      array('class'=>'edt_100'));
        $this->addElement('text',     'patr_name',       'Отчество',                 array('class'=>'edt_mid'));

        $this->addElement('dateex',   'beg_date',        'Начальная дата',    array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));
        $this->addElement('dateex',   'end_date',        'Конечная дата',     array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));

        $this->addElement('checkbox', 'show_diagnosis',  'Показывать диагноз');
        $this->addElement('checkbox', 'show_cure',       'Показывать лечение');

        $this->addElement('submit',   'Submit',          'Установить фильтр');

        $this->applyFilter('_ALL_', 'trim');
        $this->setDefaults($_GET);
    }
}


class TData extends TBaseView
{
    function GetTable()
    {
        $vDB = GetDB();
        list($vTable, $vFilter, $vOrder) = ConstructSurgeriesQuery($vDB, $_GET);
        $vShowDiagnosis = @$_GET['show_diagnosis'];
        $vShowCure      = @$_GET['show_cure'];

        $vTab =& new TTable($vTable,
                            'emst_surgeries.*, '.
                            'emst_cases.first_name, emst_cases.last_name, emst_cases.patr_name, emst_cases.born_date, emst_cases.is_male',
                            $vFilter, $vOrder, 'case_id');
        $vTab->AddColumn('case_id',               '№', array('align'=>'right'));
        $vTab->AddDateColumn('date',              'Дата и время приёма');
        $vTab->AddColumn('user_id',               'Врач',   array('align'=>'left', 'fmt'=>'tcfUserName'));
        $vTab->AddColumn('id',                    'Фамилия Имя Отчество',   array('align'=>'left', 'fmt'=>'tcfName'));
//        $vTab->AddColumn('date',                  'Дата рождения, полных лет', array('align'=>'left', 'fmt'=>'tcfBornDate'));
//        $vTab->AddColumn('is_male',               'Пол',           array('align'=>'center', 'fmt'=>'tcfSex'));
        $vTab->AddTextColumn('objective',         'Объективный статус');

        if ( $vShowDiagnosis )
            $vTab->AddTextColumn('diagnosis',         'Диагноз');
//        $vTab->AddColumn('diagnosis_mkb',         'МКБ');
        if ( $vShowCure )
            $vTab->AddTextColumn('cure',              'Лечение');

        $vTab->AddRowAction('изменение',  'case_edit.html?id=', '../images/edit_24x24.gif', 24, 24);
        $vTab->AddRowAction('печать',     '../reg/case.pdf?id=', '../images/print_24x24.gif', 24, 24);

        $vFilter = array();
        CopyRecordRefValue($vFilter, $_GET, 'case_id');
        CopyRecordRefValue($vFilter, $_GET, 'doctor_id');
        CopyRecordStrValue($vFilter, $_GET, 'first_name');
        CopyRecordStrValue($vFilter, $_GET, 'last_name');
        CopyRecordStrValue($vFilter, $_GET, 'patr_name');
        CopyRecordDateValue($vFilter, $_GET, 'beg_date');
        CopyRecordDateValue($vFilter, $_GET, 'end_date');

//            $vTab->AddTableAction('печать',  CompoundURL('cases_list.pdf', $vFilter));

        $vResult = $vTab->ProduceHTML($vDB, GetPageIdxOrLast(), 20);
        return $vResult;
    }
}

// =======================================================================

if ( empty($_GET['beg_date']) )
{
    $_GET['show_diagnosis'] = 1;
}

RegisterListParams();
$vForm =& new TSearchForm();

if ( $vForm->validate() )
{
    $vValues  = $vForm->getSubmitValues();
    $vFilter = array();
    CopyRecordRefValue($vFilter, $vValues, 'case_id');
    CopyRecordRefValue($vFilter, $vValues, 'doctor_id');
    CopyParam($vFilter, $vValues, 'last_name');
    CopyParam($vFilter, $vValues, 'first_name');
    CopyParam($vFilter, $vValues, 'patr_name');
    CopyRecordDateValue($vFilter, $vValues, 'beg_date');
    CopyRecordDateValue($vFilter, $vValues, 'end_date');
    CopyParam($vFilter, $vValues, 'show_diagnosis');
    CopyParam($vFilter, $vValues, 'show_cure');

//        CopyParam($vFilter, $vValues, 'Order');
    Redirect( CompoundURL('curecheck.html', $vFilter) );
}
else
{
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TData;
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('doc/curecheck.html');
    $vTemplate->outputObject($vView);
}
?>