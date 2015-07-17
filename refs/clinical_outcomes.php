<?php

#####################################################################
#
# Травмпункт. (c) 2005, 2006 Vista
#
#####################################################################

require_once 'library/users.php';



class TData extends TBaseView
{
    function GetTable()
    {
        $vDB = GetDB();

        $vTab =& new TTable('rb_clinical_outcomes', '*', '', 'name', 'id');
        $vTab->AddColumn('name',     'Наименование');
        $vTab->AddBoolColumn('can_skip_ill_doc_on_disability', "В случае указания\nнетрудоспособности\nможет быть опущен б/л");
        $vTab->AddBoolColumn('can_use_ill_doc_in_ability',     "Б/л может быть\nуказан при сохранении\nтрудоспособности");
        $vTab->AddBoolColumn('req_epicrisis',                  "Требуется указание\nэпикриза при печати\nистории болезни");

        $vTab->AddRowAction('изменить', 'clinical_outcome_edit.html?id=');
        $vTab->AddTableAction('новая запись',  'clinical_outcome_edit.html');
        $vResult = $vTab->ProduceHTML($vDB, $_GET['PageIdx']+0, 20);
        return $vResult;
    }
}

// =======================================================================

    RegisterListParams();

    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vView =& new TData;
    $vTemplate->compile('refs/clinical_outcomes.html');
    $vTemplate->outputObject($vView);
?>