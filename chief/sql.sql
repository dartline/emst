ALTER TABLE `rb_trauma_types` 
ADD `f57_col_adult` VARCHAR( 16 ) NOT NULL ,
ADD `f57_col_child` VARCHAR( 16 ) NOT NULL ;

UPDATE `rb_trauma_types` SET `f57_col_adult` = '0,11,20',   `f57_col_child` = '18,19,20'    WHERE `rb_trauma_types`.`id` =1;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '1,11,20',   `f57_col_child` = '18,19,20'    WHERE `rb_trauma_types`.`id` =2;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '4,11,20',   `f57_col_child` = '18,19,20'    WHERE `rb_trauma_types`.`id` =3;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '2,3,11,20', `f57_col_child` = '14,15,19,20' WHERE `rb_trauma_types`.`id` =4;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '4,11,20',   `f57_col_child` = '18,19,20'    WHERE `rb_trauma_types`.`id` =5;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '5,11,20',   `f57_col_child` = '12,19,20'    WHERE `rb_trauma_types`.`id` =6;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '6,11,20',   `f57_col_child` = '13,19,20'    WHERE `rb_trauma_types`.`id` =7;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '7,8,11,20', `f57_col_child` = '14,15,19,20' WHERE `rb_trauma_types`.`id` =8;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '10,11,20',  `f57_col_child` = '16,19,20'    WHERE `rb_trauma_types`.`id` =9;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '9,11,20',   `f57_col_child` = '17,19,20'    WHERE `rb_trauma_types`.`id` =10;
UPDATE `rb_trauma_types` SET `f57_col_adult` = '10,11,20',  `f57_col_child` = '18,19,20'    WHERE `rb_trauma_types`.`id` =11;
