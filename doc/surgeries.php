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

    $vTable = 'emst_surgeries '.
              '  LEFT JOIN emst_cases ON emst_cases.id=emst_surgeries.case_id'.
              '  LEFT JOIN rb_employment_categories ON emst_cases.employment_category_id = rb_employment_categories.id'.
              '  LEFT JOIN rb_clinical_outcomes     ON emst_surgeries.clinical_outcome_id = rb_clinical_outcomes.id';

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

    if ( array_key_exists('is_primary', $AParams) )
    {
        $vFilterPart = gSurgeryIsPrimary;
        if ( $AParams['is_primary'] === '0' )
          $vFilter[] = 'NOT '.$vFilterPart;
        else if ( $AParams['is_primary'] === '1' )
          $vFilter[] = $vFilterPart;
    }

    if ( array_key_exists('empty_diagnosis_mkb', $AParams) && $AParams['empty_diagnosis_mkb'] )
    {
        $vFilter[] = 'emst_surgeries.diagnosis_mkb = \'\'';
    }

    if ( array_key_exists('is_bad_doc', $AParams) )
    {
        $vFilterPart = gCaseWithBadDoc;
        if ( $AParams['is_bad_doc'] === '0' )
            $vFilter[] = 'NOT '.$vFilterPart;
        else if ( $AParams['is_bad_doc'] === '1' )
            $vFilter[] = $vFilterPart;
    }

    if ( array_key_exists('is_bad_illdoc', $AParams) )
    {
        $vFilterPart = gSurgeryWithBadIllDoc;
        if ( $AParams['is_bad_illdoc'] === '0' )
            $vFilter[] = 'NOT '.$vFilterPart;
        else if ( $AParams['is_bad_illdoc'] === '1' )
            $vFilter[] = $vFilterPart;
    }

    if ( array_key_exists('eisoms_status', $AParams) && $AParams['eisoms_status'] !== '' )
    {
        $vFilter[] = $ADB->CondEqual('eisoms_status', $AParams['eisoms_status']);
	$vFilter[] = 'emst_cases.paytype = 0';
    }

    if ( array_key_exists('is_lost_outcome', $AParams) && $AParams['is_lost_outcome'] )
    {
        $vFilter[] = gLostOutcome;
    }

    $vFilter = implode(' AND ', $vFilter);

    $vOrder = 'emst_surgeries.date, emst_surgeries.id';
    return array($vTable, $vFilter, $vOrder);
}


function tcfEISOMS($AID, &$ARow)
{
    switch ( $ARow['eisoms_status'] )
    {
    case 0:  return htmlspecialchars('не отправлено');
    case 1:  return htmlspecialchars('не принято: '.$ARow['eisoms_message']);
    case 2:  return htmlspecialchars('принято');
    default: return htmlspecialchars('неизвестный код ['.$ARow['eisoms_status'].']');
    }
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

        $this->addElement('checkbox', 'empty_diagnosis_mkb',  'Код диагноза по МКБ не указан');

        $this->addElement('select',   'is_primary',      'Первичные',
                          array(''=>'', '0'=>'только повторные', '1'=>'только первичные',),
                          array('style'=>'WIDTH: 180px'));

        $this->addElement('select',   'is_bad_doc',      'ПД',
                          array(''=>'', '0'=>'только нормальные', '1'=>'только с проблемами',),
                          array('style'=>'WIDTH: 180px'));

        $this->addElement('select',   'is_bad_illdoc',      'Проблемы с б/л',
                          array(''=>'', '0'=>'только нормальные', '1'=>'только с проблемами',),
                          array('style'=>'WIDTH: 180px'));

        $this->addElement('select',   'eisoms_status',      'ЕИС ОМС',
                          array(''=>'', '0'=>'не отправленные', '1'=>'не принятые', '2'=>'принятые'),
                          array('style'=>'WIDTH: 180px'));

        $this->addElement('checkbox',   'is_lost_outcome',      'Только с не указаным исходом');

      /*      $this->addElement('select',   'Order',           'Упорядочить по',
                          array_values( GetPropertiesSortOrder() ),
                          array('style'=>'WIDTH: 180px'));
      */
        $this->addElement('submit',   'Submit',       'Установить фильтр');

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

        $vTab =& new TTable($vTable,
                            'emst_surgeries.*, '.
                            'emst_cases.first_name, emst_cases.last_name, emst_cases.patr_name, emst_cases.born_date, emst_cases.is_male,'.
                            gSurgeryIsPrimary.' as is_primary, '.
                            gCaseWithBadDoc.' as is_bad_docs,'.
                            gSurgeryWithBadIllDoc.' as is_bad_illdoc,'.
                            gLostOutcome.' as is_lost_outcome',
                            $vFilter, $vOrder, 'case_id');
        $vTab->AddBoolColumn('is_primary',            'Перв.');
        $vTab->AddColumn('case_id',               '№', array('align'=>'right'));
        $vTab->AddDateColumn('date',              'Дата и время приёма');
        $vTab->AddColumn('user_id',               'Врач',   array('align'=>'left', 'fmt'=>'tcfUserName'));
        $vTab->AddColumn('id',                    'Фамилия Имя Отчество',   array('align'=>'left', 'fmt'=>'tcfName'));
        $vTab->AddColumn('date',                  'Дата рождения, полных лет', array('align'=>'left', 'fmt'=>'tcfBornDate'));
        $vTab->AddColumn('is_male',               'Пол',           array('align'=>'center', 'fmt'=>'tcfSex'));
        $vTab->AddTextColumn('diagnosis',         'Диагноз');
        $vTab->AddColumn('diagnosis_mkb',         'МКБ');
        $vTab->AddBoolColumn('is_bad_docs',       'Пробл. с док.');
        $vTab->AddBoolColumn('is_bad_illdoc',     'Пробл. с б/л');
        $vTab->AddBoolColumn('is_lost_outcome',   'Исход не указан');
        $vTab->AddColumn('id',                    'ЕИС ОМС',   array('align'=>'left', 'fmt'=>'tcfEISOMS'));

        $vTab->AddRowAction('изменение',  'case_edit.html?id=', '../images/edit_24x24.gif', 24, 24);
        $vTab->AddRowAction('печать',     '../reg/case.pdf?id=', '../images/print_24x24.gif', 24, 24);

/*
        $vTab->AddRowAction('приём',      'accept.html?id=', '../images/sugrery_24x24.gif', 24, 24);
        $vTab->AddRowAction('изменение',  'case_edit.html?id=', '../images/edit_24x24.gif', 24, 24);
        $vTab->AddRowAction('печать',     '../reg/case.pdf?id=', '../images/print_24x24.gif', 24, 24);
*/
        $vFilter = array();
        CopyRecordRefValue($vFilter, $_GET, 'case_id');
        CopyRecordRefValue($vFilter, $_GET, 'doctor_id');
        CopyRecordStrValue($vFilter, $_GET, 'first_name');
        CopyRecordStrValue($vFilter, $_GET, 'last_name');
        CopyRecordStrValue($vFilter, $_GET, 'patr_name');
        CopyRecordDateValue($vFilter, $_GET, 'beg_date');
        CopyRecordDateValue($vFilter, $_GET, 'end_date');
        CopyRecordStrValue($vFilter, $_GET, 'empty_diagnosis_mkb');
        CopyRecordStrValue($vFilter, $_GET, 'is_primary');
        CopyRecordStrValue($vFilter, $_GET, 'is_bad_doc');
        CopyRecordStrValue($vFilter, $_GET, 'is_bad_illdoc');
        CopyRecordStrValue($vFilter, $_GET, 'eisoms_status');
        CopyRecordBoolValue($vFilter, $_GET, 'is_lost_outcome');

        

//            $vTab->AddTableAction('печать',  CompoundURL('cases_list.pdf', $vFilter));

        $vResult = $vTab->ProduceHTML($vDB, GetPageIdxOrLast(), 20);
        return $vResult;
    }
}

// =======================================================================

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
    CopyParam($vFilter, $vValues, 'is_primary');
    CopyParam($vFilter, $vValues, 'empty_diagnosis_mkb');
    CopyParam($vFilter, $vValues, 'is_bad_doc');
    CopyParam($vFilter, $vValues, 'is_bad_illdoc');
    CopyParam($vFilter, $vValues, 'eisoms_status');
    CopyRecordBoolValue($vFilter, $vValues, 'is_lost_outcome');

//        CopyParam($vFilter, $vValues, 'Order');
    Redirect( CompoundURL('surgeries.html', $vFilter) );
}
else
{
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TData;
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('doc/surgeries.html');
    $vTemplate->outputObject($vView);
}
?>