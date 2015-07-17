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

        $vCaseID = @$AParams['case_id'];

        if ( !empty($vCaseID) )
            $vFilter[] = $ADB->CondEqual('id', $vCaseID);

        AddQueryParamLike( $ADB, $vFilter, $AParams, 'first_name');
        AddQueryParamLike( $ADB, $vFilter, $AParams, 'last_name');
        AddQueryParamLike( $ADB, $vFilter, $AParams, 'patr_name');

        if ( array_key_exists('paytype', $AParams) && $AParams['paytype']>=0 )
            $vFilter[] = $ADB->CondEqual('paytype', $AParams['paytype']);
        if ( array_key_exists('beg_date', $AParams) && IsValidDate($AParams['beg_date']) )
            $vFilter[] = $ADB->CondGE('create_time', $AParams['beg_date']);
        if ( array_key_exists('end_date', $AParams) && IsValidDate($AParams['end_date']) )
            $vFilter[] = $ADB->CondLT('create_time', DateAddDay($AParams['end_date']));
        if ( array_key_exists('doctor_id', $AParams) && !empty($AParams['doctor_id']) )
        {
            $vFilter[] = 'id IN (SELECT case_id FROM emst_surgeries WHERE '.$ADB->CondEqual('user_id', $AParams['doctor_id']).')';
        }

        $vFilter = implode(' AND ', $vFilter);

        $vOrder = 'emst_cases.id';
        return array($vTable, $vFilter, $vOrder);
    }



  class TSearchForm extends HTML_QuickFormEx
  {
    function TSearchForm()
    {
      $vDB = GetDB();
      $this->HTML_QuickForm('frmSearch', 'post', $_SERVER['REQUEST_URI']);
      $this->addElement('header',   'Header',          'Фильтр');

/*
      $this->addElement('text',     'case_id',         'Номер истории болезни',    array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'last_name',       'Фамилия',                  array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'first_name',      'Имя',                      array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'patr_name',       'Отчество',                 array('style'=>'WIDTH: 180px'));
*/
      $this->addElement('text',     'case_id',         'Номер истории болезни',    array('class'=>'edt_100'));
      $this->addElement('select',   'doctor_id',       'Врач', $vDB->GetRBList('users','id', 'full_name', true));

      $this->addElement('text',     'last_name',       'Фамилия',                  array('class'=>'edt_100'));
      $this->addElement('text',     'first_name',      'Имя',                      array('class'=>'edt_100'));
      $this->addElement('text',     'patr_name',       'Отчество',                 array('class'=>'edt_mid'));
      $this->addElement('select',   'paytype',         'тип',                      array(-1=>'', 0=>'ОМС', 1=>'ДМС'));


      $this->addElement('dateex',   'beg_date',        'Начальная дата',    array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));
      $this->addElement('dateex',   'end_date',        'Конечная дата',     array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>true));

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

            $vTab =& new TCasesTable(
                            $vTable,
                            'emst_cases.*,'.gCaseWithBadDoc.' as is_bad_docs',
                            $vFilter,
                            $vOrder);
            $vTab->AddBoolColumn('is_bad_docs', 'ПД');

            $vTab->AddRowAction('приём',      'accept.html?id=', '../images/sugrery_24x24.gif', 24, 24);
            $vTab->AddRowAction('изменение',  'case_edit.html?id=', '../images/edit_24x24.gif', 24, 24);
            $vTab->AddRowAction('печать',     '../reg/case.pdf?id=', '../images/print_24x24.gif', 24, 24);
//            $vTab->AddRowAction('"заключение..."',  '/info/conclusion.html?id=');

//            $vTab->AddTableAction('новая',  'case_edit.html');

            $vFilter = array();
            CopyRecordRefValue($vFilter, $_GET, 'case_id');
            CopyRecordRefValue($vFilter, $_GET, 'doctor_id');
            CopyRecordStrValue($vFilter, $_GET, 'first_name');
            CopyRecordStrValue($vFilter, $_GET, 'last_name');
            CopyRecordStrValue($vFilter, $_GET, 'patr_name');
            CopyRecordStrValue($vFilter, $_GET, 'paytype');
            CopyRecordDateValue($vFilter, $_GET, 'beg_date');
            CopyRecordDateValue($vFilter, $_GET, 'end_date');
            $vTab->AddTableAction('печать',  CompoundURL('cases_list.pdf', $vFilter));

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
        CopyParam($vFilter, $vValues, 'paytype');
        CopyRecordDateValue($vFilter, $vValues, 'beg_date');
        CopyRecordDateValue($vFilter, $vValues, 'end_date');

//        CopyParam($vFilter, $vValues, 'Order');
        Redirect( CompoundURL('cases.html', $vFilter) );
    }
    else
    {
        $vTemplate =& CreateTemplate();
        $vRenderer =& CreateRenderer($vTemplate);
        $vForm->accept($vRenderer);
        $vView =& new TData;
        $vView->form = $vRenderer->toObject();
        $vTemplate->compile('doc/cases.html');
        $vTemplate->outputObject($vView);
    }
?>