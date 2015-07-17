<?php

    require_once('HTML/QuickForm/date.php');


    class HTML_QuickFormEx extends HTML_QuickForm
    {
        function setMyRequiredNote()
        {
            $this->setRequiredNote('<span style="font-size:80%; color:#ff0000;">*</span><span style="font-size:80%;"> отмеченные поля должны быть заполнены</span>');
        }
    }

    class TBaseView
    {
        function GetMenu()
        {
          return $_SESSION['User.Menu'];
        }

        function GetUserName()
        {
          return $_SESSION['User.Name'];
        }

        function GetProjectName()
        {
          return 'EmSt:Травмпункт';
        }
    }


    class HTML_QuickForm_dateEx extends HTML_QuickForm_date
    {
        function HTML_QuickForm_dateEx($elementName = null, $elementLabel = null, $options = array(), $attributes = null)
        {
            $this->_options['addDays'] = 0;
	    $this->_options['maxYear'] = $options['maxYear'] = date("Y", strtotime("+1 year") );
            $this->HTML_QuickForm_date($elementName, $elementLabel, $options, $attributes);
        }

        function setValue($value)
        {
            if (empty($value))
            {
                $value = array();
            }
            elseif (is_scalar($value))
            {
                @list($vDate, $vTime) = explode(' ', $value);
                @list($vYear, $vMonth, $vDay) = explode('-', $vDate);
                @list($vHours,$vMinuts, $vSeconds) = explode(':', $vTime);
                $vYear  = @intval($vYear);
                $vMonth = @intval($vMonth);
                $vDay   = @intval($vDay);
                $vHours = @intval($vHours);
                $value = array(
                    'd' => $vDay,
                    'M' => $vMonth,
                    'm' => $vMonth,
                    'F' => $vMonth,
                    'Y' => $vYear,
                    'y' => $vYear,
                    'h' => $vHours,
                    'g' => $vHours,
                    'H' => $vHours,
                    'i' => @intval($vMinuts),
                    's' => @intval($vSeconds),
                );
            }
            parent::setValue($value);
        }

        function toHtml()
        {
	    $this->_options['maxYear'] = 2200;
            if (!defined('HTML_QUICKFORM_DATEEX_EXISTS'))
            {
               $js = <<<EOS
<script language="JavaScript" src="/scripts/AnchorPosition.js"></script>
<script language="JavaScript" src="/scripts/PopupWindow.js"></script>
<script language="JavaScript" src="/scripts/date.js"></script>
<script language="JavaScript" src="/scripts/CalendarPopup.js"></script>
<div id="dateexplaceholder" name="dateexplaceholder"   style="position:absolute;visibility:hidden;background-color:white;layer-background-color:white; z-index:1">1</DIV>
<DIV name="dateexplaceholder2" id="dateexplaceholder2" style='position:absolute; width:12pt; height:12pt; z-index:1; visibility:hidden'>1</DIV>

<IFRAME
    name="dateexplaceholder"
    id="dateexplaceholder"
    style="position:absolute; z-index:1; visibility:hidden; border-style:none; background-color:white; layer-background-color:white; overflow:visible"
    marginheight="0"
    marginwidth="0"
    frameborder="0"
    src="display.html"
    width=100%
    height=100%
>
<DIV ID="dateexplaceholder" STYLE="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></DIV>
</IFRAME>


<script language="JavaScript">
    document.writeln(getCalendarStyles());

    function PopupDateEx(AnchorName, FieldName, EnableClear, AddDays, ARetFunc)
    {
        var cal = new CalendarPopup("dateexplaceholder");
//        var cal = new CalendarPopup();
        cal.setReturnFunction(ARetFunc);
        cal.setMonthNames("Январь","Февраль","Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
        cal.setMonthAbbreviations("Янв","Фев","Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек");
        cal.setDayHeaders("Вс","Пн","Вт","Ср","Чт","Пт","Сб");
        cal.setWeekStartDay(1);
        cal.showNavigationDropdowns();
        cal.setYearSelectStartOffset(100);
        cal.showYearNavigation();
        cal.showYearNavigationInput();
        cal.setTodayText("Сегодня");
        cal.setEnableClear(EnableClear);
        cal.setClearText("Пусто");
        cal.setAddDays(AddDays);
        var y = parseInt(document.forms[0][FieldName+"[Y]"].value);
        var m = parseInt(document.forms[0][FieldName+"[M]"].value);
        var d = parseInt(document.forms[0][FieldName+"[d]"].value);
        if ( isNaN(y) || isNaN(m) || isNaN(d) )
            cal.currentDate=null;
        else
            cal.currentDate=new Date(y,m-1,d,0,0,0);

        cal.autoHide();
        cal.showCalendar(AnchorName);
        return false;
    }
</script>
EOS;
            }
            else
            {
                $js = '';
            }

            $vName = $this->_name;
            $vAnchorName = 'Anchor_'.$vName;
            $vRetFunctionName = 'OnSet_'.$vName;
            $vEnableClear =  (@$this->_options['addEmptyOption']) ? 'true' : 'false';
            $vAddDays = (@$this->_options['addDays']);
            if ( !is_int($vAddDays) )
              $vAddDays = 0;

            $vRetFunction = <<<EOS
<script language="JavaScript">
function $vRetFunctionName(y,m,d)
{
//        document.forms[0]["{$vName}[Y]"].selectedIndex=y;
//        document.forms[0]["{$vName}[M]"].selectedIndex=m-1;
//        document.forms[0]["{$vName}[d]"].selectedIndex=d-1;
        document.forms[0]["{$vName}[Y]"].value=y;
        document.forms[0]["{$vName}[M]"].value=m;
        document.forms[0]["{$vName}[d]"].value=d;
}
</script>
EOS;

            return $js .
                   $vRetFunction .
                   parent::toHtml() .
                   '<a name="'. $vAnchorName . '" id="' . $vAnchorName . '">'.
                   '<button onclick="return PopupDateEx(\''.$vAnchorName.'\', \''. $vName.'\', '.$vEnableClear.', '.$vAddDays.', \''.$vRetFunctionName.'\')" style="height:20px">'.
                   '<img    onclick="return PopupDateEx(\''.$vAnchorName.'\', \''. $vName.'\', '.$vEnableClear.', '.$vAddDays.', \''.$vRetFunctionName.'\')" src="/images/popupCalendar.gif">'.
                   '</button>';
        }

    }

    HTML_QuickForm::registerElementType('dateex', 'library/QuickFormEx.php', 'HTML_QuickForm_dateEx');
?>