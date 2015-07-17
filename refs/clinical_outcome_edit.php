<?php

#####################################################################
#
# Травмпункт. (c) 2005,2006 Vista
#
#####################################################################

require_once 'library/users.php';

class TEditor extends HTML_QuickFormEx
{
    function TEditor($AId='')
    {
        $vDB = GetDB();
        if ( !empty($AId) )
        {
            $vRecord = $vDB->GetById('rb_clinical_outcomes', $AId);
            if (  !is_array($vRecord) )
               $vRecord = array();
        }
        else
            $vRecord = array();


        $this->HTML_QuickForm('frmItem', 'post', $_SERVER['REQUEST_URI']);
        $this->setMyRequiredNote();
        $this->addElement('header',   'header',          'Исход лечения');
        $this->addElement('hidden',   'id',              '');
        $this->addElement('text',     'name',            'Наименование',   array('class'=>'edt_avg'));
        $this->addElement('checkbox', 'can_skip_ill_doc_on_disability', 'В случае указания нетрудоспособности может быть опущен б/л');
        $this->addElement('checkbox', 'can_use_ill_doc_in_ability',     'Б/л может быть указан при сохранении трудоспособности');
        $this->addElement('checkbox', 'req_epicrisis',                  'Требуется указание эпикриза при печати истории болезни');
        $this->addElement('submit',   'Submit',     'Ok');
        $this->applyFilter('_ALL_', 'trim');

        $this->addRule('name', 'Это поле обязательно для заполнения', 'required');
        $this->setDefaults($vRecord);
    }

    function Save()
    {
        if ( !$this->validate() )
            return FALSE;

        $vDB = GetDB();
        $vValues = $this->getSubmitValues(TRUE);
        $vRecord = array();

        if ( !empty($vValues['id']) )
          $vRecord['id'] = $vValues['id'];
        CopyRecordStrValue($vRecord, $vValues, 'name');
        CopyRecordBoolValue($vRecord, $vValues, 'can_skip_ill_doc_on_disability');
        CopyRecordBoolValue($vRecord, $vValues, 'can_use_ill_doc_in_ability');
        CopyRecordBoolValue($vRecord, $vValues, 'req_epicrisis');
        $vResult = $vDB->InsertOrUpdateById('rb_clinical_outcomes', $vRecord);

       return $vResult;
    }

}


$vId = array_key_exists('id', $_GET) ? $_GET['id'] : '';
$vForm = new TEditor($vId);
if ( !$vForm->Save() )
{
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TBaseView();
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('refs/clinical_outcome_edit.html');
    $vTemplate->outputObject($vView);
}
else
{
    RedirectToList('clinical_outcomes.html');
}

?>