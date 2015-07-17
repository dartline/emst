<?php

  #####################################################################
  #
  # Травмпункт. (c) 2005 Vista
  #
  #####################################################################

  // роли пользователей и их страницы


    function& GetUserRolesInfoList()
    {
        static $gUserRolesInfoList;

        if ( !isset($gUserRolesInfoList) )
        {
            $gUserRolesInfoList = array(
                'admin'       => array(
                    'name'     => 'Администратор БД',
                    'features' => array(
                        'Пользователи'           => 'refs/users.html',
                        'Прививки от столбняка'  => 'refs/antitetanus.html',
                        'Страховые компании'     => 'refs/insurance_companies.html',
                        'Типы документов'        => 'refs/doc_types.html',
                        'Категории'              => 'refs/employment_categories.html',
                        'Типы травм'             => 'refs/trauma_types.html',
                        'Манипуляции'            => 'refs/manipulations.html',
                        'Динамика'               => 'refs/dynamics.html',
                        'Варианты исхода'        => 'refs/clinical_outcomes.html',
                        'Кабинеты'               => 'refs/vistit_targets.html',
                        'Направления'            => 'refs/directions.html',
                        )
                ),
                'registrator' => array(
                    'name'     => 'Регистратура',
                    'features' => array(
                        'Регистрация посетителя' => 'reg/case_edit.html',
                        'Регистрационный журнал' => 'reg/cases.html',
                        'Подбор RG на дату'      => 'reg/pick_rgs.html',
                        'Журнал телефонограмм'   => 'reg/phone_messages_list.html',

                        'Журнал "гололёд"'       => 'reg/ices_list.html',
                        'Журнал "укусы животных"'=> 'reg/animal_bites_list.html',
                        'Журнал "укусы клещей"'  => 'reg/ixodes_traumas_list.html',
                        'Журнал прививок'        => 'reg/antitetanuses_list.html',
                        'Журнал гипс'            => 'reg/plasters_list.html',
                        'Журнал RG'              => 'reg/rgs_list.html',
//                        'Стат.талоны'            => 'reg/stats_list.html',
                        'Подготовить dbf для ЕИС ОМС' => 'reg/stats_dbf_setup.html',
                        'Приём dbf из ЕИС ОМС'        => 'reg/stats_dbf_import.html',
                        )
                ),
                'doctor'      => array(
                    'name'     => 'Врач',
                    'features' => array(
                        'Регистрационный журнал' => 'doc/cases.html',
                        'Очередь'                => 'doc/queue.html',
                        'Журнал ВК'              => 'doc/cec_list.html',
                        'Явки'                   => 'doc/surgeries.html',
                        'Самоконтроль'           => 'doc/curecheck.html',
                        )
                ),
/*
                'radiologist' => array(
                    'name'     => 'Ренгенолог',
                    'features' => array(
                        'Ожидаемые посетители'   => 'rad/clients.html',
                        )
                ),
*/
                'chief'       => array(
                    'name'     => 'Зав.отделения',
                    'features' => array(
                        'Отчет по явкам'         => 'chief/surgeries_report.html',
                        'Отчет ф.57'             => 'chief/report_57.html',
                        'Отчет ф.16ВН'           => 'chief/report_16.html',
                        'Контроль листков нетрудоспособности' => 'chief/illdocs_check.html',
                        'Журнал ДМС'             => 'reg/dms_list.html',
                        )
                ),

                      );
        }
        return $gUserRolesInfoList;
    }

?>
