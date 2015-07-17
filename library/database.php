<?php

require_once('config/config.php');
require_once('library/trace-dbwinole.php');


class TMyRows
{
    private $_RowsHandle;

    function __construct($ARowsHandle)
    {
        $this->_RowsHandle = $ARowsHandle;
    }


    function __destruct()
    {
        mysql_free_result($this->_RowsHandle);
        $this->_RowsHandle = NULL;
    }


    function Count()
    {
        return mysql_num_rows($this->_RowsHandle);
    }

    function Fetch()
    {
        $vResult = mysql_fetch_assoc($this->_RowsHandle);
        return $vResult;
    }
}


class TMyDB
{
    /* private */
    private $_MySQLHandle;
    private $_Trace;
    private $_TraceErr;
    private $_ErrOp;
    private $_ErrNo;
    private $_ErrMgs;

    function __construct()
    {
        $this->_MySQLHandle = mysql_connect(gDBHost, gDBUser, gDBPassword) or die('Could not connect: ' . mysql_error());

        mysql_query('SET NAMES utf8');
        mysql_select_db(gDBName) or die("Could not select database");

        $this->_Trace    = gDBDefaultTrace;
        $this->_TraceErr = gDBDefaultTraceErr;
    }

    function __destruct()
    {
      mysql_close($this->_MySQLHandle);
      $this->_MySQLHandle = NULL;
    }


    function SetTrace($ASet)
    {
        $this->_Trace = $ASet;
    }

    function SetTraceErr($ASet)
    {
        $this->_TraceErr = $ASet;
    }


    function Trace($AQuery)
    {
        if ( $this->_Trace)
        {
            Trace($AQuery);
//        print '<div class="free" >{'. htmlspecialchars($AQuery, ENT_NOQUOTES). '}</div>';
//        print '<img src="images/sqltrace.gif" width=24 height=16 title="' . htmlentities($AQuery,ENT_QUOTES) .'">';
        }
    }


    function TraceErr($AQuery)
    {
        if ( $this->_TraceErr)
        {
            Trace($AQuery);
//        print '<div class="free" >{'. htmlspecialchars($AQuery, ENT_NOQUOTES). '}</div>';
//        print '<img src="images/sqltrace.gif" width=24 height=16 title="' . htmlentities($AQuery,ENT_QUOTES) .'">';
        }
    }



    function ClearError()
    {
        $this->_ErrOp  = '';
        $this->_ErrNo  = '';
        $this->_ErrMgs = '';
    }


    function SetError($AOp)
    {
        $this->_ErrOp  = $AOp;
        $this->_ErrNo  = mysql_errno();
        $this->_ErrMgs = mysql_error();
        $this->TraceErr('Error on '. $this->ErrorMsg());
    }


    function IsError()
    {
        return !empty($this->_ErrNo);
    }


    function ErrorMsg()
    {
        return
            empty($this->_ErrNo)
            ? ""
            : ( $this->_ErrOp . ': ' .$this->_ErrNo.' '.$this->_ErrMgs);
    }

/* ===== prepare colnames && values ==================================== */

    function Decorate($AVal)
    {
        return '\'' . mysql_escape_string($AVal) . '\'';
    }


    function ConvertToDateTime($AUnixTimeStamp)
    {
        return date('Y-m-d H:i:s', $AUnixTimeStamp);
    }

    function ConvertToDate($AUnixTimeStamp)
    {
        return date('Y-m-d', $AUnixTimeStamp);
    }

    function CondEqual($ACol, $AVal)
    {
        return '`' . $ACol . '`=' . $this->Decorate($AVal);
    }

    function CondLE($ACol, $AVal)
    {
        return '`' . $ACol . '`<=' . $this->Decorate($AVal);
    }

    function CondLT($ACol, $AVal)
    {
        return '`' . $ACol . '`<' . $this->Decorate($AVal);
    }

    function CondGE($ACol, $AVal)
    {
        return '`' . $ACol . '`>=' . $this->Decorate($AVal);
    }

    function CondGT($ACol, $AVal)
    {
        return '`' . $ACol . '`>' . $this->Decorate($AVal);
    }

    function CondNotEqual($ACol, $AVal)
    {
        return '`' . $ACol . '`!=' . $this->Decorate($AVal);
    }


    function CondIsNull($ACol)
    {
        return '`' . $ACol . '` IS NULL';
    }


    function CondIsNotNull($ACol)
    {
        return '`' . $ACol . '` IS NOT NULL';
    }


    function CondLike($ACol, $AVal)
    {
        return '`' . $ACol . '` LIKE ' . $this->Decorate($AVal . '%');
    }

    function CondLikeBase($ACol, $AVal)
    {
        return '`' . $ACol . '` LIKE ' . $this->Decorate($AVal);
    }

    function CondInSet($ACol, $AVal)
    {
        return 'FIND_IN_SET('. $this->Decorate($AVal) . ', `'. $ACol .'`)';
    }

    function CondIn($ACol, $AVals)
    {
        $vVals = array();
        if ( is_array($AVals) )
            foreach( $AVals as $vVal )
            {
               $vVals[] = $this->Decorate($vVal);
            }
        else
            $vVals[] = $this->Decorate($AVals);
            
        return '`' . $ACol . '` IN (' . implode(', ', $vVals) .')';
    }



    function CondAnd()
    {
        $vNumArgs = func_num_args();
        $vResult  = '';
        for( $i=0; $i<$vNumArgs; $i++)
        {
            $vArg = func_get_arg($i);
            if ( $vArg != '' )
            {
               $vResult .= ($vResult===''?'':' AND ')
                        .  '('. $vArg . ')';
            }
        }
        return $vResult;
    }


    function CondOr()
    {
        $vNumArgs = func_num_args();
        $vResult  = '';
        for( $i=0; $i<$vNumArgs; $i++)
        {
            $vArg = func_get_arg($i);
            if ( $vArg != '' )
            {
               $vResult .= ($vResult===''?'':' OR ')
                        .  '('. $vArg . ')';
            }
        }
        return $vResult;
    }



    function ConvAssocToAssigns(&$AList)
    {
        $vResult = '';
        foreach( $AList as $vCol => $vVal )
        {
            if ( substr($vCol, strlen($vCol)-2) == '()' )
            {
                if ( $vVal === NULL )
                    $vVal = 'NULL';
                $vCol = substr($vCol, 0, strlen($vCol)-2);
            }
            else
            {
                if ( $vVal === NULL )
                    $vVal = 'NULL';
                else
                    $vVal = '\'' . mysql_escape_string($vVal) . '\'';
            }

            $vResult .= ($vResult=='' ? '' : ', ') . '`' . $vCol .'`='. $vVal;
        }
        return $vResult;
    }

/* ===== base queries ================================================== */

    function Query($AQuery)
    {
        $this->Trace($AQuery);
        $vResult = mysql_query($AQuery, $this->_MySQLHandle);
        if ( $vResult === false )
        {
            $vOperator = trim($AQuery);
            $vSpacePos = strrpos($vOperator, ' ');
            if ( $vSpacePos !== false )
                $vOperator = substr($vOperator, 0, $vSpacePos);
            $this->SetError($vOperator);
        }
        else
        {
            $this->ClearError();
        }
        return $vResult;
    }


    function Transaction()
    {
        $this->Query('BEGIN');
    }


    function Rollback()
    {
        $this->Query('ROLLBACK');
    }


    function Commit()
    {
        $this->Query('COMMIT');
    }


    function Insert($ATable, &$ARecord)
    {
//            if ( empty($ARecord) || is_array($ARecord) && count($ARecord) == 0 )
//                $vQuery = 'INSERT INTO ' . $ATable . ';';
//            else
        $vQuery = 'INSERT INTO ' . $ATable . ' SET ' . $this->ConvAssocToAssigns($ARecord) . ';';
        $vResult = $this->Query($vQuery);
        if ( $vResult !== false  )
            return mysql_insert_id($this->_MySQLHandle);
        else
            return $vResult;
    }


    function Update($ATable, $AWhere='', &$ARecord)
    {
        if ( is_array($ARecord) )
        {
            $vQuery = 'UPDATE ' . $ATable . ' SET ' .  $this->ConvAssocToAssigns($ARecord) .
                      ($AWhere==''?'': (' WHERE '. $AWhere )) .
                        ';';
            return $this->Query($vQuery);
        }
        else
            return FALSE;
    }


    function Delete($ATable, $AWhere)
    {
        $vQuery = 'DELETE FROM ' . $ATable . ($AWhere==''?'':' WHERE ' . $AWhere) . ';';
        return $this->Query($vQuery);
    }


    function& Select($ATable, $ACols='*', $AWhere='', $AOrder='', $ALimit='')
    {

        $vQuery = 'SELECT ' .
            ($ACols  == ''? '*' : $ACols) .
            ' FROM ' . $ATable .
            ($AWhere == ''? ''  : (' WHERE ' . $AWhere)) .
            ($AOrder == ''? ''  : (' ORDER BY ' . $AOrder)) .
            ($ALimit == ''? ''  : (' LIMIT ' . $ALimit)) .
            ";";
        $vTmp = $this->Query($vQuery);
        $vResult =& new TMyRows($vTmp); 
        return $vResult;
    }


    function CountRows($ATable, $ACols='*', $AWhere='')
    {
        $vQuery  = 'SELECT COUNT('.$ACols.') FROM '.$ATable.($AWhere==''? '':' WHERE '.$AWhere);
        $vResult = $this->Query($vQuery);
        return mysql_result($vResult, 0);
    }


    function& SelectList($ATable, $ACols='*', $AWhere='', $AOrder='', $ALimit='')
    {
        $vResult = array();
        $vRows   =& $this->Select($ATable, $ACols, $AWhere, $AOrder, $ALimit);
        while( $vRecord =& $vRows->Fetch() )
        {
            $vResult[] = $vRecord;
        }
        return $vResult;
    }


    function& Get($ATable, $ACols='*', $AWhere='')
    {
        $vRows   =& $this->Select($ATable, $ACols, $AWhere, '', 1);
        $vResult =& $vRows->Fetch();
        return $vResult;
    }


    /* ===== less base queries: assume that record have id field =========== */

    function UpdateEx($ATable, $AIdField, $AId, &$ARecord)
    {
        return $this->Update($ATable, $this->CondEqual($AIdField, $AId), $ARecord);
    }


    function InsertOrUpdateEx($ATable, $AIdField, &$ARecord)
    {
    $vID = @$ARecord[$AIdField];
        if ( empty($vID) )
            return $this->Insert($ATable, $ARecord);
        else
    {
    if ( $this->Update($ATable, $this->CondEqual($AIdField, $vID), $ARecord) )
       return $vID;
    else
       return false;
    }
    }


    function DeleteEx($ATable, $AIdField, $AId)
    {
        $vWhere = $this->CondEqual($AIdField, $AId);
        return $this->Delete($ATable, $vWhere);
    }

/*
function& SelectListEx($ATable, $ACols='*', $AIdField, $AId)
{
  if ( is_array($AId) )
    $vWhere = $this->CondOneOf($AIdField, $AId);
  else
    $vWhere = $this->CondEqual($AIdField, $AId);

  $vResult = array();
  $vRows   =& $this->Select($ATable, $ACol, $AWhere);
  while( $vRecord =& $vRows->Fetch() )
  {
    $vResult[] = $vRecord;
  }
  $vRows->Free();
  return $vResult;
}
*/

    function& GetEx($ATable, $AIdField, $AId, $ACols='*')
    {
        return $this->Get($ATable, $ACols, $this->CondEqual($AIdField, $AId));
    }


    function& SelectIDsEx($ATable, $AIdField, $AWhere='', $AOrder='')
    {
        $vRows = $this->Select($ATable, $AIdField, $AWhere, $AOrder);
        $vResult = array();
        while ( $vRow =& $vRows->Fetch() )
        {
            $vResult[] = $vRow[$AIdField];
        }
        return $vResult;
    }


    function& GetRBList($ATable, $AKeyCol='id', $AValCol='name', $AddNull=FALSE, $AWhere='', $AOrder='')
    {
        if ( empty($AOrder) )
        {
           $AOrder = '`'.$AValCol.'`, `'.$AKeyCol.'`';
        }

        $vRows = $this->Select($ATable,
                        '`'.$AKeyCol . '`, `'. $AValCol.'`',
                        $AWhere,
                        $AOrder);
        $vResult = array();
        $vCount  = 0;

        if ( $AddNull )
        {
          $vResult[ '' ] = '';
        }

        while ( $vRow =& $vRows->Fetch() )
        {
           $vResult[ $vRow[$AKeyCol] ] = $vRow[$AValCol];
        }
        return $vResult;
    }


/* ===== less base queries: assume that record id field is id ========== */

    function UpdateById($ATable, $AId, &$ARecord)
    {
        return $this->UpdateEx($ATable, 'id', $AId, $ARecord);
    }


    function InsertOrUpdateById($ATable, &$ARecord)
    {
        return $this->InsertOrUpdateEx($ATable, 'id', $ARecord);
    }


    function DeleteById($ATable, $AId)
    {
        return $this->DeleteEx($ATable, 'id', $AId);
    }


    function& GetById($ATable, $AId, $ACols='*')
    {
        return $this->GetEx($ATable, 'id', $AId, $ACols);
    }


    function& SelectIDsId($ATable, $AWhere='', $AOrder='')
    {
        return $this->SelectIDsEx($ATable, 'id', $AWhere, $AOrder);
    }
}


function& GetDB()
{
    static $gTMyDB;

    if ( !isset($gTMyDB) )
        $gTMyDB = new TMyDB();

    return $gTMyDB;
}

# =====================================================================



function NullIfEmpty($AValue)
{
    return ( $AValue === 0 || $AValue === '' ) ? NULL : $AValue;
}

function ForceStr($AValue)
{
    return ( $AValue === NULL ) ? '' : $AValue;
}

function ForceNull($AValue)
{
    return ( $AValue === '' ) ? NULL : $AValue;
}

function ForceBoolean($AValue)
{
    return ( $AValue == NULL || $AValue == '' || $AValue == '0' ) ? FALSE : TRUE;
}

?>
