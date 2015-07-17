<?php

#####################################################################
#
# Травмпункт. (c) 2005,2006 Vista
#
#####################################################################



  class TData extends TBaseView
  {
      function GetTable()
      {
          $vDB = GetDB();

          $vTab =& new TTable('rb_vistit_targets', '*', '', 'name', 'id');
          $vTab->AddColumn('name',      'Наименование');
          $vTab->AddRowAction('изменить', 'vistit_target_edit.html?id=');
          $vTab->AddTableAction('новая запись',  'vistit_target_edit.html');
          $vResult = $vTab->ProduceHTML($vDB, $_GET['PageIdx']+0, 20);
          return $vResult;
      }
  }

// =======================================================================

  RegisterListParams();

  $vTemplate =& CreateTemplate();
  $vRenderer =& CreateRenderer($vTemplate);
  $vView =& new TData;
  $vTemplate->compile('refs/vistit_targets.html');
  $vTemplate->outputObject($vView);
?>