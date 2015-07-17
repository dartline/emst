<?php
/**
 * Created by JetBrains PhpStorm.
 * User: illabb13
 * Date: 06.05.14
 * Time: 21:59
 * To change this template use File | Settings | File Templates.
 */
echo strftime("%Z").'<br>';
echo date('H:i:s').'<br>';
echo $_SERVER['SERVER_NAME'].'<br>';
echo date_default_timezone_get();
