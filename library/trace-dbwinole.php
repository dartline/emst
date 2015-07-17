<?php

  $gDbWinOleHandle = FALSE;

  function Trace($AStr)
  {
#    global $gDbWinOleHandle;
#
#    if ( $gDbWinOleHandle === FALSE )
#    {
#       $gDbWinOleHandle = new COM('DBW.DbWinApp');
#    }
#    if ( $gDbWinOleHandle !== FALSE )
#    {
#       if ( is_string($AStr) )
#           $gDbWinOleHandle->OutputDebugString($AStr);
#       else
#       {
#           ob_start();
#           print_r($AStr);
#           $vStr = ob_get_contents();
#           ob_end_clean();
#           $gDbWinOleHandle->OutputDebugString($vStr);
#       }
#    }
  }

?>