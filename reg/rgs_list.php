<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
require_once('config/config.php');  
require_once('library/cases_table.php');


function ConstructQuery(&$ADB, $AParams)
{
    $vFilter = array();
    $vTable = 'emst_rg LEFT JOIN emst_cases ON emst_rg.case_id = emst_cases.id';

    if ( array_key_exists('case_id', $AParams) )
       $vFilter[] = $ADB->CondEqual('case_id', $AParams['case_id']);
    if ( array_key_exists('beg_date', $AParams) )
       $vFilter[] = $ADB->CondGE('date', $AParams['beg_date']);
    if ( array_key_exists('end_date', $AParams) )
       $vFilter[] = $ADB->CondLT('date', DateAddDay($AParams['end_date']));
       
    $vFilter = implode(' AND ', $vFilter);
    $vOrder = 'date, case_id';
    return array($vTable, $vFilter, $vOrder);
}



class TSearchForm extends HTML_QuickFormEx
{
    function TSearchForm()
    {
        $this->HTML_QuickForm('frmSearch', 'post', $_SERVER['REQUEST_URI']);
        $this->addElement('header',   'Header',          'Поиск');

        $this->addElement('text',     'case_id',   'Номер истории болезни',    array('class'=>'edt_100'));
        $this->addElement('dateex',   'beg_date',  'Начальная дата',    array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>false));
        $this->addElement('dateex',   'end_date',  'Конечная дата',     array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>false));
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
        list($vTable, $vFilter, $vOrder) = ConstructQuery($vDB, $_GET);

        $vTab =& new TTable($vTable, 'emst_rg.*, emst_cases.last_name, emst_cases.first_name, emst_cases.patr_name, emst_cases.born_date, emst_cases.is_male', $vFilter, $vOrder);
        $vTab->AddDateColumn('date',              'Дата');
        $vTab->AddColumn('case_id',               '№', array('align'=>'right'));
        $vTab->AddColumn('id',                    'Фамилия Имя Отчество',   array('align'=>'left', 'fmt'=>'tcfName'));
        $vTab->AddColumn('date',                  'Дата рождения, полных лет', array('align'=>'left', 'fmt'=>'tcfBornDate'));
        $vTab->AddColumn('is_male',               'Пол',           array('align'=>'center', 'fmt'=>'tcfSex'));
        $vTab->AddColumn('area',                  'Область');
        $vTab->AddBoolColumn('done',              'Выполнено');
        $vTab->AddLimTextColumn('description',    'Описание');
        $vTab->AddRowAction('изменить',           '/doc/rg_dir_edit.html?id=');

        $vFilter = array();
        CopyRecordDateValue($vFilter, $_GET, 'beg_date');
        CopyRecordDateValue($vFilter, $_GET, 'end_date');
        $vTab->AddTableAction('печать',  CompoundURL('rgs_list.pdf', $vFilter));
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
    CopyParam($vFilter, $vValues, 'case_id');
    CopyRecordDateValue($vFilter, $vValues, 'beg_date');
    CopyRecordDateValue($vFilter, $vValues, 'end_date');
//        CopyParam($vFilter, $vValues, 'Order');
    Redirect( CompoundURL('rgs_list.html', $vFilter) );
}
else
{
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TData;
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('reg/rgs_list.html');
    $vTemplate->outputObject($vView);
}

?>