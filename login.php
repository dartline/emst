<?php

#####################################################################
#
# Травмпункт. (c) 2005 Vista
#
#####################################################################


require_once 'library/common.php';
require_once 'library/users.php';

class TLoginForm extends HTML_QuickFormEx
{
    function TLoginForm()
    {
        $this->HTML_QuickForm('frmLogin', 'post', $_SERVER['REQUEST_URI']);
        $this->addElement('header',   'Header',   'Введите имя и пароль');
        $this->addElement('text',     'Name',     'Имя:');
        $this->addElement('password', 'Password', 'Пароль:');
        $this->addElement('submit',   'Submit',   'Войти');

        $this->addRule('Name',        'Имя не может быть пропущено', 'required');
//        $this->addRule('Password',    'Password is required', 'required');
        $this->applyFilter('_ALL_', 'trim');
        $vDefault = array( 'Name'  => @$_COOKIE['LoginName']);
        $this->setDefaults($vDefault);
    }

    function CheckLoginName()
    {
        if ( $this->validate() )
        {
            $vValues   = $this->getSubmitValues(TRUE);
            $vName     = $vValues['Name'];
            $vPassword = $vValues['Password'];

            $vDB = GetDB();

            $vUserRecord = $vDB->Get('users',
                                     '*',
                                     $vDB->CondAnd(
                                       $vDB->CondEqual('login', $vName),
                                       $vDB->CondEqual('password', $vPassword),
                                       $vDB->CondEqual('retired', 0)));
//            unset($vDB);
            $vRoles = $vUserRecord['roles'];
            list( $vMenu, $vFirstPage ) = GetMenuAndFirstPage( $vRoles );

            if ( $vFirstPage != '' )
            {
                $_SESSION['User.ID']    = $vUserRecord['id'];
                $_SESSION['User.Login'] = $vName;
                $_SESSION['User.Name' ] = $vUserRecord['full_name'];
                $_SESSION['User.Roles'] = $vRoles;
                $_SESSION['User.FirstPage'] = $vFirstPage;
                $_SESSION['User.Menu' ] = $vMenu;
                setcookie('LoginName'   , $vName, time()+9999999);
				//var_dump($vFirstPage);
				//exit();
                Redirect( $vFirstPage );

            }
        }
        return FALSE;
    }
};


session_unset();
if ( ob_get_level() == 0 )
    ob_start();


$vForm =& new TLoginForm();
if ( !$vForm->CheckLoginName() )
#    if ( True )
{
    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vRenderer =& new HTML_QuickForm_Renderer_ObjectFlexy($vTemplate);
    $vRenderer->setHtmlTemplate('html.html');

    $vForm->accept($vRenderer);
    $vView =& new TBaseView;
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('login.html');
    $vTemplate->outputObject($vView);
}

#    print $POST;     
?> 
