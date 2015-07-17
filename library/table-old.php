<?php

#  define('MSG_TABLE_EMPTY',     'Table is empty');
#  define('MSG_SELECTION_EMPTY', 'Table is empty or no there are no suitable records');
#  define('ACTION_COL_NAME',     'Action');

  define('MSG_TABLE_EMPTY',     'РўР°Р±Р»РёС†Р° РїСѓСЃС‚Р°');
  define('MSG_SELECTION_EMPTY', 'РўР°Р±Р»РёС†Р° РїСѓСЃС‚Р° РёР»Рё РЅРµС‚ РїРѕРґС…РѕРґСЏС‰РёС… Р·Р°РїРёСЃРµР№');
  define('ACTION_COL_NAME',     'Р”РµР№СЃС‚РІРёРµ');



  function tcfBoolean($AVal)
  {
    if ( $AVal )
      return '<img src="/images/checkbox_on_16.gif" width="13" height="13">';
    else
      return '<img src="/images/checkbox_off_16.gif" width="13" height="13">';
  }


  function tcfDate($AVal)
  {
    return Date2Readable($AVal);
  }


  function tcfText($AVal)
  {
    return nl2br(htmlspecialchars($AVal, ENT_QUOTES));
  }


  function tcfLimText($AVal)
  {
    if ( strlen($AVal)>100 )
      $AVal = substr($AVal,0,97).'...';
    return nl2br(htmlspecialchars($AVal, ENT_QUOTES));
  }


  class TTable
  {
    var $_SQLTable     = '';
    var $_SQLCols      = '*';
    var $_SQLCountCols = '*';
    var $_SQLFilter    = '';
    var $_SQLOrder     = '';
    var $_SQLIDCol     = 'id';

    var $_Columns   = array();
    var $_Titles    = array();
    var $_Fmts      = array();

    var $_RowActionNames = array();
    var $_RowActionLinks = array();

    var $_TableActionNames = array();
    var $_TableActionLinks = array();


    function TTable($ATable='', $ACols='*', $AFilter='', $AOrder='', $ASQLIDCol='id', $ASQLCountCols='*')
    {
      $this->setTable($ATable);
      $this->setCols($ACols);
      $this->setFilter($AFilter);
      $this->setOrder($AOrder);
      $this->setSQLIDCol($ASQLIDCol);
      $this->setSQLCountCols($ASQLCountCols);
    }


    function setTable($ATable)
    {
      $this->_SQLTable = $ATable;
    }


    function setCols($ACols)
    {
      $this->_SQLCols = $ACols;
    }


    function setFilter($AFilter)
    {
      $this->_SQLFilter = $AFilter;
    }


    function setOrder($AOrder)
    {
      $this->_SQLOrder = $AOrder;
    }


    function setSQLIDCol($ASQLIDCol)
    {
      $this->_SQLIDCol = $ASQLIDCol;
    }


    function setSQLCountCols($ASQLCountCols)
    {
      $this->_SQLCountCols = $ASQLCountCols;
    }


    function addColumn($ASQLColName, $ATitle, $AFmt='')
    {
      $this->_Columns[] = $ASQLColName;
      $this->_Titles[]  = htmlspecialchars($ATitle, ENT_QUOTES);
      if ( is_array( $AFmt ) )
        $vFmt =& $AFmt;
      else if ( !empty( $AFmt ) )
        $vFmt = array('fmt'=>$AFmt);
      else
        $vFmt = array();
      $this->_Fmts[] = $vFmt;
    }


    function addBoolColumn($ASQLColName, $ATitle)
    {
      $this->addColumn( $ASQLColName, $ATitle, array('align'=>'center', 'fmt'=>'tcfBoolean') );
    }


    function addIntColumn($ASQLColName, $ATitle)
    {
      $this->addColumn( $ASQLColName, $ATitle, array('align'=>'right'));
    }


    function addDateColumn($ASQLColName, $ATitle)
    {
      $this->addColumn( $ASQLColName, $ATitle, array('align'=>'left', 'fmt'=>'tcfDate'));
    }


    function addTextColumn($ASQLColName, $ATitle)
    {
      $this->addColumn( $ASQLColName, $ATitle, array('align'=>'left', 'fmt'=>'tcfText'));
    }

    function addLimTextColumn($ASQLColName, $ATitle)
    {
      $this->addColumn( $ASQLColName, $ATitle, array('align'=>'left', 'fmt'=>'tcfLimText'));
    }



    function addRowAction($AName, $ALink)
    {
      $this->_RowActionNames[] = htmlspecialchars($AName, ENT_QUOTES);
      $this->_RowActionLinks[] = $ALink;
    }


    function addTableAction($AName, $ALink)
    {
      $this->_TableActionNames[] = htmlspecialchars($AName, ENT_QUOTES);
      $this->_TableActionLinks[] = $ALink;
    }


    function ProduceHTML(&$ADB, $APageIdx=0, $APerPage=0)
    {
      if ( $APerPage > 0 )
      {
//        $vTotal = $ADB->CountRows($this->_SQLTable, 'id',  $this->_SQLFilter);
        $vTotal = $ADB->CountRows($this->_SQLTable, $this->_SQLCountCols, $this->_SQLFilter);
        $vLimit = $this->GetLimit($vTotal, $APerPage, $APageIdx);
      }
      else
      {
        $vLimit = '';
      }

      $vRows = $ADB->Select($this->_SQLTable, $this->_SQLCols, $this->_SQLFilter, $this->_SQLOrder, $vLimit);

      if ( $vRows->Count() > 0 )
      {
        $vColsCount       = count($this->_Columns);
        $vRowActionsCount = count($this->_RowActionNames);


        $vResult  = '<p><table><thead><tr>';
        for($i=0; $i<$vColsCount; $i++)
        {
          $vResult .= '<th class="tabheader" valign="top">' . $this->_Titles[$i] . '</th>';
        }


        if ( $vRowActionsCount>0 )
        {
          $vResult .= '<th class="tabheader" valign="top">'.ACTION_COL_NAME.'</th>';
        }
        $vResult .= '</tr></thead>';


        $vRowFlip = FALSE;
        while( $vRow = $vRows->Fetch() )
        {
          $vId = $vRow[$this->_SQLIDCol];
          $vResult  .= '<tr>';
          for( $i=0; $i<$vColsCount; $i++ )
          {

            $vVal = $vRow[$this->_Columns[$i]];
            $vFmt = $this->_Fmts[$i];
            $vFmtFunc  = @$vFmt['fmt'];
            $vFmtAlign = @$vFmt['align'];
            $vOldVal = $vVal;
            if ( $vFmtFunc!='' )
              $vVal = $vFmtFunc($vVal, &$vRow, &$ADB);
            else
              $vVal = htmlspecialchars($vVal, ENT_QUOTES);

            $vResult  .= '<td class=tabrow'. ( $vRowFlip ? '1' : '0' )
                      . ( empty($vFmtAlign)? '': (' align=' . $vFmtAlign) )
                      . ' valign=top>' . $vVal . '</td>';
          }

          if ( $vRowActionsCount>0 )
          {
            $vResult .= '<td class="tabrow'. ( $vRowFlip ? '1' : '0' ) . '" valign="top">' ;
            for( $i=0; $i<$vRowActionsCount; $i++ )
            {
              $vURL = $this->_RowActionLinks[$i] . $vId;
              $vResult .= '&nbsp;<a href="' . $vURL .'">'. $this->_RowActionNames[$i] .'</a>&nbsp;';
            }
            $vResult .= '</td>';
          }

          $vResult .= '</tr>';
          $vRowFlip = !$vRowFlip;
        }
        $vResult .= '</table></p>';
      }
      else
      {
        if ( $this->_SQLFilter == '' )
          $vResult = '<p>'.MSG_TABLE_EMPTY.'</p>';
        else
          $vResult = '<p>'.MSG_SELECTION_EMPTY.'</p>';

      }

      if ( $APerPage > 0 )
      {
        $vBase = explode('?', $_SERVER['REQUEST_URI']);
        $vBase = $vBase[0];
        $vResult .= '<p>'. $this->DrawPagesBar($vBase, $vTotal, $APerPage, $APageIdx) .'</p>';
      }


      for($i=0; $i<count($this->_TableActionNames); $i++ )
      {
        $vResult .= '&nbsp;<a href="'.$this->_TableActionLinks[$i].'">'.$this->_TableActionNames[$i].'</a>&nbsp;';
      }

      return $vResult;
    }


    function BoundPage($ATotal, $APerPage, $APageIdx)
    {
        if ( $APageIdx === 'last' )
            $APageIdx=2147483647; // maxint
        if ( $ATotal>0 && $APerPage>0 && $APageIdx>0 )
        {
            $vMaxPageIdx = intval( ($ATotal-1)/$APerPage );
            $vResult = $APageIdx>$vMaxPageIdx ? $vMaxPageIdx : $APageIdx;
        }
        else
            $vResult = 0;

        return $vResult;
    }


    function GetLimit($ATotal, $APerPage, &$APageIdx)
    {
        if ( $APerPage>0 && $ATotal > $APerPage )
        {
            $APageIdx = $this->BoundPage($ATotal, $APerPage, $APageIdx);
            if ( $APageIdx>0 )
            {
                return ($APageIdx*$APerPage) . ', ' . $APerPage;
            }
            else
            {
                return $APerPage;
            }
        }
        else
        {
            return '';
        }
    }


    function DrawPagesBar($AUrl, $ATotal, $APerPage, $APageIdx)
    {
        if ( $APerPage>0 && $ATotal > $APerPage )
        {
            $vParams    = $_GET;
            $vPageIdx   = $this->BoundPage($ATotal, $APerPage, $APageIdx);
            $vMaxPageIdx= intval( ($ATotal-1)/$APerPage );
            $vResult = '';
            if ( $vPageIdx != 0 )
            {
                $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>0))), '[1]' );
                $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>($APageIdx-1)))), 'prev');
            }

      /*
            for( $i=0; $i<=$vMaxPageIdx; $i++ )
            {
              if ( $i == $vPageIdx )
                $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>$i))), '<b>' . ($i+1) . '</b>');
              else
                $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>$i))), ($i+1));
            }
      */

            $vMinPage = max($vPageIdx-10,0);
            $vMaxPage = min($vPageIdx+10,$vMaxPageIdx);

      /*
            $vMinPage = max($vPageIdx - ($vPageIdx%10),0);
            $vMaxPage = min($vMinPage+10,$vMaxPageIdx);
      */
            for( $i=$vMinPage; $i<=$vMaxPage; $i++ )
            {
                if ( $i == $vPageIdx )
                    $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>$i))), '<b>' . ($i+1) . '</b>');
                else if ( ($i==$vMinPage && ($vMinPage>0) ||
                        ($i==$vMaxPage && $vMaxPage<$vMaxPageIdx)) )
                    $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>$i))), '...');
                else
                    $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>$i))), ($i+1));
            }

            if ( $vPageIdx < $vMaxPageIdx )
            {
                $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>($APageIdx+1)))),  'next');
                $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>$vMaxPageIdx))), '['.($vMaxPageIdx+1).']');
            }
            return $vResult;
        }
        else
              return '';
      }
  }


    function GetPageIdxOrLast()
    {
        if ( array_key_exists('PageIdx', $_GET) )
            return $_GET['PageIdx']+0;
        else
            return 'last';
    }



?>