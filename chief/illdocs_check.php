<?php

#####################################################################
#
# Травмпункт. (c) 2006 Vista
#
#####################################################################

    require_once('config/config.php');
    require_once('library/cases_table.php');


function ConstructSurgeriesQuery(&$ADB, $AParams)
{
    $vFilter = array();

    $vTable = 'emst_surgeries '.
              '  LEFT JOIN emst_cases ON emst_surgeries.case_id = emst_cases.id'.
              '  LEFT JOIN rb_employment_categories ON rb_employment_categories.id = emst_cases.employment_category_id'.
              '  LEFT JOIN rb_clinical_outcomes     ON emst_surgeries.clinical_outcome_id = rb_clinical_outcomes.id';

    $vFilter[] = '(emst_surgeries.disability=2 OR '.
                 '  (emst_surgeries.disability=0 AND rb_employment_categories.need_ill_doc!=0))';
    $vFilter[] = 'emst_surgeries.ill_refused=0';
    $vFilter[] = 'ill_sertificat=0';
    $vFilter[] = '(emst_surgeries.clinical_outcome_id IS NULL OR rb_clinical_outcomes.can_skip_ill_doc_on_disability=0)';
    $vFilter[] = 'emst_cases.disability_from_date = DATE(emst_surgeries.date)';

    if ( array_key_exists('beg_date', $AParams) && IsValidDate($AParams['beg_date']) )
        $vFilter[] = $ADB->CondGE('date', $AParams['beg_date']);
    if ( array_key_exists('end_date', $AParams) && IsValidDate($AParams['end_date']) )
        $vFilter[] = $ADB->CondLT('date', DateAddDay($AParams['end_date']));

    $vFilter = implode(' AND ', $vFilter);

    $vOrder = 'emst_surgeries.date, emst_surgeries.id';
    return array($vTable, $vFilter, $vOrder);
}



class TSearchForm extends HTML_QuickFormEx
{
    function TSearchForm()
    {
        $vDB = GetDB();
        $this->HTML_QuickForm('frmSearch', 'post', $_SERVER['REQUEST_URI']);
        $this->addElement('header',   'Header',   'Фильтр');

        $this->addElement('dateex',   'beg_date', 'Начальная дата',    array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));
        $this->addElement('dateex',   'end_date', 'Конечная дата',     array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));
        $this->addElement('submit',   'Submit',   'Установить фильтр');

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
                            'emst_surgeries.case_id, emst_surgeries.date, emst_surgeries.user_id,'.
                            'emst_surgeries.diagnosis, emst_surgeries.diagnosis_mkb,'.
                            'emst_cases.first_name, emst_cases.last_name, emst_cases.patr_name, emst_cases.born_date, emst_cases.is_male,'.
                            gCaseWithBadDoc.' as is_bad_docs,'.
                            gSurgeryWithBadIllDoc.' as is_bad_illdoc',
                            $vFilter, $vOrder, 'case_id');

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

        $vTab->AddRowAction('изменение',  '../doc/case_edit.html?id=', '../images/edit_24x24.gif', 24, 24);
        $vTab->AddRowAction('печать',     '../reg/case.pdf?id=', '../images/print_24x24.gif', 24, 24);

        $vFilter = array();
        CopyRecordDateValue($vFilter, $_GET, 'beg_date');
        CopyRecordDateValue($vFilter, $_GET, 'end_date');

        $vTab->AddTableAction('печать',  CompoundURL('illdocs_check.pdf', $vFilter));

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
    CopyRecordDateValue($vFilter, $vValues, 'beg_date');
    CopyRecordDateValue($vFilter, $vValues, 'end_date');

//        CopyParam($vFilter, $vValues, 'Order');
    Redirect( CompoundURL('illdocs_check.html', $vFilter) );
}
else
{
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TData;
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('chief/illdocs_check.html');
    $vTemplate->outputObject($vView);
}
?>