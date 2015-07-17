<?php

#####################################################################
#
# Травмпункт. (c) 2005 Vista
#
#####################################################################

$vPDFPage = $_GET['pdfpage'];
unset($_GET['pdfpage']);
unset($_GET['page']);
$vPDFPage = CompoundURL($vPDFPage, $_GET);

$vPrint  = false;
$vLink   = false;
$vObject = false;

$vPrint  = true;
//$vLink   = true;
$vObject = true;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
    <title>PDF document</title>
    <script language="javascript">
    <!--
            function PrintIt()
            {
                var vMyDoc = document.all["MyDoc"];
//                if ( vMyDoc.readyState == 4 )
                //vMyDoc.printWithDialog();
            }
    -->
    </script>

</head>
<body
    TOPMARGIN="0"
    LEFTMARGIN="0"
    <?php
        if ( $vObject&&$vPrint )
            print 'onload="PrintIt();"'
   ?>
>
<?php
    if ( $vLink )
    {
        print '<a href="'. $vPDFPage .'">open it</a><hr>';
    }
    if ( $vObject )
    {
?>
   <object
     id="MyDoc"
     classid="clsid:CA8A9780-280D-11CF-A24D-444553540000"
     width="100%"
     height="100%"
     onreadystatechange1="PrintIt();"
   >
        <param name="SRC" value="<?= $vPDFPage ?>">
        <embed
            SRC="<?= $vPDFPage ?>"
            TYPE="application/pdf"
            NAME="MyDoc"
            ALIGN=LEFT
            WIDTH=100%
            HEIGHT=100%
        >
   </object>
<?php
    }
?>
</body>
</html>
