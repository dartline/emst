<?php

#####################################################################
#
# Травмпункт. (c) 2005 Vista
#
#####################################################################

require_once('config/config.php');
require_once('library/cases_table.php');


function ConstructCaseQuery(&$ADB, $AParams)
{
    $vFilter = array();

    $vTable = 'emst_cases';

    $vFilter[] = 'phone_message_required != 0';
    if ( array_key_exists('beg_date', $AParams) )
        $vFilter[] = $ADB->CondGE('create_time', $AParams['beg_date']);
    if ( array_key_exists('end_date', $AParams) )
	$vFilter[] = $ADB->CondLT('create_time', DateAddDay($AParams['end_date']));

    $vFilter = implode(' AND ', $vFilter);
    $vOrder = 'emst_cases.id';
    return array($vTable, $vFilter, $vOrder);
}



class TSearchForm extends HTML_QuickFormEx
{
    function TSearchForm()
    {
	$this->HTML_QuickForm('frmSearch', 'post', $_SERVER['REQUEST_URI']);
        $this->addElement('header',   'Header',          'Поиск');

        $this->addElement('dateex',   'beg_date',  'Начальная дата',    array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>false));
        $this->addElement('dateex',   'end_date',  'Конечная дата',     array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>false));
/*        $this->addElement('select',   'Order',           'Упорядочить по',
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
        list($vTable, $vFilter, $vOrder) = ConstructCaseQuery($vDB, $_GET);
        $vTab = new TTable($vTable, 'emst_cases.*', $vFilter, $vOrder, 'id');
        $vTab->AddColumn('id',                    '№', array('align'=>'right'));
        $vTab->AddDateColumn('create_time',       'Дата и время обращения');
        $vTab->AddColumn('first_doctor_id',       'Врач', array('fmt'=>'tcfUserName'));
        $vTab->AddColumn('id',                    'Фамилия Имя Отчество',array('align'=>'left', 'fmt'=>'tcfName'));
        $vTab->AddColumn('create_time',           'Дата рождения, полных лет', array('align'=>'left', 'fmt'=>'tcfBornDate'));
        $vTab->AddColumn('is_male',               'Пол',           array('align'=>'center', 'fmt'=>'tcfSex'));
        $vTab->AddColumn('id',                    'Адрес',         array('align'=>'left', 'fmt'=>'tcfAddress'));
        $vTab->AddColumn('phone',                 'Телефон');
        $vTab->AddTextColumn('accident',          'Происшествие');
        $vTab->AddTextColumn('accident_datetime', 'Дата и время происшествия');
        $vTab->AddTextColumn('diagnosis',         'Диагноз');
        $vTab->AddColumn('message_number',        'Телефонограмма');
	
        $vFilter = array();
        CopyRecordDateValue($vFilter, $_GET, 'beg_date');
        CopyRecordDateValue($vFilter, $_GET, 'end_date');
        $vTab->AddTableAction('печать',  CompoundURL('phone_messages_list.pdf', $vFilter));

        $vResult = $vTab->ProduceHTML($vDB, $_GET['PageIdx']+0, 20);
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
    CopyRecordDateValue($vFilter, $vValues, 'beg_date');
    CopyRecordDateValue($vFilter, $vValues, 'end_date');
//        CopyParam($vFilter, $vValues, 'Order');
    Redirect( CompoundURL('phone_messages_list.html', $vFilter) );
}
else
{
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TData;
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('reg/phone_messages_list.html');
    $vTemplate->outputObject($vView);
}

?>