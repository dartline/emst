<?php

#####################################################################
#
# Поликлинический автоматизированный комплекс
# (c) 2005,2006 Vista
#
# Библиотека функций
#
#####################################################################

/* most popular includes: */

require_once 'library/database.php';
require_once 'library/table.php';

require_once 'HTML/Template/Flexy.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ObjectFlexy.php';

require_once 'library/QuickFormEx.php';


/* some functions */

function& CreateTemplate()
{
    $vOptions = array(
      'templateDir'   => './templates',
      'compileDir'    => './templates/compile',
      'forceCompile'  => 0,
      'debug'         => 0,
      'locale'        => 'en',
//      'compiler'      => 'Standard'
      'compiler'      => 'Flexy'
    );

    $vResult = new HTML_Template_Flexy($vOptions);
    return $vResult;
}


function& CreateRenderer(&$ATemplate)
{
    $vRenderer =& new HTML_QuickForm_Renderer_ObjectFlexy($ATemplate);
    $vRenderer->setLabelTemplate('label.html');
    $vRenderer->setHtmlTemplate('html.html');
    return $vRenderer;
}


function Redirect($AURL)
{
    ob_end_clean();
    header('Location: '.$AURL);
}


function RegisterListParams($APageName=NULL, $APageParams=NULL)
{
    if ( empty($APageName) )
    {
        $vURI = $_SERVER['REQUEST_URI'];
        $vURIRoot = $vURI;
        $vURIRoot = preg_replace('/\?.*$/', '', $vURIRoot);
        $vURIRoot = preg_replace('/\.[hH][tT][mM][lL]?$/', '', $vURIRoot);
        $vURIRoot = preg_replace('/\.[pP][hH][pP]/',       '', $vURIRoot);
    }
    else
        $vURIRoot = $APageName;

    if ( $APageParams === NULL )
        $vListParams = $_SERVER['QUERY_STRING'];
    else
        $vListParams = $APageParams;

    $_SESSION['ListParams'][$vURIRoot] = $vListParams;
}


function RedirectToList($APage, $APageName = NULL)
{
    if ( empty($APageName) )
    {
        $vURIRoot = $APage;
        if ( $vURIRoot{0} != '/' )
            $vURIRoot = '/' . $vURIRoot;

        $vURIRoot = preg_replace('/\.[hH][tT][mM][lL]?$/', '', $vURIRoot);
        $vURIRoot = preg_replace('/\.[pP][hH][pP]/',       '', $vURIRoot);
    }
    else
        $vURIRoot = $APageName;


    $vListParams = @$_SESSION['ListParams'][$vURIRoot];
    if ( empty($vListParams) )
        Redirect($APage);
    else
        Redirect($APage.'?'.$vListParams);
}


function ClientRedirect($AURL, $ATime=0)
{
    ob_end_clean();
    $msec = $ATime * 1000;
    echo ("\n<SCRIPT LANGUAGE=\"JavaScript\">\n");
    echo ("<!--\n");
    echo ("function GO() {\n");
    echo ("setTimeout('document.location = \"$AURL\"', $msec);\n");
    echo ("}\n");
    echo ("//-->\n");
    echo ("</SCRIPT>\n");
    echo ("<meta http-equiv=\"REFRESH\" content=\"$ATime;url=$AURL\">\n");
    echo ("<meta name=\"REFRESH\" content=\"$ATime;url=$AURL\">\n");
}


function DateIsEmpty($ADate)
{
    return empty($ADate) ||
           (is_string($ADate) && ( $ADate == '0000-00-00' || $ADate == '0000-00-00 00:00:00' )) ||
           (is_array($ADate) && ( count($ADate)==0 || max(array_values($ADate)) == 0 ) );
}


function DateValueToStr($AValue)
{
    if ( is_array($AValue) )
        return sprintf('%04.4d-%02.2d-%02.2d', @$AValue['Y'], @$AValue['M'], @$AValue['d']);
    else
        return $AValue;
}


function DateTimeValueToStr($AValue)
{
    if ( is_array($AValue) )
        return sprintf('%04.4d-%02.2d-%02.2d %02.2d:%02.2d:%02.2d',
                        @$AValue['Y'], @$AValue['M'], @$AValue['d'],
                        @$AValue['H'], @$AValue['i'], @$AValue['s']);
    else
        return $AValue;
}

/* ====================================================== */

function CopyRecordStrValue(&$vRecord, &$vValues, $AName)
{
    $vRecord[$AName] = @trim($vValues[$AName]);
}


function CopyRecordStrValues(&$vRecord, &$vValues, $ANamesList)
{
    if ( is_array($ANamesList) )
    {
        foreach( $ANamesList as $vName )
          $vRecord[$vName] = @trim($vValues[$vName]);
    }
    else
    {
        $vName = $ANamesList;
        $vRecord[$vName] = @trim($vValues[$vName]);
    }
}


function CopyRecordBoolValue(&$vRecord, &$vValues, $AName)
{
    $vRecord[$AName] = @($vValues[$AName]==1?1:0);
}


function CopyRecordSelValue(&$vRecord, &$vValues, $AName)
{
/*
    if ( array_key_exists($AName, $vValues) )
        $vVal = implode(',', $vValues[$AName]);
    else
        $vVal = '';
    $vRecord[$AName] = $vVal;
*/
    $vRecord[$AName] = @implode(',', $vValues[$AName]);
}


function CopyRecordDateValue(&$vRecord, &$vValues, $AName)
{
    $vVal = @$vValues[$AName];
    if ( empty($vVal) )
      $vRecord[$AName] = '0000-00-00';
    else
      $vRecord[$AName] = DateValueToStr($vVal);
}


function CopyRecordDateTimeValue(&$vRecord, &$vValues, $AName)
{
    $vVal = @$vValues[$AName];
    if ( empty($vVal) )
      $vRecord[$AName] = '0000-00-00 00:00:00';
    else
      $vRecord[$AName] = DateTimeValueToStr($vVal);
}


function CopyRecordFileValue(&$ADB, &$vRecord, &$vValues, $AName)
{
    $vVal = $vValues[$AName];
    if ( is_array($vVal) && $vVal['error'] == UPLOAD_ERR_OK )
    {
        $vFileName = $vVal['tmp_name'];
        $vFile     = fopen($vFileName, "r");
        $vContent  = fread($vFile, filesize($vFileName));
        fclose($vFile);
        unlink($vFileName);

        $vNewVal = $ADB->Insert('Files',
                              array(
                                     'name'       => $vVal['name'],
                                     'type'       => $vVal['type'],
                                     'size'       => $vVal['size'],
                                     'content'    => $vContent
                                   )
                            );
        $vRecord[$AName] = $vNewVal;
    }
}


function CopyRecordRefValue(&$vRecord, &$vValues, $AName)
{
    $vVal = @trim($vValues[$AName]);
    if ( empty($vVal) )
        $vVal = NULL;
    $vRecord[$AName] = $vVal;
}


function CopyParam(&$AFilter, &$AValues, $AName)
{
    $vValue = @$AValues[$AName];
    if ( $vValue != '' )
        $AFilter[$AName] = $vValue;
}


function AddQueryParamEqual( &$ADB, &$AQuery, &$ARequest, $AName)
{
    $vVal = @$ARequest[$AName];
    if ( !empty($vVal) )
    {
        $AQuery[] = $ADB->CondEqual($AName, $vVal);
    }
}


function AddQueryParamEqualEx( &$ADB, $ATableName, &$AQuery, &$ARequest, $AName)
{
    $vVal = @$ARequest[$AName];
    if ( !empty($vVal) )
    {
        $AQuery[] = $ADB->CondEqual($ATableName.'.'.$AName, $vVal);
    }
}


function AddQueryParamLike( &$ADB, &$AQuery, &$ARequest, $AName)
{
    $vVal = @$ARequest[$AName];
    if ( !empty($vVal) )
    {
        $AQuery[] = $ADB->CondLike($AName, $vVal);
    }
}


function AddQueryParamLikeEx(&$ADB, $ATableName, &$AQuery, &$ARequest, $AName)
{
    $vVal = @$ARequest[$AName];
    if ( !empty($vVal) )
    {
        $AQuery[] = $ADB->CondLike($ATableName.'.'.$AName, $vVal);
    }
}


function GetMinYear()
{
    $vResult = gMinYear;
    if ( empty($vResult) )
        $vResult = 1980;
    return $vResult;
}


function CompoundURL($AUrlBase, $AParams)
{
    $vResult = '';
    if ( is_array($AParams) ) 
    {
        foreach( $AParams as $vParam=>$vValue )
        {
            $vResult .= ($vResult==''?'':'&').$vParam.'='.urlencode($vValue);
        }
    }
    if ( $vResult == '' )
        return $AUrlBase;
    else
    return $AUrlBase.'?'.$vResult;
}


function CreateLink($AUrl, $AName)
{
    return '&nbsp;<a href="' . htmlentities($AUrl) . '">' . $AName . '</a>';
}


function IsValidDate($AMySqlDate)
{
    list($vYear, $vMonth, $vDay) = explode('-', $AMySqlDate);
    return $vYear!=0 && $vMonth!=0 && $vDay!=0;
}


function Date2Readable($AMySqlDate)
{
    $gMonthsAbbr = array( 'Янв' , 'Фев' , 'Мар' , 'Апр' , 'Май' , 'Июн' ,'Июл' , 'Авг' , 'Сен' , 'Окт' , 'Ноя' , 'Дек' );

    @list($vDate, $vTime) = explode(' ', $AMySqlDate);
    if ( $vDate == '0000-00-00' || empty($vDate) )
        return '';

    list($vYear, $vMonth, $vDay) = explode('-', $vDate);
    $vResult = @($vDay . ' ' . $gMonthsAbbr[$vMonth-1] . ' ' . $vYear);
    if ( !empty($vTime) )
    {
        $vTimeList = explode(':', $vTime);
        if ( count($vTimeList) == 3 && $vTime != '00:00:00' )
            $vResult .= ' ' . $vTimeList[0].':'.$vTimeList[1];
    }
    return $vResult;
}


function Date2ReadableLong($AMySqlDate)
{
    $gMonthsAbbr = array( 'Января' , 'Февраля' , 'Марта' , 'Апреля' , 'Мая' , 'Июня' ,'Июля' , 'Августа' , 'Сентября' , 'Октября' , 'Ноября' , 'Декабря' );

    @list($vDate, $vTime) = explode(' ', $AMySqlDate);
    if ( $vDate == '0000-00-00' || empty($vDate) )
        return '';

    list($vYear, $vMonth, $vDay) = explode('-', $vDate);
    $vResult = @($vDay . ' ' . $gMonthsAbbr[$vMonth-1] . ' ' . $vYear);
    if ( !empty($vTime) )
    {
        $vTimeList = explode(':', $vTime);
        if ( count($vTimeList) == 3 && $vTime != '00:00:00' )
            $vResult .= ' ' . $vTimeList[0].':'.$vTimeList[1];
    }
    return $vResult;
}


function DateAddDay($A)
{
    $vResult = date('Y-m-d', strtotime($A)+24*60*60);
    return $vResult;
}


function CalcAge($ABornDate, $AToday = null)
{
    $vBornTmp = explode(' ',$ABornDate);
    if ( $AToday === null )
    {
        $AToday = date('Y-m-d');
    }

    $vTodayTmp = explode(' ',$AToday);

    if ( !empty($vBornTmp[0]) )
    {
        list($vBYear, $vBMonth, $vBDay) = explode('-', @$vBornTmp[0]);
        list($vTYear, $vTMonth, $vTDay) = explode('-', @$vTodayTmp[0]);

        $vAge = $vTYear - $vBYear;
        if ( ($vBMonth > $vTMonth) ||
            ($vBMonth == $vTMonth) && ($vBDay > $vTDay) )
            $vAge--;

        return $vAge;
    }
    else
        return '';
}


function DateDiff($A, $B)
{
    $vResult = floor((strtotime($A)-strtotime($B))/(24*60*60));
    return $vResult;
}


function ExtractWord($AStr, $ASep, $ANum)
{
    $vList = explode($ASep, $AStr);
    return @$vList[$ANum];
}

?>