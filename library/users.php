<?php

  #####################################################################
  #
  # ??????????. (c) 2005 Vista
  #
  #####################################################################

    require_once('config/userroles.php');


    function& GetRolesList()
    {
        $vInfoList  = GetUserRolesInfoList();
        foreach( $vInfoList as $vRole => $vInfo )
        {
            $vResult[$vRole] = $vInfo['name'];
        }
		
        return $vResult;
    }


    function GetMenuAndFirstPage( $ARoles )
    {
        if ( empty($ARoles) )
            $ARoles = array();
        else
            $ARoles = explode(',', $ARoles);


        $vMenu = '<table>';
        $vMenu .= '<tr><td align="center"><img src="../images/logo.gif" width="120" height="128" alt="logo"></td></tr>';
        $vFirstPage = '';

        $vInfoList  = GetUserRolesInfoList();
        $vAddWPName = count($ARoles) > 1;

        foreach( $ARoles as $vRole )
        {
            if ( array_key_exists($vRole, $vInfoList) )
            {
                $vInfo = $vInfoList[$vRole];
                $vRoleName = $vInfo['name'];
                if ( $vRoleName )
                  $vMenu .= '<tr><th class="menuheader">'.htmlspecialchars($vRoleName).'</th></tr>';

                $vFeatures = $vInfo['features'];
                foreach( $vFeatures as $vName=>$vPage )
                {
                    if ( empty($vFirstPage) )
                        $vFirstPage = $vPage;
                    $vMenu .= '<tr><td class="menuaction"><a href="'.htmlspecialchars(gRootDirectory . $vPage).'">'
                           . htmlspecialchars($vName).'</a></td></tr>';
                }
                $vMenu .= '<tr><td><hr></td></tr>';
            }
        }

        $vMenu .= '<tr><td class="menuaction"><a href="' . gRootDirectory . 'logout.html">Выход</a></td></tr></table>';
        return array($vMenu, $vFirstPage);
    }


?>