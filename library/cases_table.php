<?php

#####################################################################
#
# Поликлинический автоматизированный комплекс
# (c) 2005,2006 Vista
#
# Базовый класс и вспомогательные функции для вывода списка
# историй болезни
#
#####################################################################

    define('gCaseWithBadDoc',    '(emst_cases.doc_series="" OR emst_cases.doc_number="" OR emst_cases.polis_series="" OR emst_cases.polis_number="")');
//    define('gSurgeryWithBadIllDoc', '(emst_surgeries.ill_doc!="" AND (emst_surgeries.ill_beg_date="0000-00-00" OR emst_surgeries.ill_end_date="0000-00-00"  OR (emst_surgeries.ill_beg_date>emst_surgeries.ill_end_date) OR DATEDIFF(emst_surgeries.ill_end_date, emst_surgeries.ill_beg_date)>30))');
   
//    define('gSurgeryWithBadIllDoc', '(emst_surgeries.ill_doc!="" AND (emst_surgeries.ill_beg_date=0 OR emst_surgeries.ill_end_date=0 OR (emst_surgeries.ill_beg_date>emst_surgeries.ill_end_date) OR DATEDIFF(emst_surgeries.ill_end_date, emst_surgeries.ill_beg_date)>30))');

#### gSurgeryWithBadIllDoc

// 1) Работающий
    define('gSurgeryWithBadIllDoc_1', '((NOT(employment_category_id IS NULL) AND rb_employment_categories.need_ill_doc))'); 

// 2) Трудоспособность не оценена
    define('gSurgeryWithBadIllDoc_2', '(emst_surgeries.disability = 0)');

// 3) Трудоспособность сохранена
    define('gSurgeryWithBadIllDoc_3', '(emst_surgeries.disability = 1)');

// 4) Трудоспособность утрачена
    define('gSurgeryWithBadIllDoc_4', '(emst_surgeries.disability = 2)');

// 5) Документ пуст (ни отказа, ни номера с датами)

    define('gSurgeryWithBadIllDoc_5', '(
   (NOT emst_surgeries.ill_refused) AND
   (emst_surgeries.ill_doc="") AND
   (emst_surgeries.ill_doc_new="") AND
   (NOT emst_surgeries.ill_sertificat) AND
   (emst_surgeries.ill_beg_date=0) AND
   (emst_surgeries.ill_end_date=0) AND
   (NOT emst_surgeries.ill_doc_closed)
   )');

// 6) Документ заполнен правильно (отказ или всё остальное)
    define('gSurgeryWithBadIllDoc_6', '(
   ((emst_surgeries.ill_refused) AND
    (emst_surgeries.ill_doc="") AND
    (emst_surgeries.ill_doc_new="") AND
    (NOT emst_surgeries.ill_sertificat) AND
    (emst_surgeries.ill_beg_date=0) AND
    (emst_surgeries.ill_end_date=0) AND
    (NOT emst_surgeries.ill_doc_closed)
   ) OR
   ((NOT emst_surgeries.ill_refused) AND
    ( 
     (emst_surgeries.ill_doc="") != (emst_surgeries.ill_sertificat=0) AND
      emst_surgeries.ill_beg_date!=0 AND 
      emst_surgeries.ill_end_date!=0 AND
      emst_surgeries.ill_beg_date<=emst_surgeries.ill_end_date AND
      DATEDIFF(emst_surgeries.ill_end_date, emst_surgeries.ill_beg_date)<=30
    )
   )
   )');

// 7) Признак исхода: в случае указания нетрудоспособности может быть опущен б/л
    define('gSurgeryWithBadIllDoc_7', '(
   (NOT (emst_surgeries.clinical_outcome_id IS NULL)) AND
   rb_clinical_outcomes.can_skip_ill_doc_on_disability
   )');

// 8) Признак исхода: c случае сохранения трудоспособности может быть указан б/л
    define('gSurgeryWithBadIllDoc_8', '(
   (NOT (emst_surgeries.clinical_outcome_id IS NULL)) AND
   rb_clinical_outcomes.can_use_ill_doc_in_ability
   )');

// документ нетрудоспособности указан правильно, если:
// для неработающего: ~1 & 2 
// для работающего:   1 & 3 & ( 5 | 6 & 8 )
// или                1 & 4 & ( 6 | 5 & 7 )
//
// или можно переформулировать так:
// (~1 & 2) |
// (1 & 5 & ( 3 | 4 & 7 )) |
// (1 & 6 & ( 4 | 3 & 8 ))

    define('gSurgeryWithBadIllDoc', '(NOT('.
'(NOT ' . gSurgeryWithBadIllDoc_1 .' AND '. gSurgeryWithBadIllDoc_2.') OR '.
'( '. gSurgeryWithBadIllDoc_1 .' AND '. gSurgeryWithBadIllDoc_5. ' AND ( '. gSurgeryWithBadIllDoc_3 . ' OR '.  gSurgeryWithBadIllDoc_4 . ' AND '. gSurgeryWithBadIllDoc_7 .')) OR '.
'( '. gSurgeryWithBadIllDoc_1 .' AND '. gSurgeryWithBadIllDoc_6. ' AND ( '. gSurgeryWithBadIllDoc_4 . ' OR '.  gSurgeryWithBadIllDoc_3 . ' AND '. gSurgeryWithBadIllDoc_8 .'))'.
'))');


    define('gLostOutcome',
       '( emst_surgeries.next_visit_date = emst_cases.next_visit_date AND 
          emst_surgeries.clinical_outcome_id IS NULL AND
          ( DATEDIFF(CURDATE(), emst_surgeries.date) >= 30 OR
            emst_surgeries.next_visit_date = 0))');



    define('gSurgeryIsPrimary',  '(DATE(emst_surgeries.date)=DATE(emst_cases.create_time))');


    function FormatBoolean($AValue)
    {
        if ( $AValue )
            return 'да';
        else
            return 'нет';
    }


    function FormatNameEx(&$AInfo)
    {
        if ( is_array($AInfo) )
            return FormatName(@$AInfo['last_name'], $AInfo['first_name'], @$AInfo['patr_name']);
        else
            return FormatName ($AInfo);
    }


    function FormatName($ALast, $AFirst='', $APatr='')
    {
        $vResult = trim($ALast.' '.$AFirst.' '.$APatr);
        preg_match_all('/\w*\W*/', $vResult, $vWords);
        $vResult = '';
        foreach($vWords[0] as $vWord) $vResult .= ucfirst(strtolower($vWord));
        return $vResult;
    }


    function FormatShortName($ALast, $AFirst, $APatr)
    {
        $vTmp = explode(' ', FormatName($ALast, $AFirst, $APatr));
        $vResult = '';
        foreach($vTmp as $vWord)
        {
            if ( $vResult==='' )
                $vResult = $vWord.' ';
            else
                $vResult .= $vWord{0}.'.';
        }

        return $vResult;
    }


    function FormatShortNameEx($AInfo)
    {
        if ( is_array($AInfo) )
            return FormatShortName(iconv('utf-8', 'cp1251',@$AInfo['last_name']), iconv('utf-8', 'cp1251',@$AInfo['first_name']), iconv('utf-8', 'cp1251',@$AInfo['patr_name']));
        else
            return FormatShortName($AInfo);
    }


    function FormatAddress($AStreet, $ANum, $ACorpus, $AApartment)
    {
        $vResult = $AStreet;
        if ( !empty($ANum) )
          $vResult .= ' д.'.$ANum;
        if ( !empty($ACorpus) )
          $vResult .= ' к.'.$ACorpus;
        if ( !empty($AApartment) )
          $vResult .= ' кв.'.$AApartment;
        return $vResult;
    }


    function FormatAddresses($ARegAddr, $APhysAddr)
    {
        if ( $ARegAddr == $APhysAddr )
        {
            return $APhysAddr;
        }
        else
        {
            return 'Рег.:' .$ARegAddr. "\n" . 'Факт:' .$APhysAddr;
        }
    }


    function FormatProfession($APlace, $AProfession )
    {
        if ( empty($APlace) )
            return $AProfession;
        elseif ( empty($AProfession) )
            return $APlace;
        else
            return $APlace.', '.$AProfession;
    }


    function FormatBornDateAndAge($AToday, $ABornDate)
    {
        return Date2Readable($ABornDate).' ('.CalcAge($ABornDate,$AToday).')';
    }


    function FormatBornDateAndAgeLong($AToday, $ABornDate)
    {
        $vResult = Date2ReadableLong($ABornDate);
        return $vResult . ', полных лет '.CalcAge($ABornDate, $AToday);
    }


    function FormatSex($AValue)
    {
        return $AValue?'М':'Ж';
    }


    function& GetWorkableAgesList()
    {
        static $vList;

        if ( empty($vList) )
        {
            $vDB = GetDB();
            $vList = array(
                $vDB->GetRBList('rb_workable_ages', 'is_male', 'min_age', false),
                $vDB->GetRBList('rb_workable_ages', 'is_male', 'max_age', false));
        }
        return $vList;
    }


    function IsWorkableAge($AToday, $ABornDate, $AIsMale)
    {
        $vAge = CalcAge($ABornDate, $AToday);
        $vWorkableAges = GetWorkableAgesList();
        return  @($vWorkableAges[0][$AIsMale] <= $vAge && $vAge <= $vWorkableAges[1][$AIsMale]);
    }


    function FormatWorkableAge($AToday, $ABornDate, $AIsMale)
    {
        if ( IsWorkableAge($AToday, $ABornDate, $AIsMale) )
            return 'трудосп. возраст';
        else
            return 'нетрудосп. возраст';
    }


    function& GetDocTypesList()
    {
        static $vList;

        if ( empty($vList) )
        {
            $vDB = GetDB();
            $vList = $vDB->GetRBList('rb_doc_types', 'id', 'name', false);
        }
        return $vList;
    }


    function FormatDocument($ADocTypeID, $ASeries, $ANumber)
    {
        $vList = GetDocTypesList();
        $vResult = @$vList[$ADocTypeID];
        if ( !empty($ASeries) )
          $vResult .= ' серия ' .$ASeries;
        if ( !empty($ANumber) )
          $vResult .= ' № '.$ANumber;
        return trim($vResult);
    }


    function& GetSMOList()
    {
        static $vList;

        if ( empty($vList) )
        {
            $vDB = GetDB();
            $vList = $vDB->GetRBList('rb_insurance_companies', 'id', 'long_name', false);
        }
        return $vList;
    }


    function FormatPolis($ACompanyID, $ASeries, $ANumber)
    {
        $vList = GetSMOList();
        $vResult = @$vList[$ACompanyID];
        if ( empty($vResult) )
            $vResult = @$vList[0];
        if ( !empty($ASeries) )
          $vResult .= ' серия ' .$ASeries;
        if ( !empty($ANumber) )
          $vResult .= ' № '.$ANumber;
        return trim($vResult);
    }


    function FormatPolisEx($ACompanyID, $ASeries, $ANumber)
    {
        $vList = GetSMOList();
        $vResult = @$vList[$ACompanyID];
        if ( !empty($ASeries) )
          $vResult .= ' серия ' .$ASeries;
        if ( !empty($ANumber) )
          $vResult .= ' № '.$ANumber;
        return trim($vResult);
    }


    function& GetCategoriesList()
    {
        static $vList;

        if ( empty($vList) )
        {
            $vDB = GetDB();
            $vList = $vDB->GetRBList('rb_employment_categories', 'id', 'name', false);
        }
        return $vList;
    }


    function FormatCategory($AID)
    {
//        $vDB = GetDB();
//        $vRecord = $vDB->GetById('rb_employment_categories', $AID);
//        $vResult = trim(@$vRecord['name']);
//        return $vResult;
        $vList = GetCategoriesList();
        $vResult = trim(@$vList[$AID]);
        return $vResult;
    }


    function& GetTraumaTypesList()
    {
        static $vList;

        if ( empty($vList) )
        {
            $vDB = GetDB();
            $vList = $vDB->GetRBList('rb_trauma_types', 'id', 'name', false);
        }
        return $vList;
    }


    function FormatTraumaType($AID)
    {
//        $vDB = GetDB();
//        $vRecord = $vDB->GetById('rb_trauma_types', $AID);
//        $vResult = trim(@$vRecord['name']);
        $vList = GetTraumaTypesList();
        $vResult = trim(@$vList[$AID]);
        return $vResult;
    }


    function FormatDisability($ADisability)
    {
        switch ( $ADisability )
        {
            case 2  : return 'утрачена';
            case 1  : return 'сохранена';
            default : return '';
        }
    }


    function FormatAntitetanus($AID, $ASeries)
    {
        $vDB = GetDB();
        $vRecord = $vDB->GetById('rb_antitetanus', $AID);
        $vResult = trim(@$vRecord['name'].' '.$ASeries);
        return $vResult;
    }


    function FormatDynamic($AID)
    {
        $vDB = GetDB();
        $vRecord = $vDB->GetById('rb_dynamics', $AID);
        $vResult = trim(iconv('utf-8', 'cp1251',@$vRecord['name']));
        return $vResult;
    }


    function FormatManipulation($AID, $AText)
    {
        $vDB = GetDB();
        $vRecord = $vDB->GetById('rb_manipulations', $AID);
        $vResult = trim(iconv('utf-8', 'cp1251',@$vRecord['name']).' '.iconv('utf-8', 'cp1251',$AText));
        return $vResult;
    }


    function& GetClinicalOutcomesList()
    {
        static $vList;

        if ( empty($vList) )
        {
            $vDB = GetDB();
            $vList = $vDB->GetRBList('rb_clinical_outcomes', 'id', 'name', true);
        }
        return $vList;
    }


    function FormatClinicalOutcome($AID, $ANotes = '')
    {
//        $vDB = GetDB();
//        $vRecord = $vDB->GetById('rb_clinical_outcomes', $AID);
//        $vResult = trim(@$vRecord['name']);
        $vList   = GetClinicalOutcomesList();
        $vResult = trim(@$vList[$AID] . ' '. $ANotes);
        return $vResult;
    }




    function& GetUsersFullNameList()
    {
        static $vList;

        if ( empty($vList) )
        {
            $vDB = GetDB();
            $vList = $vDB->GetRBList('users','id', 'full_name', false);
        }
        return $vList;
    }


    function FormatUserName($AID)
    {
        $vList = GetUsersFullNameList();
        $vResult = trim(@$vList[$AID]);
        return $vResult;
    }



    function& GetBranchInfo($AID=NULL)
    {
        static $vInfo;
        if ( empty($AID) )
            $AID = 1; // force default id

        if ( empty($vInfo) || $vInfo['id'] != $AID)
        {
            $vDB = GetDB();
            $vInfo = $vDB->GetById('branches', $AID);
            if (  !is_array($vInfo) )
            {
                $vInfo = array();
                $vInfo['id'] = $AID;
            }
        }
        return  $vInfo;
    }



    /////////////////////////////////////////////////////////

    function tcfName($AID, &$ARow)
    {
        return htmlspecialchars($ARow['last_name'].' '.$ARow['first_name'].' '.$ARow['patr_name']);
    }


    function tcfBornDate($ADate, &$ARow)
    {
        $vBornDate = $ARow['born_date'];
        return htmlspecialchars(FormatBorndateAndAge($ADate, $vBornDate));
    }


    function tcfSex($ASex)
    {
        return htmlspecialchars(FormatSex($ASex));
    }



    function tcfAddress($AID, &$ARow)
    {
        $vRegAddr  = FormatAddress(@$ARow['addr_reg_street'],  @$ARow['addr_reg_num'],  @$ARow['addr_reg_subnum'],  @$ARow['addr_reg_apartment']);
        $vPhysAddr = FormatAddress(@$ARow['addr_phys_street'], @$ARow['addr_phys_num'], @$ARow['addr_phys_subnum'], @$ARow['addr_phys_apartment']);
        if ( $vRegAddr == $vPhysAddr )
        {
            return htmlspecialchars($vPhysAddr);
        }
        else
        {
            return htmlspecialchars('Рег.:' .$vRegAddr).
                   '<br>'.
                   htmlspecialchars('Факт:' .$vPhysAddr);
        }

    }


    function tcfUserName($AID)
    {
        return htmlspecialchars(FormatUserName($AID));
    }


    class TCasesTable extends TTable
    {
        function TCasesTable($ATable, $ACols, $AFilter, $AOrder)
        {
            $this->TTable($ATable, $ACols, $AFilter, $AOrder, 'id');

            $this->AddColumn('id',                    '№', array('align'=>'right'));
            $this->AddDateColumn('create_time',       'Дата и время обращения');
            $this->AddColumn('first_doctor_id',       'Врач',   array('align'=>'left', 'fmt'=>'tcfUserName'));

            $this->AddColumn('id',                    'Фамилия Имя Отчество',   array('align'=>'left', 'fmt'=>'tcfName'));
            $this->AddColumn('create_time',           'Дата рождения, полных лет', array('align'=>'left', 'fmt'=>'tcfBornDate'));
            $this->AddColumn('is_male',               'Пол',           array('align'=>'center', 'fmt'=>'tcfSex'));
            $this->AddTextColumn('diagnosis',         'Диагноз');
            $this->AddColumn('diagnosis_mkb',         'МКБ');
        }
    }


    class TCasesTableEx extends TTable
    {

        function TCasesTableEx($ATable, $ACols, $AFilter, $AOrder)
        {
            $this->TTable($ATable, $ACols, $AFilter, $AOrder, 'id');

            $this->AddColumn('id',                    '№', array('align'=>'right'));
            $this->AddDateColumn('create_time',       'Дата и время обращения');
            $this->AddColumn('first_doctor_id',       'Врач',   array('align'=>'left', 'fmt'=>'tcfUserName'));
            $this->AddColumn('id',                    'Фамилия Имя Отчество',   array('align'=>'left', 'fmt'=>'tcfName'));
            $this->AddColumn('create_time',           'Дата рождения, полных лет', array('align'=>'left', 'fmt'=>'tcfBornDate'));
            $this->AddColumn('is_male',               'Пол',           array('align'=>'center', 'fmt'=>'tcfSex'));
            $this->AddTextColumn('accident',          'Что произошло');
            $this->AddDateColumn('accident_datetime', 'Дата и время происшествия');
            $this->AddTextColumn('diagnosis',         'Диагноз');
            $this->AddColumn('diagnosis_mkb',         'МКБ');
        }
    }


?>