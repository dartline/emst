<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################



class TData extends TBaseView
{
    function GetTable()
    {
        $vDB = GetDB();

        $vTab =& new TTable('rb_employment_categories', '*', '', 'name', 'id');
        $vTab->AddColumn('name',      'Наименование');
        $vTab->AddBoolColumn('need_ill_doc', 'Нужен б/л или справка');
        $vTab->AddRowAction('изменить', 'employment_category_edit.html?id=');
        $vTab->AddTableAction('новая запись',  'employment_category_edit.html');
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
    $vTemplate->compile('refs/employment_categories.html');
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