<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.14
 * Time: 15:15
 */
try {
    $con = new PDO('mysql:dbname=s11;host=localhost;charset=utf8;', 'dbuser', 'dbpassword');
} catch (PDOException $e) {
    $html .= 'Could not connect: ' . $e->getMessage();
}

$query = "
    SELECT 
        Client.id,
        Client.lastName,
	Client.firstName,
	Client.patrName,
	Client.sex,
	Client.birthDate,	
	ClientDocument.documentType_id,
	ClientDocument.serial as docSerial,
	ClientDocument.number as docNumber,
	ClientPolicy.serial,
	ClientPolicy.number,
	ClientPolicy.begDate,
	ClientPolicy.endDate,

        #Вытаскиваем данные регистации type = 0  
      
        regaddr.flat as regflat, #13
        reghouseaddr.number as regNumberHouse,#14
        reghouseaddr.corpus as regCorpusHouse,#15
        regkladr.SOCR as regSocrCode,#16        
        regkladr.NAME as regNameCode,#17  
        regstreetkladr.SOCR as regStreetSocr,#18
        regstreetkladr.NAME as regStreetName,#19
        

        #Вытаскиваем данные проживания type = 1
        
        locaddr.flat as locflat,#20
        lochouseaddr.number as locNumberHouse,#21
        lochouseaddr.corpus as locCorpusHouse,#22
        lockladr.SOCR as locSocrCode,#23
        lockladr.NAME as locNameCode,#24        
        locstreetkladr.SOCR as locStreetSocr,#25
        locstreetkladr.NAME as locStreetName#26

    FROM Client
                LEFT JOIN ClientDocument ON ClientDocument.id = getClientDocumentId(Client.id)
                LEFT JOIN ClientPolicy ON ClientPolicy.id = getClientPolicyId(Client.id,1)
                
                #джойним адрес регистрации (REG ADDRESS) - 0

                LEFT JOIN ClientAddress RegAddress ON RegAddress.id = getClientRegAddressId(Client.id)                
                LEFT JOIN Address regaddr ON RegAddress.address_id = regaddr.id
                LEFT JOIN AddressHouse reghouseaddr ON regaddr.house_id = reghouseaddr.id
                LEFT JOIN kladr.KLADR regkladr ON reghouseaddr.KLADRCode = regkladr.CODE
                LEFT JOIN kladr.STREET regstreetkladr ON reghouseaddr.KLADRStreetCode = regstreetkladr.CODE
                
                #джойним адрес проживания (LOC ADDRESS) - 1

                LEFT JOIN ClientAddress LocAddress ON LocAddress.id = getClientLocAddressId(Client.id)
                LEFT JOIN Address locaddr ON LocAddress.address_id = locaddr.id
                LEFT JOIN AddressHouse lochouseaddr ON locaddr.house_id = lochouseaddr.id
                LEFT JOIN kladr.KLADR lockladr ON lochouseaddr.KLADRCode = lockladr.CODE
                LEFT JOIN kladr.STREET locstreetkladr ON lochouseaddr.KLADRStreetCode = locstreetkladr.CODE
    WHERE Client.id = '{$_REQUEST['id']}';
";

$sth = $con->prepare($query);
$sth->execute();
$result = $sth->fetch(PDO::FETCH_ASSOC);

if (empty($result)) exit();

foreach($result as $key=>$value) {
    if (stripos($key, 'name') !== false || stripos($key, 'series') !== false) {
        $result[$key] = $value;
    }
}

$result = implode("#", array_values($result));

echo $result;
