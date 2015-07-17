<?php

#####################################################################
#
# Травмпункт. (c) 2005 Vista
#
#####################################################################

require_once('library/cases_table.php');


function CheckFileUploadCode($ACode)
{
    switch( $ACode )
    {
    case UPLOAD_ERR_OK:         // There is no error, the file uploaded with success. 
        break;
    case UPLOAD_ERR_INI_SIZE:   // The uploaded file exceeds the upload_max_filesize directive in php.ini. 
         throw new Exception('Файл слишком большой -- необходимо увеличить upload_max_filesize в php.ini');
        
    case UPLOAD_ERR_FORM_SIZE:  // The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form. 
        throw new Exception('Файл слишком большой -- необходимо увеличить MAX_FILE_SIZE в описании формы');
        
    case UPLOAD_ERR_PARTIAL:    // The uploaded file was only partially uploaded. 
        throw new Exception('Ошибка загрузки файла -- файл загружен частично');

    case UPLOAD_ERR_NO_FILE:    //   No file was uploaded. 
        throw new Exception('Ошибка загрузки файла -- файл не загружен');

    default:
        throw new Exception('Неизвестная ошибка загрузки файла -- '.$ACode);
    }
}


function GetFieldList($AHandle)
{
    $vHeader = dbase_get_header_info($AHandle);
    $vResult = array();
    if ( is_array($vHeader) )
    {
        foreach($vHeader as $vFD)
        {
            $vResult[] = strtoupper($vFD['name']);
        }
    }
    return $vResult;
}


function FindFieldIdx(&$AList, $AName, $AThrow = true)
{
    $vResult = array_search(strtoupper($AName), $AList, true);
    if ( $vResult === false )
    {
        if ( $AThrow )
            throw new Exception('В файле нет поля '.$AName);
        else
            $vResult = -1; // false is not suitable, becouse a[false] == a[0] :(
    }
    return $vResult;
}


function DBF2Date($ADBFDate)
{
    return substr($ADBFDate,0,4).'-'.substr($ADBFDate,4,2).'-'.substr($ADBFDate,6,2);
}


function ProcessDBFRecord($ASurgeryID, $ACaseID, $ADate, $ASend, $AError)
{
    $vDB = GetDB();
    if ( empty($ASurgeryID) )
        $vSurgery = $vDB->Get('emst_surgeries', '*', $vDB->CondEqual('case_id', $ACaseID).' AND DATE(`date`)='.$vDB->Decorate($ADate));
    else
        $vSurgery = $vDB->Get('emst_surgeries', '*', $vDB->CondEqual('id', $ASurgeryID));
    if ( is_array($vSurgery) )
    {
        if ($ASend === '1' ||
           ($ASend === '0' && $AError == 'Повторная запись' ))
        {
            $vUpdate = array('eisoms_status'=>'2', 'eisoms_message'=>'');
            $vDB->UpdateById('emst_surgeries', $vSurgery['id'], $vUpdate);
        }
        else if ( $ASend === '0' && $AError != '' )
        {
            $vUpdate = array('eisoms_status'=>'1', 'eisoms_message'=>$AError);
            $vDB->UpdateById('emst_surgeries', $vSurgery['id'], $vUpdate);
        }
        else
        {
            Trace("Can't update ProcessDBFRecord($ASurgeryID, $ACaseID, $ADate, $ASend, $AError)");
        }
    }
    else
    {
            Trace("Can't find ProcessDBFRecord($ASurgeryID, $ACaseID, $ADate, $ASend, $AError)");
    }
}


function ProcessDBF($AName)
{
    $vResult = 0;
    $vHandle = dbase_open($AName, 0);
    if  ( $vHandle === FALSE )
        throw new Exception('Невозможно открыть файл базы данных');

    try
    {
        $vFieldList = GetFieldList($vHandle);
        $vSurgeryIDIdx = FindFieldIdx($vFieldList, 'MYSURGERYID', false);
        $vCaseIDIdx    = FindFieldIdx($vFieldList, 'MYCASEID');
        $vDateIdx      = FindFieldIdx($vFieldList, 'DATEIN');
        $vSendIdx      = FindFieldIdx($vFieldList, 'SEND');
        $vErrorIdx     = FindFieldIdx($vFieldList, 'ERROR');

        $vCnt = dbase_numrecords($vHandle);
        for($i=1;$i<=$vCnt;$i++)
        {
            $vRecord = dbase_get_record($vHandle, $i);
            ProcessDBFRecord(trim(@$vRecord[$vSurgeryIDIdx]),
                             trim(@$vRecord[$vCaseIDIdx]),
                             DBF2Date(trim(@$vRecord[$vDateIdx])),
                             trim(@$vRecord[$vSendIdx]),
                             iconv('CP866','UTF-8',trim(@$vRecord[$vErrorIdx])));
            
            $vResult++;
        }        
    }
    catch(Exception $e)
    {
        dbase_close( $vHandle );
        throw $e;
    }

    dbase_close( $vHandle );
    return $vResult;
}

                 
class TUploadForm extends HTML_QuickFormEx
{
    function TUploadForm()
    {
        $this->HTML_QuickForm('frmUpload', 'post', $_SERVER['REQUEST_URI']);
        $this->addElement('header',   'Header',          'Файл загрузки');

        $this->addElement('file',     'dbf_file',  'Файл');
        $this->addElement('static',   'report',    '');
        $this->addElement('submit',   'Submit',    'Загрузить');

        $this->applyFilter('_ALL_', 'trim');
        $this->setDefaults($_GET);
    }

    function Work()
    {
        $vNumRecords = 0;
        $vValues  = $this->getSubmitValues(true);
        if ( empty($vValues['dbf_file']) )
            return false;
        $vTmpName = $vValues['dbf_file']['tmp_name'];
        try 
        { 
            CheckFileUploadCode($vValues['dbf_file']['error']);
            $vNumRecords = ProcessDBF($vTmpName);
        }
        catch (Exception $e)
        {   
            $vMessage = $e->getMessage();
        }

        unlink($vTmpName);

        if ( !empty($vMessage) )
        {
            $this->setElementError('dbf_file',$vValues['dbf_file']['name'].': '.$vMessage);
            return false;
        }
        else
        {
            $this->getElement('report')->setValue('<hr><big><b>'.$vValues['dbf_file']['name'].': обработано записей '.$vNumRecords.'</b></big><hr>');
            return true;
        }
    }
}


class TData extends TBaseView
{
}

// =======================================================================

$vForm =& new TUploadForm();

if ( $vForm->validate() )
    $vForm->Work();

$vTemplate =& CreateTemplate();
$vRenderer =& CreateRenderer($vTemplate);
$vForm->accept($vRenderer);
$vView =& new TData;
$vView->form = $vRenderer->toObject();
$vTemplate->compile('reg/stats_dbf_import.html');
$vTemplate->outputObject($vView);

?>