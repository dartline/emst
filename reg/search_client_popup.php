<?php
  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################


    function& GetPreparedGet()
    {
        if ( !isset($vPreparedGet) )
        {
            if ( array_key_exists( 'unicode', $_GET ) )
            {
                unset( $_GET['unicode'] );
                foreach( $_GET as $vVar => $vVal )
                {
                    $vPreparedGet[$vVar] = iconv('UTF-8', 'CP1251', $vVal);
                    $_GET[$vVar] = $vPreparedGet[$vVar];
                }
            }
            $vPreparedGet =& $_GET;
        }
        return $vPreparedGet;
    }


    function ConstructClientQuery(&$ADB, $AParams)
    {
        $vFilter = array();

        $vTable = 'imp_clients' .
              ' LEFT JOIN rb_doc_types ON imp_clients.doc_type_id=rb_doc_types.id' .
              ' LEFT JOIN rb_insurance_companies ON imp_clients.insurance_company_id=rb_insurance_companies.id' .
              ' LEFT JOIN imp_addr   AS reg_addr   ON imp_clients.addr_reg_id=reg_addr.id'  .
              ' LEFT JOIN imp_house  AS reg_house  ON reg_addr.house_id=reg_house.id'       .
              ' LEFT JOIN imp_prefix AS reg_prefix ON reg_house.prefix_id=reg_prefix.id'    .
              ' LEFT JOIN imp_geonim_name AS reg_geonim_name ON reg_prefix.geonim_name_id=reg_geonim_name.id' .
              ' LEFT JOIN imp_geonim_type AS reg_geonim_type ON reg_prefix.geonim_type_id=reg_geonim_type.id' .
              ' LEFT JOIN imp_town   AS reg_town   ON reg_prefix.town_id=reg_town.id'       .
              ' LEFT JOIN imp_addr   AS loc_addr   ON imp_clients.addr_loc_id=loc_addr.id'  .
              ' LEFT JOIN imp_house  AS loc_house  ON loc_addr.house_id=loc_house.id'       .
              ' LEFT JOIN imp_prefix AS loc_prefix ON loc_house.prefix_id=loc_prefix.id'    .
              ' LEFT JOIN imp_geonim_name AS loc_geonim_name ON loc_prefix.geonim_name_id=loc_geonim_name.id' .
              ' LEFT JOIN imp_geonim_type AS loc_geonim_type ON loc_prefix.geonim_type_id=loc_geonim_type.id' .
              ' LEFT JOIN imp_town   AS loc_town   ON loc_prefix.town_id=loc_town.id';

        $vCols = 'imp_clients.*,' .
                 'rb_doc_types.name as doc_type,'.
                 'rb_insurance_companies.name as insurance_company,'.
                 'reg_town.name as reg_town_name,'.
                 'reg_geonim_name.name as reg_street_name,'.
                 'reg_geonim_type.name as reg_street_type,'.
                 'reg_house.number as reg_number,'.
                 'reg_house.corpus as reg_corpus,'.
                 'reg_addr.flat as reg_flat,'.
                 'loc_town.name as loc_town_name,'.
                 'loc_geonim_name.name as loc_street_name,'.
                 'loc_geonim_type.name as loc_street_type,'.
                 'loc_house.number as loc_number,'.
                 'loc_house.corpus as loc_corpus,'.
                 'loc_addr.flat as loc_flat';
        AddQueryParamLike( $ADB, $vFilter, $AParams, 'first_name');
        AddQueryParamLike( $ADB, $vFilter, $AParams, 'last_name');
        AddQueryParamLike( $ADB, $vFilter, $AParams, 'patr_name');
        AddQueryParamEqual($ADB, $vFilter, $AParams, 'doc_type_id');
        AddQueryParamLike( $ADB, $vFilter, $AParams, 'doc_series');
        AddQueryParamLike( $ADB, $vFilter, $AParams, 'doc_number');
        AddQueryParamEqual($ADB, $vFilter, $AParams, 'insurance_company_id');
        AddQueryParamLike( $ADB, $vFilter, $AParams, 'polis_series');
        AddQueryParamLike( $ADB, $vFilter, $AParams, 'polis_number');
#        $vFilter = implode(' AND ', $vFilter);
        $vFilter = '0';
        $vOrder = 'last_name, first_name, patr_name, born_date, id';
        return array($vTable, $vCols, $vFilter, $vOrder);
    }


    class TSearchForm extends HTML_QuickFormEx
    {
        function TSearchForm()
        {
            $vDB = GetDB();

            $this->HTML_QuickForm('frmSearch', 'get', '/reg/search_client_popup.html');
            $this->addElement('header',   'Header',          'Поиск');

            $this->addElement('text',     'last_name',       'Фамилия',                  array('class'=>'edt_100'));
            $this->addElement('text',     'first_name',      'Имя',                      array('class'=>'edt_100'));
            $this->addElement('text',     'patr_name',       'Отчество',                 array('class'=>'edt_100'));
            $this->addElement('select',   'doc_type_id',     'Документ', $vDB->GetRBList('rb_doc_types','id', 'name', true));
            $this->addElement('text',     'doc_series',      'серия',         array('class'=>'edt_tiny'));
            $this->addElement('text',     'doc_number',      'номер',         array('class'=>'edt_tiny'));
            $this->addElement('select',   'insurance_company_id','Полис: СМО',       $vDB->GetRBList('rb_insurance_companies','id', 'name', true));
            $this->addElement('text',     'polis_series',        'серия',            array('class'=>'edt_tiny'));
            $this->addElement('text',     'polis_number',        'номер',            array('class'=>'edt_tiny'));


/*      $this->addElement('select',   'Order',           'Упорядочить по',
                        array_values( GetPropertiesSortOrder() ),
                        array('style'=>'WIDTH: 180px'));
*/
            $this->addElement('submit',   'Submit',       'Искать');

            $this->applyFilter('_ALL_', 'trim');
            $this->setDefaults(GetPreparedGet());
        }
    }


    function tcfSex($ASex)
    {
        return htmlspecialchars($ASex?'М':'Ж');
    }


    function tcfDocument($AID, &$ARow)
    {
        return htmlspecialchars($ARow['doc_type']).
               '<br>'.
               htmlspecialchars($ARow['doc_series'].' '.$ARow['doc_number']);
    }


    function tcfPolis($AID, &$ARow)
    {
        return htmlspecialchars($ARow['insurance_company']).
               '<br>'.
               htmlspecialchars($ARow['polis_series'].' '.$ARow['polis_number']);
    }


    function tcfRegAddr($AID, &$ARow)
    {
        $vResult = array();
        $vVal = $ARow['reg_town_name'];
        if ( !empty($vVal) )
          $vResult[] = htmlspecialchars($vVal . ',').'<br>';
        $vVal = trim($ARow['reg_street_name'].' '.$ARow['reg_street_type']);
        if ( !empty($vVal) )
          $vResult[] = htmlspecialchars($vVal . ',').'<br>';
        $vVal = $ARow['reg_number'];
        if ( !empty($vVal) )
            $vResult[] = htmlspecialchars('д.' . $vVal);
        $vVal = $ARow['reg_corpus'];
        if ( !empty($vVal) )
            $vResult[] = htmlspecialchars('к.' . $vVal);
        $vVal = $ARow['reg_flat'];
        if ( !empty($vVal) )
            $vResult[] = htmlspecialchars('кв.' . $vVal);

        return trim(implode(' ', $vResult));
    }


    function tcfLocAddr($AID, &$ARow)
    {
        $vResult = array();
        $vVal = $ARow['loc_town_name'];
        if ( !empty($vVal) )
          $vResult[] = htmlspecialchars($vVal . ',').'<br>';
        $vVal = trim($ARow['loc_street_name'].' '.$ARow['loc_street_type']);
        if ( !empty($vVal) )
          $vResult[] = htmlspecialchars($vVal . ',').'<br>';
        $vVal = $ARow['loc_number'];
        if ( !empty($vVal) )
            $vResult[] = htmlspecialchars('д.' . $vVal);
        $vVal = $ARow['loc_corpus'];
        if ( !empty($vVal) )
            $vResult[] = htmlspecialchars('к.' . $vVal);
        $vVal = $ARow['loc_flat'];
        if ( !empty($vVal) )
            $vResult[] = htmlspecialchars('кв.' . $vVal);

        return trim(implode(' ', $vResult));
    }


    function tcfSelectItem($AID, &$ARow)
    {
        $vItem = array();
        foreach( $ARow as $vKey => $vVal )
        {
            $vItem[] = "'" . htmlspecialchars($vKey) . "':'" . htmlspecialchars($vVal) . "'";
        }
        $vItem = '{' . implode(', ', $vItem) . '}';
        return '<input type="button" value="выбрать" onclick="onSelect('.$vItem.');">';
    }

    class TData extends TBaseView
    {
        function GetTable()
        {
            $vDB = GetDB();

            list($vTable, $vCols, $vFilter, $vOrder) = ConstructClientQuery($vDB, GetPreparedGet());

            $vTab =& new TTable($vTable, $vCols, $vFilter, $vOrder, 'id');
            $vTab->AddColumn('last_name',   'Фамилия');
            $vTab->AddColumn('first_name',  'Имя');
            $vTab->AddColumn('patr_name',   'Отчество');
            $vTab->AddColumn('sex',         'Пол', array('align'=>'center', 'fmt'=>'tcfSex'));
            $vTab->AddDateColumn('born_date',    'Дата рождения');
            $vTab->AddColumn('id',  'документ', array('align'=>'left', 'fmt'=>'tcfDocument'));
            $vTab->AddColumn('id',  'полис', array('align'=>'left', 'fmt'=>'tcfPolis'));
            $vTab->AddColumn('id',    'Адрес регистрации', array('align'=>'left', 'fmt'=>'tcfRegAddr'));
            $vTab->AddColumn('id',    'Адрес проживания',  array('align'=>'left', 'fmt'=>'tcfLocAddr'));
            $vTab->AddColumn('phone', 'Телефон');

            $vTab->AddColumn('id',  '', array('align'=>'center', 'fmt'=>'tcfSelectItem'));

            $vResult = $vTab->ProduceHTML($vDB, @($_GET['PageIdx'])+0, 20);
            return $vResult;
        }
    }

// =======================================================================

    GetPreparedGet();

    $vForm =& new TSearchForm();

    $vTemplate =& CreateTemplate();
    $vRenderer =& CreateRenderer($vTemplate);
    $vForm->accept($vRenderer);
    $vView =& new TData;
    $vView->form = $vRenderer->toObject();
    $vTemplate->compile('reg/search_client_popup.html');
    $vTemplate->outputObject($vView);

    if ( $vForm->validate() )
    {
        $vValues  = $vForm->getSubmitValues();
        $vFilter = array();
        CopyParam($vFilter, $vValues, 'last_name');
        CopyParam($vFilter, $vValues, 'first_name');
        CopyParam($vFilter, $vValues, 'patr_name');
        CopyParam($vFilter, $vValues, 'doc_type_id');
        CopyParam($vFilter, $vValues, 'doc_series');
        CopyParam($vFilter, $vValues, 'doc_number');
        CopyParam($vFilter, $vValues, 'insurance_company_id');
        CopyParam($vFilter, $vValues, 'polis_series');
        CopyParam($vFilter, $vValues, 'polis_number');
//        CopyParam($vFilter, $vValues, 'Order');
    }

?>