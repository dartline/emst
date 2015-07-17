<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="../emst.css" type="text/css" rel="stylesheet">
    <link href="../favicon.ico" rel="shortcut icon">
    <title><?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetUserName'))) echo htmlspecialchars($t->GetUserName());?>: Врач: направление на рентгенологическое исследование</title>
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
            <table class=maintable>
              <tr><th colspan="8"><?php echo $t->form->header->header;?></th></tr><tr>
              <tr><td class="label"> <?php echo $t->form->case_id->label;?>         </td><td class="control" colspan="7"> <?php echo $t->form->case_id->html;?>         </td></tr>
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
              <tr><td class="label"> <?php echo $t->form->employment_place->label;?></td><td class="control" colspan="7"> <?php echo $t->form->employment_place->html;?></td></tr>
              <tr><td class="label"> <?php echo $t->form->profession->label;?>      </td><td class="control" colspan="7"> <?php echo $t->form->profession->html;?>      </td></tr>

              <tr>
                <td class="label"> <?php echo $t->form->insurance_company_id->label;?></td>
                <td class="control"> <?php echo $t->form->insurance_company_id->html;?></td>
                <td class="label"> <?php echo $t->form->polis_series->label;?></td>
                <td class="control"> <?php echo $t->form->polis_series->html;?></td>
                <td class="label"> <?php echo $t->form->polis_number->label;?></td>
                <td class="control" colspan="3"> <?php echo $t->form->polis_number->html;?></td>
              </tr>

              <tr><td class="label"> <?php echo $t->form->date->label;?>            </td><td class="control" colspan="7"> <?php echo $t->form->date->html;?>            </td></tr>
              <tr><td class="label"> <?php echo $t->form->objective->label;?>       </td><td class="control" colspan="7"> <?php echo $t->form->objective->html;?>       </td></tr>
              <tr><td class="label"> <?php echo $t->form->area->label;?>            </td><td class="control" colspan="7"> <?php echo $t->form->area->html;?>            </td></tr>
              <tr><td class="label"> <?php echo $t->form->diagnosis->label;?>       </td><td class="control" colspan="7"> <?php echo $t->form->diagnosis->html;?>       </td></tr>
              <tr><td class="label"> <?php echo $t->form->done->label;?>            </td><td class="control" colspan="7"> <?php echo $t->form->done->html;?>            </td></tr>
              <tr><td class="label"> <?php echo $t->form->description->label;?>     </td><td class="control" colspan="7"> <?php echo $t->form->description->html;?>     </td></tr>
              <tr><td colspan="8" align="right"> <?php echo $t->form->do_Print->html;?> <?php echo $t->form->do_Save->html;?> <?php echo $t->form->do_Cancel->html;?> </td>
              </tr>
            </table>
<!--            <table>
              <tr><td>{form.requirednote:h}</td></tr>
            </table>
-->
          </form>
        </td>
      </tr>
    </table>
  </body>
</html>
