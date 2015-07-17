<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################

    require_once 'library/users.php';


    class TData extends TBaseView
    {
        function GetTable()
        {
            $vDB = GetDB();

            $vTab =& new TTable('rb_directions', '*', '', 'name', 'id');
            $vTab->AddColumn('name',      'Наименование');
            $vTab->AddRowAction('изменить', 'direction_edit.html?id=');
            $vTab->AddTableAction('новая запись',  'direction_edit.html');
            $vResult = $vTab->ProduceHTML($vDB, $_GET['PageIdx']+0, 20);
            return $vResult;
        }
    }

// =======================================================================

    RegisterListParams();
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vView =& new TData;
    $vTemplate->compile('refs/directions.html');
    $vTemplate->outputObject($vView);
?>