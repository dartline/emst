<?php
    require_once('../library/TFOMS/Ident.php');
    // function createIdentRequest($fName, $mName, $lName, $bDate, $doc, $pSer, $pNum)
    try {
        $con = new PDO('mysql:dbname=s11;host=localhost;charset=utf8;', 'dbuser', 'dbpassword');
    } catch (PDOException $e) {
        echo 'Could not connect: ' . $e->getMessage();
    }

   $req = createIdentRequest(
        $_REQUEST['first_name'], // $_SESSION['client']['firstName'],
        $_REQUEST['patr_name'], //$_SESSION['client']['patrName'],
        $_REQUEST['last_name'], //$_SESSION['client']['lastName'],
        date('Y-m-d', strtotime(implode("-", $_REQUEST['born_date']))), //$_SESSION['client']['birthDate'],
        $_REQUEST['doc_number'], //$_SESSION['client']['docNumber'],
        $_REQUEST['polis_series'], //$_SESSION['client']['polisSerial'],
        $_REQUEST['polis_number'] //$_SESSION['client']['polisNumber']
    );

    try
    {
        $ident = new IdentService(dirname(dirname(__FILE__))."/library/TFOMS/Ident.wsdl");
        $smoOut = $ident->doIdentification($req);

        if ($smoOut->return->polisN)
        {
            echo json_encode($smoOut->return);
            $_REQUEST['polis_series'] = $smoOut->return->polisS;
            $_REQUEST['polis_number'] = $smoOut->return->polisN;
            $_REQUEST['patient_polis_from']   = date('d.m.Y', strtotime(substr($smoOut->return->dateBegin, 0, 10)));
            $_REQUEST['patient_polis_to']     = date('d.m.Y', strtotime(substr($smoOut->return->dateEnd, 0, 10)));

            $smoId = $smoOut->return->idSmo;

            $smo = createSmoRequest();
            $smoOut = $ident->getIdSmo($smo);
            foreach($smoOut->return as $i)
            {
                if ($i->item[0] == $smoId)
                {
                    $smoFullName = $i->item[1];
                    break;
                }
            }

            //$smoShortName = dibi::fetchSingle('select [shortName] from [Organisation] where [deleted]=0 and [fullName]=%s', $smoFullName);
            $sth = $con->prepare("SELECT long_name FROM rb_insurance_companies WHERE [long_name]='{$smoFullName}' LIMIT 0,1;");
            $result = $sth->execute();

            if (empty($result)) exit();
            $result = $sth->fetch(PDO::FETCH_NUM);
            $_REQUEST['insurance_company_id'] = $smoShortName;
        }


    }
    catch (Exception $e)
    {
        echo $e->getMessage();
    }
