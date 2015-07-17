<?php

    require_once 'library/users.php';

    function tcfRoles($ARolesStr)
    {
        static $vRolesList;

        if ( !isset($vRolesList) )
            $vRolesList = GetRolesList();

        $vArray = explode(',', $ARolesStr);
        $vResult = '';
        foreach( $vArray as $vRole )
        {
            $vResult .= ( $vResult != '' ? '<br>':'' ) .
                        htmlspecialchars($vRolesList[$vRole]);
        }
        return $vResult;
    }


    class TData extends TBaseView
    {
        function GetTable()
        {
            $vDB = GetDB();

            $vTab =& new TTable('users', '*', '', 'login', 'id');
            $vTab->AddColumn('login',     'Логин');
            $vTab->AddColumn('full_name', 'ФИО');
            $vTab->AddColumn('eisCode',   'Код ЕИС ОМС');	    
            $vTab->AddColumn('roles',     'Роли', array('fmt'=>'tcfRoles'));
            $vTab->AddColumn('retired',   'Запрещён', array('align'=>'center', 'fmt'=>'tcfBoolean'));
            $vTab->AddRowAction('изменить', 'user_edit.html?id=');
            $vTab->AddTableAction('новый',  'user_edit.html');
            $vResult = $vTab->ProduceHTML($vDB, $_GET['PageIdx']+0, 20);
            return $vResult;
        }
    }

// =======================================================================

    RegisterListParams();

    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vView =& new TData;
    $vTemplate->compile('refs/users.html');
    $vTemplate->outputObject($vView);
?>