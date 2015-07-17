<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################
    require_once('config/config.php');
    require_once('library/cases_table.php');

/*
    function ConstructQuery(&$ADB, $AParams)
    {
        $vFilter = array();

        $vTable = 'emst_rg '.
                  ' LEFT JOIN emst_cases ON emst_rg.case_id = emst_cases.id';

        if ( array_key_exists('beg_date', $AParams) )
           $vFilter[] = $ADB->CondGE('date', $AParams['beg_date']);
        if ( array_key_exists('end_date', $AParams) )
           $vFilter[] = $ADB->CondLT('date', DateAddDay($AParams['end_date']));

        $vFilter = implode(' AND ', $vFilter);
        $vOrder = 'date, case_id';
        return array($vTable, $vFilter, $vOrder);
    }
*/


    class TSearchForm extends HTML_QuickFormEx
    {
        function TSearchForm()
        {
            $this->HTML_QuickForm('frmSearch', 'post', $_SERVER['REQUEST_URI']);
            $this->addElement('header',   'Header',          'Период');

            $this->addElement('dateex',   'beg_date',  'Начальная дата',    array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>false));
            $this->addElement('dateex',   'end_date',  'Конечная дата',     array('language' => 'ru', 'format'=>'dMY', 'minYear'=>gMinYear, 'maxYear'=>gMaxYear, 'addEmptyOption'=>false));
            $this->addElement('submit',   'Submit',    'Подготовить');

            $this->applyFilter('_ALL_', 'trim');
            $this->setDefaults($_GET);
        }
    }


    class TData extends TBaseView
    {
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
        Redirect( CompoundURL('stats_list.pdf', $vFilter) );
    }
    else
    {
        $vTemplate =& CreateTemplate();
        $vRenderer =& CreateRenderer($vTemplate);
        $vForm->accept($vRenderer);
        $vView =& new TData;
        $vView->form = $vRenderer->toObject();
        $vTemplate->compile('reg/stats_list.html');
        $vTemplate->outputObject($vView);
    }

?>