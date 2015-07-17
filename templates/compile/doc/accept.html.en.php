<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="../emst.css" type="text/css" rel="stylesheet">
    <link href="../favicon.ico" rel="shortcut icon">
    <title><?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetUserName'))) echo htmlspecialchars($t->GetUserName());?>: Врач: Приём</title>
  </head>

  <body>
    <?php if ($t->popup_url)  {?>
        <?php require_once 'HTML/Javascript/Convert.php';?>
<script type='text/javascript'>
<?php $__tmp = HTML_Javascript_Convert::convertVar($t->popup_url,'popup_url',true);echo (is_a($__tmp,"PEAR_Error")) ? ("<pre>".print_r($__tmp,true)."</pre>") : $__tmp;?>
</script>
        <script language="javascript"><!--
            var s =  popup_url;
            var vWnd = window.open(popup_url, "print_popup", "location=0; menubar=0; resizable=1; scrollbars=0; status=0; toolbar=0;");
            vWnd.focus();
        --></script>
    <?php }?>
    <table cellspacing="0" cellpadding="4">
      <tr>
        <td valign="top" class="maintable">
          <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetMenu'))) echo $t->GetMenu();?>
        </td>
        <td valign="top">
          <?php if ($this->options['strict'] || (isset($t->form) && method_exists($t->form, 'outputHeader'))) echo $t->form->outputHeader();?>
            <?php echo $t->form->hidden;?>
            <h1>
              <table>
                <tr><td align="left"><big><big>ИСТОРИЯ БОЛЕЗНИ <?php echo htmlspecialchars($t->case_id);?>, <?php echo htmlspecialchars($t->name);?>, <?php echo htmlspecialchars($t->age);?>, <?php echo htmlspecialchars($t->category);?>, <?php echo htmlspecialchars($t->paytype);?></big></big></td></tr>
                <tr><td align="left"><big><big>дата обращения <?php echo htmlspecialchars($t->create_time);?></big></big></td></tr>
                <?php if ($t->disability_from_date)  {?><tr><td align="left"><big><big>нетрудоспособность с <?php echo htmlspecialchars($t->disability_from_date);?></big></big></td></tr><?php }?>
                <?php if ($t->docs_is_empty)  {?><tr><td align="left"><b><i><font color="red">необходимо уточнить документ и/или полис!</font></i></b></td></tr><?php }?>
              </table>
            </h1>
            <table cellspacing="0" cellpadding="4"><tr><td><?php echo $t->form->tabs->html;?></td></tr></table>
            <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'PageIs'))) if ($t->PageIs("BaseInfoPage")) { ?>
              <table class=maintable>
                <tr><th colspan="8"><?php echo $t->form->header->header1;?></th></tr><tr>
                <tr><td class="label"> <?php echo $t->form->last_name->label;?>       </td><td class="control" colspan="7"> <?php echo $t->form->last_name->html;?>       </td></tr>
                <tr><td class="label"> <?php echo $t->form->first_name->label;?>      </td><td class="control" colspan="7"> <?php echo $t->form->first_name->html;?>      </td></tr>
                <tr><td class="label"> <?php echo $t->form->patr_name->label;?>       </td><td class="control" colspan="7"> <?php echo $t->form->patr_name->html;?>       </td></tr>
                <tr><td class="label"> <?php echo $t->form->is_male->label;?>         </td><td class="control" colspan="7"> <?php echo $t->form->is_male->html;?>         </td></tr>
                <tr><td class="label"> <?php echo $t->form->born_date->label;?>       </td><td class="control" colspan="7"> <?php echo $t->form->born_date->html;?>       </td></tr>

                <tr>
                  <td class="label"> <?php echo $t->form->doc_type_id->label;?> </td>
                  <td class="control"> <?php echo $t->form->doc_type_id->html;?>  </td>
                  <td class="label"> <?php echo $t->form->doc_series->label;?>  </td>
                  <td class="control"> <?php echo $t->form->doc_series->html;?>   </td>
                  <td class="label"> <?php echo $t->form->doc_number->label;?>  </td>
                  <td class="control" colspan="3"> <?php echo $t->form->doc_number->html;?>   </td>
                </tr>

                <tr>
                  <td class="label"> <?php echo $t->form->addr_reg_street->label;?>    </td>
                  <td class="control"> <?php echo $t->form->addr_reg_street->html;?>     </td>
                  <td class="label"> <?php echo $t->form->addr_reg_num->label;?>       </td>
                  <td class="control"> <?php echo $t->form->addr_reg_num->html;?>        </td>
                  <td class="label"> <?php echo $t->form->addr_reg_subnum->label;?>    </td>
                  <td class="control"> <?php echo $t->form->addr_reg_subnum->html;?>     </td>
                  <td class="label"> <?php echo $t->form->addr_reg_apartment->label;?> </td>
                  <td class="control"> <?php echo $t->form->addr_reg_apartment->html;?>  </td>
                </tr>

                <tr>
                  <td class="label"> <?php echo $t->form->addr_phys_street->label;?>    </td>
                  <td class="control"> <?php echo $t->form->addr_phys_street->html;?>     </td>
                  <td class="label"> <?php echo $t->form->addr_phys_num->label;?>       </td>
                  <td class="control"> <?php echo $t->form->addr_phys_num->html;?>        </td>
                  <td class="label"> <?php echo $t->form->addr_phys_subnum->label;?>    </td>
                  <td class="control"> <?php echo $t->form->addr_phys_subnum->html;?>     </td>
                  <td class="label"> <?php echo $t->form->addr_phys_apartment->label;?> </td>
                  <td class="control"> <?php echo $t->form->addr_phys_apartment->html;?>  </td>
                </tr>

                <tr><td class="label"> <?php echo $t->form->phone->label;?>           </td><td class="control" colspan="7"> <?php echo $t->form->phone->html;?>           </td></tr>
                <tr><td class="label"> <?php echo $t->form->employment_category_id->label;?></td><td class="control" colspan="7"> <?php echo $t->form->employment_category_id->html;?></td></tr>
                <tr><td class="label"> <?php echo $t->form->employment_place->label;?></td><td class="control" colspan="7"> <?php echo $t->form->employment_place->html;?></td></tr>
                <tr><td class="label"> <?php echo $t->form->profession->label;?>      </td><td class="control" colspan="7"> <?php echo $t->form->profession->html;?>      </td></tr>

                <tr>
                  <td class="label"> <?php echo $t->form->insurance_company_id->label;?></td>
                  <td class="control"> <?php echo $t->form->insurance_company_id->html;?></td>
                  <td class="label"> <?php echo $t->form->polis_series->label;?></td>
                  <td class="control"> <?php echo $t->form->polis_series->html;?></td>
                  <td class="label"> <?php echo $t->form->polis_number->label;?></td>
                  <td class="control"> <?php echo $t->form->polis_number->html;?></td>
                  <td class="label"> <?php echo $t->form->paytype->label;?></td>
                  <td class="control"> <?php echo $t->form->paytype->html;?></td>
                </tr>

                <tr><th colspan="8"><?php echo $t->form->header->header2;?></th></tr><tr>
                <tr><td class="label"> <?php echo $t->form->trauma_type_id->label;?></td><td class="control" colspan="7"> <?php echo $t->form->trauma_type_id->html;?></td></tr>
                <tr><td class="label"> <?php echo $t->form->notes->label;?></td><td class="control" colspan="7"> <?php echo $t->form->notes->html;?></td></tr>
                <tr><td align="right" colspan="8"> <?php echo $t->form->submit->html;?></td></tr>
              </table>
            <?php }?>

            <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'PageIs'))) if ($t->PageIs("FirstPassPage")) { ?>
              <table class=maintable>
                <tr><th colspan="4"><?php echo $t->form->header->header;?></th></tr>
                <tr><td class="label"> <?php echo $t->form->accident->label;?>              </td><td colspan="3" class="control"> <?php echo $t->form->accident->html;?>              </td></tr>
                <tr><td class="label"> <?php echo $t->form->accident_datetime->label;?>     </td><td colspan="3" class="control"> <?php echo $t->form->accident_datetime->html;?>     </td></tr>
                <tr><td class="label"> <?php echo $t->form->accident_place->label;?>        </td><td colspan="3" class="control"> <?php echo $t->form->accident_place->html;?>        </td></tr> 
                <tr>
                  <td class="label">   <?php echo $t->form->phone_message_required->label;?></td>
                  <td class="control"> <?php echo $t->form->phone_message_required->html;?> </td>
                  <td class="label">   <?php echo $t->form->animal_bite_trauma->label;?>    </td>
                  <td class="control"> <?php echo $t->form->animal_bite_trauma->html;?>     </td>
                </tr>
                <tr>
                  <td class="label">   <?php echo $t->form->ice_trauma->label;?>    </td>
                  <td class="control"> <?php echo $t->form->ice_trauma->html;?>     </td>
                  <td class="label">   <?php echo $t->form->ixodes_trauma->label;?> </td>
                  <td class="control"> <?php echo $t->form->ixodes_trauma->html;?>  </td>
                </tr>
                <tr><td class="label"> <?php echo $t->form->ses_message_required->label;?>  </td><td colspan="3" class="control"> <?php echo $t->form->ses_message_required->html;?>  </td></tr>
                <tr><td class="label"> <?php echo $t->form->message_number->label;?>        </td><td colspan="3" class="control"> <?php echo $t->form->message_number->html;?>        </td></tr>
                <tr>
                   <td class="label"> <?php echo $t->form->antitetanus_id->label;?>  </td>
                   <td class="control"> <?php echo $t->form->antitetanus_id->html;?> </td>
                   <td class="label"> <?php echo $t->form->antitetanus_series->label;?>  </td>
                   <td class="control"> <?php echo $t->form->antitetanus_series->html;?> </td>
                </tr>
                <tr><td class="label"> <?php echo $t->form->complaints->label;?>      </td><td colspan="3" class="control"> <?php echo $t->form->complaints->html;?>        </td></tr>
                <tr><td class="label"> <?php echo $t->form->objective->label;?>       </td><td colspan="3" class="control"> <?php echo $t->form->objective->html;?>         </td></tr>
                <tr><td class="label"> <?php echo $t->form->diagnosis->label;?>       </td><td colspan="3" class="control"> <?php echo $t->form->diagnosis->html;?>         </td></tr>
                <tr><td class="label"> <?php echo $t->form->diagnosis_mkb->label;?>   </td><td colspan="3" class="control"> <?php echo $t->form->diagnosis_mkb->html;?>     </td></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->manipulation_id->label;?>  </td>
                    <td class="control"> <?php echo $t->form->manipulation_id->html;?> </td>
                    <td colspan="2" class="control"> <?php echo $t->form->manipulation_text->html;?> </td>
                </tr>
                <tr><td class="label"> <?php echo $t->form->cure->label;?>            </td><td colspan="3" class="control"> <?php echo $t->form->cure->html;?>               </td></tr>
                <tr><td class="label"> <?php echo $t->form->notes->label;?>           </td><td colspan="3" class="control"> <?php echo $t->form->notes->html;?>              </td></tr>
                <tr><th colspan="4">Экспертиза временной нетрудоспособности (для работающих)</th></tr>
                <tr><td class="label"> <?php echo $t->form->disability->label;?>      </td><td colspan="3" class="control"> <?php echo $t->form->disability->html;?>         </td></tr>
                <tr><td class="label"> <?php echo $t->form->disability_from_date->label;?> </td><td colspan="3" class="control"> <?php echo $t->form->disability_from_date->html;?></td></tr>
                <tr><td class="label"> <?php echo $t->form->ill_refused->label;?>    </td><td colspan="3" class="control"> <?php echo $t->form->ill_refused->html;?>    </td></tr>
                <tr><td class="label"> <?php echo $t->form->ill_sertificat->label;?> </td><td colspan="3" class="control"> <?php echo $t->form->ill_sertificat->html;?> </td></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->ill_doc->label;?>         </td>
                    <td class="control"> <?php echo $t->form->ill_doc->html;?>        </td>
                    <td class="label"> <?php echo $t->form->ill_doc_closed->label;?>  </td>
                    <td class="control"> <?php echo $t->form->ill_doc_closed->html;?> </td>
                </tr>
                <tr>
                    <td class="label"> <?php echo $t->form->ill_doc_new->label;?>     </td>
                    <td class="control"> <?php echo $t->form->ill_doc_new->html;?>    </td>
                    <td class="label"> <?php echo $t->form->ill_doc_is_continue->label;?>  </td>
                    <td class="control"> <?php echo $t->form->ill_doc_is_continue->html;?> </td>
                </tr>
                <tr>
                    <td class="label">   <?php echo $t->form->ill_beg_date->label;?> </td>
                    <td class="control"> <?php echo $t->form->ill_beg_date->html;?>  </td>
                    <td class="label">   <?php echo $t->form->ill_end_date->label;?> </td>
                    <td class="control"> <?php echo $t->form->ill_end_date->html;?>  </td>
                </tr>
                <tr><td>&nbsp;</td><td colspan="3" align="right">
                  <input type="submit" value="напечатать" name="<?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetPrintIllDocButtonName'))) echo $t->GetPrintIllDocButtonName();?>" />
                </td></tr>
                <tr><td class="label"> <?php echo $t->form->next_cec_date->label;?>   </td><td colspan="3" class="control"> <?php echo $t->form->next_cec_date->html;?>     </td></tr>


                <tr><th colspan="4"><?php echo $t->form->header->cec_header;?></th></tr>
                <tr><td class="label"> <?php echo $t->form->is_cec->label;?>     </td><td colspan="3" class="control"> <?php echo $t->form->is_cec->html;?>     </td></tr>
                <tr><td class="label"> <?php echo $t->form->cec_number->label;?> </td><td colspan="3" class="control"> <?php echo $t->form->cec_number->html;?> </td></tr>
                <tr><td class="label"> <?php echo $t->form->cec_ill_doc->label;?></td><td colspan="3" class="control"> <?php echo $t->form->cec_ill_doc->html;?></td></tr>
                <tr>
<!--
                    <td class="label">   {form.cec_ill_beg_date.label:h} </td>
                    <td class="control"> {form.cec_ill_beg_date.html:h}  </td>
-->
                    <td class="label">   <?php echo $t->form->cec_ill_end_date->label;?> </td>
                    <td class="control" colspan="3"> <?php echo $t->form->cec_ill_end_date->html;?>  </td>
                </tr>
                <tr><td class="label"> <?php echo $t->form->cec_cureup_date->label;?> </td><td colspan="3" class="control"> <?php echo $t->form->cec_cureup_date->html;?> </td></tr>
                <tr><th colspan="4">Следующая явка</th></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->next_visit_date->label;?> </td><td class="control"> <?php echo $t->form->next_visit_date->html;?>  </td>
                    <td class="label"> <?php echo $t->form->next_visit_target_id->label;?> </td><td class="control"> <?php echo $t->form->next_visit_target_id->html;?>  </td>
                </tr>
                <tr><th colspan="4">Исход</th></tr>
                <tr><td class="label"> <?php echo $t->form->clinical_outcome_id->label;?> </td><td colspan="2" class="control"> <?php echo $t->form->clinical_outcome_id->html;?>     </td>
                    <td colspan="1" class="control"> <?php echo $t->form->clinical_outcome_notes->html;?></td>
                </tr>
                <tr><td class="label"> <?php echo $t->form->cec_members->label;?>     </td><td colspan="3" class="control"> <?php echo $t->form->cec_members->html;?> </td></tr>
                <tr><td>&nbsp;</td>                                   <td colspan="3" align="right"> <?php echo $t->form->submit->html;?></td></tr>
              </table>
            <?php }?>

            <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'PageIs'))) if ($t->PageIs("NonFirstPassPage")) { ?>
              <table class=maintable>
                <tr><th colspan="4"><?php echo $t->form->header->header;?></th></tr>
                <tr><td class="label"> <?php echo $t->form->complaints->label;?>  </td><td colspan="3" class="control"> <?php echo $t->form->complaints->html;?>  </td></tr>
                <tr><td class="label"> <?php echo $t->form->dynamic_id->label;?>  </td><td colspan="3" class="control"> <?php echo $t->form->dynamic_id->html;?>  </td></tr>
                <tr><td class="label"> <?php echo $t->form->objective->label;?>   </td><td colspan="3" class="control"> <?php echo $t->form->objective->html;?>   </td></tr>
                <tr><td class="label"> <?php echo $t->form->diagnosis->label;?>       </td><td colspan="3" class="control"> <?php echo $t->form->diagnosis->html;?>         </td></tr>
                <tr><td class="label"> <?php echo $t->form->diagnosis_mkb->label;?>   </td><td colspan="3" class="control"> <?php echo $t->form->diagnosis_mkb->html;?>     </td></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->manipulation_id->label;?>  </td>
                    <td class="control"> <?php echo $t->form->manipulation_id->html;?> </td>
                    <td colspan="2" class="control"> <?php echo $t->form->manipulation_text->html;?> </td>
                </tr>
                <tr><td class="label"> <?php echo $t->form->cure->label;?>        </td><td colspan="3" class="control"> <?php echo $t->form->cure->html;?>        </td></tr>
                <tr><td class="label"> <?php echo $t->form->notes->label;?>       </td><td colspan="3" class="control"> <?php echo $t->form->notes->html;?>       </td></tr>
                <tr><th colspan="4">Экспертиза временной нетрудоспособности (для работающих)</th></tr>
                <tr><td class="label"> <?php echo $t->form->disability->label;?>  </td><td colspan="3" class="control"> <?php echo $t->form->disability->html;?>  </td></tr>
                <tr><td class="label"> <?php echo $t->form->ill_refused->label;?>    </td><td colspan="3" class="control"> <?php echo $t->form->ill_refused->html;?>    </td></tr>
                <tr><td class="label"> <?php echo $t->form->ill_sertificat->label;?> </td><td colspan="3" class="control"> <?php echo $t->form->ill_sertificat->html;?> </td></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->ill_doc->label;?>         </td>
                    <td class="control"> <?php echo $t->form->ill_doc->html;?>        </td>
                    <td class="label"> <?php echo $t->form->ill_doc_closed->label;?>  </td>
                    <td class="control"> <?php echo $t->form->ill_doc_closed->html;?> </td>
                </tr>
                <tr>
                    <td class="label"> <?php echo $t->form->ill_doc_new->label;?>     </td>
                    <td class="control"> <?php echo $t->form->ill_doc_new->html;?>    </td>
                    <td class="label"> <?php echo $t->form->ill_doc_is_continue->label;?>  </td>
                    <td class="control"> <?php echo $t->form->ill_doc_is_continue->html;?> </td>
                </tr>
                <tr>
                    <td class="label">   <?php echo $t->form->ill_beg_date->label;?> </td>
                    <td class="control"> <?php echo $t->form->ill_beg_date->html;?>  </td>
                    <td class="label">   <?php echo $t->form->ill_end_date->label;?> </td>
                    <td class="control"> <?php echo $t->form->ill_end_date->html;?>  </td>
                </tr>
                <tr><td>&nbsp;</td><td colspan="3" align="right">
                  <input type="submit" value="напечатать" name="<?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetPrintIllDocButtonName'))) echo $t->GetPrintIllDocButtonName();?>" />
                </td></tr>

                <tr><td class="label"> <?php echo $t->form->next_cec_date->label;?>   </td><td colspan="3" class="control"> <?php echo $t->form->next_cec_date->html;?>     </td></tr>

                <tr><th colspan="4"><?php echo $t->form->header->cec_header;?></th></tr>
                <tr><td class="label"> <?php echo $t->form->is_cec->label;?>     </td><td colspan="3" class="control"> <?php echo $t->form->is_cec->html;?>     </td></tr>
                <tr><td class="label"> <?php echo $t->form->cec_number->label;?> </td><td colspan="3" class="control"> <?php echo $t->form->cec_number->html;?> </td></tr>
                <tr><td class="label"> <?php echo $t->form->cec_ill_doc->label;?></td><td colspan="3" class="control"> <?php echo $t->form->cec_ill_doc->html;?></td></tr>
                <tr>
<!--
                    <td class="label">   {form.cec_ill_beg_date.label:h} </td>
                    <td class="control"> {form.cec_ill_beg_date.html:h}  </td>
-->
                    <td class="label">   <?php echo $t->form->cec_ill_end_date->label;?> </td>
                    <td class="control" colspan="3"> <?php echo $t->form->cec_ill_end_date->html;?>  </td>
                </tr>
                <tr><td class="label"> <?php echo $t->form->cec_cureup_date->label;?> </td><td colspan="3" class="control"> <?php echo $t->form->cec_cureup_date->html;?> </td></tr>
                <tr><td class="label"> <?php echo $t->form->cec_members->label;?>     </td><td colspan="3" class="control"> <?php echo $t->form->cec_members->html;?> </td></tr>
                <tr><th colspan="4">Следующая явка</th></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->next_visit_date->label;?> </td><td class="control"> <?php echo $t->form->next_visit_date->html;?>  </td>
                    <td class="label"> <?php echo $t->form->next_visit_target_id->label;?> </td><td class="control"> <?php echo $t->form->next_visit_target_id->html;?>  </td>
                </tr>
                <tr><th colspan="4">Исход</th></tr>
                <tr><td class="label"> <?php echo $t->form->clinical_outcome_id->label;?> </td><td colspan="2" class="control"> <?php echo $t->form->clinical_outcome_id->html;?></td>
                    <td colspan="1" class="control"> <?php echo $t->form->clinical_outcome_notes->html;?></td>
                </tr>

                <tr><td>&nbsp;</td>                                   <td colspan="3" align="right"> <?php echo $t->form->submit->html;?></td></tr>
              </table>
            <?php }?>

            <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'PageIs'))) if ($t->PageIs("RGsPage")) { ?>
              <table class=maintable>
                <tr><th> <?php echo $t->form->header->header;?></th></tr>
                <tr><td> <?php echo $t->form->table->html;?>   </td></tr>
                <tr><td align="right"> <?php echo $t->form->submit->html;?></td></tr>
              </table>
            <?php }?>

            <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'PageIs'))) if ($t->PageIs("HospitalsPage")) { ?>
              <table class=maintable>
                <tr><th> <?php echo $t->form->header->header;?></th></tr>
                <tr><td> <?php echo $t->form->table->html;?>   </td></tr>
                <tr><td align="right"> <?php echo $t->form->submit->html;?></td></tr>
              </table>
            <?php }?>

            <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'PageIs'))) if ($t->PageIs("MiscDocsPage")) { ?>
              <table class=maintable>
                <tr><th colspan="3"> <?php echo $t->form->header->heavity_header;?></th></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->heavity->label;?> </td>
                    <td class="control"> <?php echo $t->form->heavity->html;?> </td>
                    <td class="control"> <input type="submit" value="напечатать" name="<?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetPrintHeavityConclusionButtonName'))) echo $t->GetPrintHeavityConclusionButtonName();?>" />
                </tr>

                <tr><th colspan="3"> <?php echo $t->form->header->direction_header;?></th></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->direction_target->label;?> </td>
                    <td class="control" colspan="2"> <?php echo $t->form->direction_target->html;?> </td>
                </tr>
                <tr>
                    <td class="label"> <?php echo $t->form->direction_subject->label;?> </td>
                    <td class="control"> <?php echo $t->form->direction_subject->html;?> </td>
                    <td class="control"> <input type="submit" value="напечатать" name="<?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetPrintDirectionButtonName'))) echo $t->GetPrintDirectionButtonName();?>" />
                </tr>

                <tr><th colspan="3"> <?php echo $t->form->header->physiotherapy_direction_header;?></th></tr>
                <tr>
                    <td class="label">   </td>
                    <td class="control"> </td>
                    <td class="control"> <input type="submit" value="напечатать" name="<?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetPrintPhysiotherapyDirectionButtonName'))) echo $t->GetPrintPhysiotherapyDirectionButtonName();?>" />
                </tr>

                <tr><th colspan="3"> <?php echo $t->form->header->remedial_gymnastics_direction_header;?></th></tr>
                <tr>
                    <td class="label">   </td>
                    <td class="control"> </td>
                    <td class="control"> <input type="submit" value="напечатать" name="<?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetPrintRemedialGymnasticsDirectionButtonName'))) echo $t->GetPrintRemedialGymnasticsDirectionButtonName();?>" />
                </tr>

                <tr><th colspan="3"> <?php echo $t->form->header->out_epicrisis_header;?></th></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->out_epicrisis_target->label;?> </td>
                    <td class="control" colspan="2"> <?php echo $t->form->out_epicrisis_target->html;?> </td>
                </tr>
                <tr>
                    <td class="label"> <?php echo $t->form->out_epicrisis_recomendation->label;?> </td>
                    <td class="control" colspan="2"> <?php echo $t->form->out_epicrisis_recomendation->html;?> </td>
                </tr>
                <tr>
                    <td class="label" colspan="2">&nbsp;</td>
                    <td class="control"> <input type="submit" value="напечатать" name="<?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetPrintOutEpicrisisButtonName'))) echo $t->GetPrintOutEpicrisisButtonName();?>" />
                </tr>

                <tr><th colspan="3"> <?php echo $t->form->header->studinfo_header;?></th></tr>
                <tr>
                    <td class="label"> <?php echo $t->form->studinfo_type->label;?> </td>
                    <td class="control" colspan="2"> <?php echo $t->form->studinfo_type->html;?> </td>
                </tr>
                <tr>
                    <td class="label"> <?php echo $t->form->studinfo_target->label;?> </td>
                    <td class="control" colspan="2"> <?php echo $t->form->studinfo_target->html;?> </td>
                </tr>
                <tr>
                    <td class="label"> <?php echo $t->form->studinfo_show_diagnosis->label;?> </td>
                    <td class="control" colspan="2"> <?php echo $t->form->studinfo_show_diagnosis->html;?>
                </tr>
                <tr>
                    <td class="label" colspan="2">&nbsp;</td>
                    <td class="control"> <input type="submit" value="напечатать" name="<?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetPrintStudinfoButtonName'))) echo $t->GetPrintStudinfoButtonName();?>" />
                </tr>
                <tr><td align="right" colspan="3"> <?php echo $t->form->submit->html;?></td></tr>
              </table>
            <?php }?>

            <table>
              <tr><td><?php echo $t->form->requirednote;?></td></tr>
            </table>
          </form>
          <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'DumpMe'))) echo $t->DumpMe();?>
        </td>
      </tr>
    </table>
  </body>
</html>
