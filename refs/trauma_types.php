<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################



/*
  class TUserSearchForm extends HTML_QuickForm
  {
    function TUserSearchForm()
    {
      $this->HTML_QuickForm('frmSearchProperty', 'post', $_SERVER[REQUEST_URI]);
      $this->addElement('header',   'Header',          'Filter');

      $this->addElement('text',     'PropID',          'Property ID',      array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'StreetNumber',    'Street Number',    array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'StreetName',      'Street Name',      array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'City',            'City',             array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'State',           'State',            array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'Zip',             'ZIP',              array('style'=>'WIDTH: 180px'));
      $this->addElement('select',   'PropType',        'Property Type',    array(''=>'', 'Apt'=>'Apt', 'Hotel/Motel'=>'Hotel/Motel', 'Convalescent Home'=>'Convalescent Home', 'Private Res.'=>'Private Res.', 'Condo'=>'Condo', 'Mobile Home Park'=>'Mobile Home Park', 'Marine'=>'Marine', 'Other'=>'Other'), array('style'=>'WIDTH: 180px'));
      $this->addElement('select',   'IsPrinted',       'Printed',          array(''=>'', 1=>'Not Printed', 2=>'Printed'), array('style'=>'WIDTH: 180px'));
      $this->addElement('select',   'SurveyCompleted', 'Survey Completed', array(''=>'', 1=>'Not Completed', 2=>'Completed'), array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'OwnerID',         'Owner ID',         array('style'=>'WIDTH: 180px'));
      $this->addElement('text',     'MgmtID',          'Management ID',    array('style'=>'WIDTH: 180px'));
      $this->addElement('select',   'Order',        'Sort By',
                        array_values( GetPropertiesSortOrder() ),
                        array('style'=>'WIDTH: 180px'));
      $this->addElement('submit',   'Submit',       'Set Filter');

      $this->applyFilter('_ALL_', 'trim');
      $this->setDefaults($_GET);
    }
  }
*/

    class TData extends TBaseView
    {
        function GetTable()
        {
            $vDB = GetDB();

            $vTab =& new TTable('rb_trauma_types', '*', '', 'name', 'id');
            $vTab->AddColumn('name',      'Наименование');
            $vTab->AddRowAction('изменить', 'trauma_type_edit.html?id=');
            $vTab->AddTableAction('новая запись',  'trauma_type_edit.html');
            $vResult = $vTab->ProduceHTML($vDB, $_GET['PageIdx']+0, 20);
            return $vResult;
        }
    }

// =======================================================================

    RegisterListParams();

//  $vForm =& new TUserSearchForm();

    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
//  $vForm->accept($vRenderer);
    $vView =& new TData;
//  $vView->form = $vRenderer->toObject();
    $vTemplate->compile('refs/trauma_types.html');
    $vTemplate->outputObject($vView);
/*
    if ( $vForm->validate() )
    {
        $vValues  = $vForm->getSubmitValues();
        $vFilter = array();
        CopyParam($vFilter, $vValues, 'PropID');
        CopyParam($vFilter, $vValues, 'StreetNumber');
        CopyParam($vFilter, $vValues, 'StreetName');
        CopyParam($vFilter, $vValues, 'City');
        CopyParam($vFilter, $vValues, 'State');
        CopyParam($vFilter, $vValues, 'Zip');
        CopyParam($vFilter, $vValues, 'PropType');
        CopyParam($vFilter, $vValues, 'IsPrinted');
        CopyParam($vFilter, $vValues, 'SurveyCompleted');
        CopyParam($vFilter, $vValues, 'OwnerID');
        CopyParam($vFilter, $vValues, 'MgmtID');
        CopyParam($vFilter, $vValues, 'Order');
        Redirect( CompoundURL('de_properties.html', $vFilter) );
    }
*/
?>