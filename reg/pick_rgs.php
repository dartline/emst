<?php

#####################################################################
#
# Травмпункт. (c) 2005 Vista
#
#####################################################################

require_once('config/config.php');
require_once('library/cases_table.php');

/*
я придумал 4 варианта запроса, реализовал первый -- посмотрим, что выйдет 

SELECT emst_cases.* 
FROM emst_cases 
WHERE 
    next_visit_date >= '2005-01-15' AND 
    next_visit_date <  '2006-01-16' AND
    id IN (SELECT case_id FROM 
    emst_rg) 
ORDER BY 
    id;


SELECT emst_cases.* 
FROM emst_cases 
WHERE 
    next_visit_date >= '2005-01-15' AND 
    next_visit_date <  '2006-01-16' AND
    id IN (SELECT case_id FROM emst_rg WHERE emst_cases.id = emst_rg.case_id)  
ORDER BY 
    id;


SELECT emst_cases.* 
FROM emst_cases 
WHERE 
    next_visit_date >= '2005-01-15' AND 
    next_visit_date <  '2006-01-16' AND
    EXISTS (SELECT id FROM emst_rg WHERE emst_cases.id = emst_rg.case_id) 
ORDER BY 
    id;

SELECT DISTINCT emst_cases.*
FROM emst_cases 
JOIN emst_rg ON emst_rg.case_id = emst_cases.id
WHERE 
    next_visit_date >= '2005-01-15' AND 
    next_visit_date <  '2006-01-16'
ORDER BY 
    id;

*/

function ConstructQuery(&$ADB, $AParams)
{
    $vFilter = array();

    $vTable = 'emst_cases LEFT JOIN rb_vistit_targets ON rb_vistit_targets.id=emst_cases.next_visit_target_id';

//        if ( array_key_exists('case_id', $AParams) )
//           $vFilter[] = $ADB->CondEqual('case_id', $AParams['case_id']);
    if ( array_key_exists('beg_date', $AParams) )
	$vFilter[] = $ADB->CondGE('next_visit_date', $AParams['beg_date']);
    if ( array_key_exists('end_date', $AParams) )
	$vFilter[] = $ADB->CondLT('next_visit_date', DateAddDay($AParams['end_date']));
    $vFilter[] = 'emst_cases.id IN (SELECT case_id FROM emst_rg)';

    $vFilter = implode(' AND ', $vFilter);
    $vOrder = 'rb_vistit_targets.name, emst_cases.id';
    return array($vTable, $vFilter, $vOrder);
}



class TSearchForm extends HTML_QuickFormEx
{
    function TSearchForm()
    {
	$this->HTML_QuickForm('frmSearch', 'post', $_SERVER['REQUEST_URI']);
	$this->addElement('header', 'Header',   'Поиск');

        $this->addElement('dateex', 'beg_date', 'Начальная дата', array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>false));
        $this->addElement('dateex', 'end_date', 'Конечная дата',  array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>false));
        $this->addElement('submit', 'Submit',   'Установить фильтр');

        $this->applyFilter('_ALL_', 'trim');
        $this->setDefaults($_GET);
    }
}


class TData extends TBaseView
{
    function GetTable()
    {
        $vDB = GetDB();
        list($vTable, $vFilter, $vOrder) = ConstructQuery($vDB, $_GET);

        $vTab =& new TTable($vTable, 'emst_cases.id, emst_cases.last_name, emst_cases.first_name, emst_cases.patr_name, rb_vistit_targets.name as vistit_target', $vFilter, $vOrder);
//            $vTab->AddDateColumn('date',              'Дата');
        $vTab->AddColumn('id',                    '№', array('align'=>'right'));
        $vTab->AddColumn('id',                    'Фамилия Имя Отчество',   array('align'=>'left', 'fmt'=>'tcfName'));
        $vTab->AddColumn('vistit_target',         'Кабинет');
//            $vTab->AddColumn('date',                  'Дата рождения, полных лет', array('align'=>'left', 'fmt'=>'tcfBornDate'));
//            $vTab->AddColumn('is_male',               'Пол',           array('align'=>'center', 'fmt'=>'tcfSex'));
//            $vTab->AddColumn('area',                  'Область');
//            $vTab->AddBoolColumn('done',              'Выполнено');
//            $vTab->AddLimTextColumn('description',    'Описание');
//            $vTab->AddRowAction('изменить',           '/doc/rg_dir_edit.html?id=');
        $vResult = $vTab->ProduceHTML($vDB, (@($_GET['PageIdx']))+0, 20);
        return $vResult;
    }
}

// =======================================================================
if ( !array_key_exists('beg_date', $_GET) )
    $_GET['beg_date'] = date('Y-m-d');
if ( !array_key_exists('end_date', $_GET) )
    $_GET['end_date'] = date('Y-m-d');

RegisterListParams();
$vForm =& new TSearchForm();

if ( $vForm->validate() )
{
    $vValues  = $vForm->getSubmitValues();
    $vFilter = array();
//        CopyParam($vFilter, $vValues, 'case_id');
    CopyRecordDateValue($vFilter, $vValues, 'beg_date');
    CopyRecordDateValue($vFilter, $vValues, 'end_date');
//        CopyParam($vFilter, $vValues, 'Order');
    Redirect( CompoundURL('pick_rgs.html', $vFilter) );
}
else
{
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TData;
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('reg/pick_rgs.html');
    $vTemplate->outputObject($vView);
}

?>