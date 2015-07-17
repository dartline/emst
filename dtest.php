<?php
//	setlocale (LC_TIME, 'ru_RU'); 
//	print strftime('%d %b %Y');
//	print strftime('%d %B %Y');
//	print strftime('%c');
    $s = "РџСЂРѕРІРµСЂРєР°";
    $s2="Проверка";
//	print '<br>';
//	echo iconv("CP1251","UTF-8","Проверка - This is a test."); 
	print '<br>';
	echo iconv("UTF-8", "CP1251", $s); 
	print '<br>';
	echo mb_detect_encoding($s) . '/'. mb_detect_encoding($s2); 
	print '<br>';
	echo mb_detect_encoding($s, "auto") .'/'.mb_detect_encoding($s2, "auto"); 
	print '<br>';
	echo mb_detect_encoding($s, array('ASCII','UTF-8','CP1251','KOI8-R')).'/'.mb_detect_encoding($s2, array('ASCII','UTF-8','CP1251','KOI8-R')); 
?>