<?php

$ini = ini_set("soap.wsdl_cache_enabled","0");

class stringArray {
  public $item; // string
}

class getIdTArea {
  public $arg0; // string
  public $arg1; // string
}

class getIdTAreaResponse {
  public $return; // stringArray
}

class _Exception {
  public $message; // string
}

class getIdGeonimType {
  public $arg0; // string
  public $arg1; // string
}

class getIdGeonimTypeResponse {
  public $return; // stringArray
}

class getIdSmo {
  public $arg0; // string
  public $arg1; // string
}

class getIdSmoResponse {
  public $return; // stringArray
}

class doIdentification {
  public $arg0; // string
  public $arg1; // string
  public $arg2; // identTO
}

class identTO {
  public $idCase; // string
  public $dateBegin; // dateTime
  public $dateEnd; // dateTime
  public $surname; // string
  public $name; // string
  public $secondName; // string
  public $birthday; // dateTime
  public $docNumber; // string
  public $polisS; // string
  public $polisN; // string
  public $idTArea; // long
  public $idGeonimName; // long
  public $idGeonimType; // long
  public $house; // string
  public $idSmo; // long
  public $numTest; // long
  public $agrType; // long
}

class doIdentificationResponse {
  public $return; // identTO
}

class getIdGeonimName {
  public $arg0; // string
  public $arg1; // string
}

class getIdGeonimNameResponse {
  public $return; // stringArray
}


/**
 * IdentService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class IdentService extends SoapClient {

  private static $classmap = array(
                                    'stringArray' => 'stringArray',
                                    'getIdTArea' => 'getIdTArea',
                                    'getIdTAreaResponse' => 'getIdTAreaResponse',
                                    'Exception' => '_Exception',
                                    'getIdGeonimType' => 'getIdGeonimType',
                                    'getIdGeonimTypeResponse' => 'getIdGeonimTypeResponse',
                                    'getIdSmo' => 'getIdSmo',
                                    'getIdSmoResponse' => 'getIdSmoResponse',
                                    'doIdentification' => 'doIdentification',
                                    'identTO' => 'identTO',
                                    'doIdentificationResponse' => 'doIdentificationResponse',
                                    'getIdGeonimName' => 'getIdGeonimName',
                                    'getIdGeonimNameResponse' => 'getIdGeonimNameResponse',
                                   );

  public function IdentService($wsdl = "library/TFOMS/Ident.wsdl", $options = array()) {
    foreach(self::$classmap as $key => $value) {
      if(!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }
    parent::__construct($wsdl, $options);
  }

  /**
   *  
   *
   * @param doIdentification $parameters
   * @return doIdentificationResponse
   */
  public function doIdentification(doIdentification $parameters) {
    return $this->__soapCall('doIdentification', array($parameters),       array(
            'uri' => 'http://identification.ws.eis.spb.ru/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getIdTArea $parameters
   * @return getIdTAreaResponse
   */
  public function getIdTArea(getIdTArea $parameters) {
    return $this->__soapCall('getIdTArea', array($parameters),       array(
            'uri' => 'http://identification.ws.eis.spb.ru/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getIdGeonimName $parameters
   * @return getIdGeonimNameResponse
   */
  public function getIdGeonimName(getIdGeonimName $parameters) {
    return $this->__soapCall('getIdGeonimName', array($parameters),       array(
            'uri' => 'http://identification.ws.eis.spb.ru/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getIdGeonimType $parameters
   * @return getIdGeonimTypeResponse
   */
  public function getIdGeonimType(getIdGeonimType $parameters) {
    return $this->__soapCall('getIdGeonimType', array($parameters),       array(
            'uri' => 'http://identification.ws.eis.spb.ru/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param getIdSmo $parameters
   * @return getIdSmoResponse
   */
  public function getIdSmo(getIdSmo $parameters) {
    return $this->__soapCall('getIdSmo', array($parameters),       array(
            'uri' => 'http://identification.ws.eis.spb.ru/',
            'soapaction' => ''
           )
      );
  }

}

function create_guid($namespace = '') {
  static $guid = '';
  $uid = uniqid("", true);
  $data = $namespace;
  $data .= $_SERVER['REQUEST_TIME'];
  $data .= $_SERVER['HTTP_USER_AGENT'];
  //$data .= $_SERVER['LOCAL_ADDR'];
  //$data .= $_SERVER['LOCAL_PORT'];
  $data .= $_SERVER['REMOTE_ADDR'];
  $data .= $_SERVER['REMOTE_PORT'];
  $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
  $guid = '{' .   
          substr($hash,  0,  8) . 
          '-' .
          substr($hash,  8,  4) .
          '-' .
          substr($hash, 12,  4) .
          '-' .
          substr($hash, 16,  4) .
          '-' .
          substr($hash, 20, 12) .
          '}';
  return $guid;
}

function createIdentRequest($fName, $mName, $lName, $bDate, $doc, $pSer, $pNum)
{
    $g_config  =array (
            'login'           => 'dp35_soza',
            'passw'           => '7A29Nfzs'
        );

    $smoIn = new doIdentification;
    $smoIn->arg0         = $g_config['login'];
    $smoIn->arg1         = $g_config['passw'];
    $smoIn->arg2         = new identTO;
    $smoIn->arg2->idCase = create_guid();//'d6903610-b636-11e1-afa6-0800200c9a61';


    $smoIn->arg2->dateBegin  = date('Y-m-d\TH:i:s.00', time());
    $smoIn->arg2->surname    = $lName; // string
    $smoIn->arg2->name       = $fName; // string
    $smoIn->arg2->secondName = $mName; // string
    $smoIn->arg2->birthday   = $bDate.'T00:00:00.00'; // dateTime
    $smoIn->arg2->idTArea       = 0; // long
    $smoIn->arg2->idGeonimName  = 0; // long
    $smoIn->arg2->idGeonimType  = 0; // long
    $smoIn->arg2->house         = '-'; // string
    $smoIn->arg2->docNumber     = $doc;
    $smoIn->arg2->polisS        = $pSer;
    $smoIn->arg2->polisN        = $pNum;
    
    return $smoIn;
}

function createSmoRequest()
{
    $g_config  =array (
        'login'           => 'dp35_soza',
        'passw'           => '7A29Nfzs',
    );
    
    $smoIn = new getIdSmo;
    $smoIn->arg0         = $g_config['login'];
    $smoIn->arg1         = $g_config['passw'];
    
    return $smoIn;
}
