<?php
#####################################################################
#
# Поликлинический автоматизированный комплекс
# (c) 2005,2006 Vista
#
# Библиотека для вывода таблиц
#
#####################################################################

#  define('MSG_TABLE_EMPTY',     'Table is empty');
#  define('MSG_SELECTION_EMPTY', 'Table is empty or no there are no suitable records');
#  define('ACTION_COL_NAME',     'Action');

  define('MSG_TABLE_EMPTY',     'Таблица пуста');
  define('MSG_SELECTION_EMPTY', 'Таблица пуста или нет подходящих записей');
  define('ACTION_COL_NAME',     'Действие');



  function tcfBoolean($AVal)
  {
    if ( $AVal )
      return '<img src="../images/checkbox_on_16.gif" width="13" height="13">';
    else
      return '<img src="../images/checkbox_off_16.gif" width="13" height="13">';
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


    class TAction
    {
        private $_URLTemplate = '';

        function TAction($AURLTemplate)
        {
            $this->_URLTemplate = $AURLTemplate;
        }

        function RecordToURL($AID)
        {
            return $this->_URLTemplate.$AID;
        }   
    }

    class TTextAction extends TAction
    {
        private $_Label = '';   

        function TTextAction($AURLTemplate, $ALabel)
        {
            $this->TAction($AURLTemplate);
            $this->_Label = htmlspecialchars($ALabel, ENT_QUOTES);
        }

        function RenderToHTML($AID)
        {
            return '&nbsp;<a href="' . htmlspecialchars($this->RecordToURL($AID), ENT_QUOTES) . '">' . $this->_Label . '</a>&nbsp;';
        }   
    }

    class TImageAction extends TAction
    {
        private $_ImageURL = '';    
        private $_Label    = '';
        private $_Width    = 0;
        private $_Heght    = 0;
        private $_PreURL   = null;
        private $_PostURL  = null;


        function TImageAction($AURLTemplate, $ALabel, $AImageURL, $AWidth=0, $AHeight=0)
        {
            $this->TAction($AURLTemplate);
            $this->_ImageURL = htmlspecialchars($AImageURL, ENT_QUOTES);
            $this->_Label    = htmlspecialchars($ALabel, ENT_QUOTES);
            if ( !empty($AWidth) )
                $this->_Width = htmlspecialchars($AWidth, ENT_QUOTES);
            else
                $this->_Width = 0;

            if ( !empty($AHeight) )
                $this->_Height = htmlspecialchars($AHeight, ENT_QUOTES);
            else
                $this->_Height = 0;
        }


        function RenderToHTML($AID)
        {
            if ( $this->_PreURL === null )
            {
                $this->_PreURL  = '&nbsp;<a href="';
                $this->_PostURL = '"><img src="'. $this->_ImageURL 
                                 .'" alt="'. $this->_Label. '" border="0"';
                if ( !empty($this->_Width) )
                    $this->_PostURL .= ' width="'.$this->_Width.'"';
                if ( !empty($this->_Height) )
                    $this->_PostURL .= ' height="'.$this->_Height.'"';
                $this->_PostURL .= '></a>&nbsp;';
            }
            return $this->_PreURL.htmlspecialchars($this->RecordToURL($AID), ENT_QUOTES).$this->_PostURL;
        }   
    }



  class TTable
  {
    private $_SQLTable     = '';
    private $_SQLCols      = '*';
    private $_SQLCountCols = '*';
    private $_SQLFilter    = '';
    private $_SQLOrder     = '';
    private $_SQLIDCol     = 'id';

    private $_Columns   = array();
    private $_Titles    = array();
    private $_Fmts      = array();

//    var $_RowActionNames = array();
//    var $_RowActionLinks = array();
    private $_RowActions =  array();

    private $_TableActionNames = array();
    private $_TableActionLinks = array();


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
      $this->_Titles[]  = nl2br(htmlspecialchars($ATitle, ENT_QUOTES));
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


    function addRowAction($AName, $ALink, $AImageURL='', $AWidth=0, $AHeight=0)
    {
//      $this->_RowActionNames[] = htmlspecialchars($AName, ENT_QUOTES);
//      $this->_RowActionLinks[] = $ALink;
      if ( empty($AImageURL) )
        $this->_RowActions[] = new TTextAction($ALink, $AName);
      else
        $this->_RowActions[] = new TImageAction($ALink, $AName, $AImageURL, $AWidth, $AHeight);
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
        $vRowActionsCount = count($this->_RowActions);


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
              $vVal = $vFmtFunc($vVal, $vRow, $ADB);
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
//              $vURL = $this->_RowActionLinks[$i] . $vId;
//              $vResult .= '&nbsp;<a href="' . $vURL .'">'. $this->_RowActionNames[$i] .'</a>&nbsp;';
              $vResult .= $this->_RowActions[$i]->RenderToHTML($vId);
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
                $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>($APageIdx-1)))), 'пред.');
            }

            $vMinPage = max($vPageIdx-10,0);
            $vMaxPage = min($vPageIdx+10,$vMaxPageIdx);

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
                $vResult .= CreateLink(CompoundURL($AUrl, array_merge($vParams, array('PageIdx'=>($APageIdx+1)))),  'след.');
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