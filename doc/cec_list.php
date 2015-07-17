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

        $vTable = 'emst_surgeries '.
                  ' JOIN emst_cases ON emst_surgeries.case_id = emst_cases.id';

        $vFilter[] = $ADB->CondEqual('emst_surgeries`.`is_cec', 1);
        if ( array_key_exists('beg_date', $AParams) )
           $vFilter[] = $ADB->CondGE('emst_surgeries`.`date', $AParams['beg_date']);
        if ( array_key_exists('end_date', $AParams) )
           $vFilter[] = $ADB->CondLT('emst_surgeries`.`date', DateAddDay($AParams['end_date']));

        $vFilter = implode(' AND ', $vFilter);
        $vOrder = 'emst_surgeries.cec_number, emst_surgeries.date, emst_surgeries.case_id';
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
            list($vTable, $vFilter, $vOrder) = ConstructCaseQuery($vDB, $_GET);

            $vTab =& new TTable($vTable, 'emst_cases.*, emst_surgeries.cec_number as cec_number, emst_surgeries.case_id as case_id, emst_surgeries.date as cec_date, emst_surgeries.user_id as doctor_id', $vFilter, $vOrder, 'case_id');
            $vTab->AddColumn('cec_number',   '№ ВК');
            $vTab->AddDateColumn('cec_date', 'Дата');
            $vTab->AddColumn('case_id',      '№', array('align'=>'right'));
            $vTab->AddColumn('doctor_id',    'Врач',   array('align'=>'left', 'fmt'=>'tcfUserName'));
            $vTab->AddColumn('id',           'Фамилия Имя Отчество',   array('align'=>'left', 'fmt'=>'tcfName'));
            $vTab->AddColumn('cec_date',     'Дата рождения, полных лет', array('align'=>'left', 'fmt'=>'tcfBornDate'));
            $vTab->AddColumn('is_male',      'Пол',           array('align'=>'center', 'fmt'=>'tcfSex'));
            $vTab->AddRowAction('приём',     'accept.html?id=', '../images/sugrery_24x24.gif', 24, 24);
            $vTab->AddRowAction('изменение', 'case_edit.html?id=', '../images/edit_24x24.gif', 24, 24);
            $vTab->AddRowAction('печать',    '../reg/case.pdf?id=', '../images/print_24x24.gif', 24, 24);


            $vTab->AddTextColumn('diagnosis',  'Диагноз');
            $vTab->AddColumn('diagnosis_mkb',  'МКБ');

//            $vTab->AddColumn('antitetanus_series', 'Серия');

            $vFilter = array();
            CopyRecordDateValue($vFilter, $_GET, 'beg_date');
            CopyRecordDateValue($vFilter, $_GET, 'end_date');
            $vTab->AddTableAction('печать',  CompoundURL('cec_list.pdf', $vFilter));
//            $vTab->AddTableAction('сводный отчет',  CompoundURL('antitetanuses_report.pdf', $vFilter));
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
        Redirect( CompoundURL('cec_list.html', $vFilter) );
    }
    else
    {
        $vTemplate =& CreateTemplate();
        $vRenderer =& CreateRenderer($vTemplate);
        $vForm->accept($vRenderer);
        $vView =& new TData;
        $vView->form = $vRenderer->toObject();
        $vTemplate->compile('doc/cec_list.html');
        $vTemplate->outputObject($vView);
    }
?>