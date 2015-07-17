<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="../emst.css" type="text/css" rel="stylesheet">
    <link href="../favicon.ico" rel="shortcut icon">
    <link rel="stylesheet" href="../scripts/jquery-ui-1.10.4.custom/css/smoothness/jquery-ui-1.10.4.custom.css">
    <script src="../scripts/jquery/js/jquery-1.8.3.js"></script>
    <script src="../scripts/jquery/js/jquery-ui-1.9.2.custom.js"></script>

    <title><?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetUserName'))) echo htmlspecialchars($t->GetUserName());?>: Регистратура: история болезни</title>
      <style>
          .ui-corner-alll{width: 98%;height:205px;border:1px solid black; border-radius: 5px;}
          .error{
              background-color: #ff0000;
          }
      </style>
    <script language="javascript">
        function search_client____() {
            var vURI = "/reg/search_client_popup.html"
            vURI += "?unicode=1"
            vURI += "&first_name="+encodeURIComponent(frmItem.first_name.value);
            vURI += "&last_name="+encodeURIComponent(frmItem.last_name.value);
            vURI += "&patr_name="+encodeURIComponent(frmItem.patr_name.value);
            vURI += "&is_male="+encodeURIComponent(frmItem.is_male.value);
            vURI += "&born_date_d="+encodeURIComponent(frmItem.born_date['d'].value);
            vURI += "&born_date_M="+encodeURIComponent(frmItem.born_date['M'].value);
            vURI += "&born_date_Y="+encodeURIComponent(frmItem.born_date['Y'].value);
            vURI += "&doc_type_id="+encodeURIComponent(frmItem.doc_type_id.value);
            vURI += "&doc_series="+encodeURIComponent(frmItem.doc_series.value);
            vURI += "&doc_number="+encodeURIComponent(frmItem.doc_number.value);
            vURI += "&insurance_company_id="+encodeURIComponent(frmItem.insurance_company_id.value);
            vURI += "&polis_series="+encodeURIComponent(frmItem.polis_series.value);
            vURI += "&polis_number="+encodeURIComponent(frmItem.polis_number.value);
            var vWnd = window.open(vURI, "popup");
            vWnd.focus();
        }

        function search_patients() {
            var dates = $('select[name*=born_date]').map(function() {
                var val = $(this).val();
                if (val) return val;
            }).toArray();

            $.post('output_data_from_bd.php', {
                    lastName:  $("input[name='last_name']").val(),
                    firstName: $("input[name='first_name']").val(),
                    patrName:  $("input[name='patr_name']").val(),
                    birthDate:  dates.length == 3 ? dates.reverse().join('-') : '',
                    sex:        $("select[name='is_male']").val()
                }, function (data) {
                    $('#patientResult').html(data);
                }
            )
        }
       function check_by_tfoms(){
           var msg   = $('form').serialize();
           $.ajax({
               type: 'POST',
               url: 'module.disp.php',
               data: msg,
               dataType: "json",
               success: function(data) {
                    console.log(data);
                   $("input[name='polis_number']").val(data.polisN);
                   $("input[name='polis_series']").val(data.polisS);

                   var date_begin_tmp = data.dateBegin.split("T");
                   var date_begin = date_begin_tmp[0].split("-");
                   $("select[name='patient_polis_from[d]']").val(parseInt(date_begin[2]));
                   $("select[name='patient_polis_from[M]']").val(parseInt(date_begin[1]));
                   $("select[name='patient_polis_from[Y]']").val(parseInt(date_begin[0]));

                   var date_end_tmp = data.dateEnd.split("T");
                   var date_end = date_end_tmp[0].split("-");
                   $("select[name='patient_polis_to[d]']").val(parseInt(date_end[2]));
                   $("select[name='patient_polis_to[M]']").val(parseInt(date_end[1]));
                   $("select[name='patient_polis_to[Y]']").val(parseInt(date_end[0]));


                   $("select[name='insurance_company_id']").val(parseInt(data.idSmo));

               },
               error:  function(xhr, str){
                   alert('Возникла ошибка: ' + xhr.responseCode);
               }
           });
       }

    </script>
  </head>

  <body>
    <table cellspacing="0" cellpadding="4">
      <tr>
        <td valign="top" class="maintable">
          <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetMenu'))) echo $t->GetMenu();?>
        </td>
        <td valign="top">
          <?php if ($this->options['strict'] || (isset($t->form) && method_exists($t->form, 'outputHeader'))) echo $t->form->outputHeader();?>
            <?php echo $t->form->hidden;?>

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
                  <td class="label"> <?php echo $t->form->paytype->label;?></td>
                  <td class="control"> <?php echo $t->form->paytype->html;?></td>
              </tr>
                <tr>
                    <td class="label">&nbsp;</td>
                    <td class="control" colspan="6">
                        <?php echo $this->elements['search_client']->toHtml();?>
                    </td>
                </tr>
              <tr>
                  <td class="label"> <?php echo $t->form->insurance_company_id->label;?></td>
                  <td class="control"> <?php echo $t->form->insurance_company_id->html;?></td>
                  <td class="label"> <?php echo $t->form->polis_series->label;?></td>
                  <td class="control"> <?php echo $t->form->polis_series->html;?></td>
                  <td class="label"> <?php echo $t->form->polis_number->label;?></td>
                  <td class="control"> <?php echo $t->form->polis_number->html;?></td>
              </tr>
                <tr>
                    <tr><td class="label"><?php echo $t->form->patient_polis_from->label;?></td><td class="control" colspan="4"> <?php echo $t->form->patient_polis_from->html;?></td></tr>
                    <tr><td class="label"><?php echo $t->form->patient_polis_to->label;?></td><td class="control" colspan="2"> <?php echo $t->form->patient_polis_to->html;?></td></tr>
                    <tr><td class="control" colspan="8"> <?php echo $this->elements['search_by_tfoms']->toHtml();?></td></tr>
                </tr>
              <tr>

                  <td class="control" colspan="8">
                  <div>
                      <fieldset class="ui-corner-alll" id="patientResulteFset" style="visibility: visible; background-color:#ffffff;">
                          <legend>Найденные пациенты</legend>
                          <div id="patientResult" style="height:200px;overflow:auto;"></div>
                      </fieldset>
                   </div>
                  </td>
              </tr>
              <tr><th colspan="8"><?php echo $t->form->header->header2;?></th></tr>
              <tr><td class="label"> <?php echo $t->form->trauma_type_id->label;?></td><td class="control" colspan="7"> <?php echo $t->form->trauma_type_id->html;?></td></tr>
              <tr><td class="label"> <?php echo $t->form->accident->label;?>              </td><td class="control" colspan="7"> <?php echo $t->form->accident->html;?>              </td></tr>
              <tr><td class="label"> <?php echo $t->form->accident_datetime->label;?>     </td><td class="control" colspan="7"> <?php echo $t->form->accident_datetime->html;?>     </td></tr>
              <tr><td class="label"> <?php echo $t->form->accident_place->label;?>        </td><td class="control" colspan="7"><?php echo $t->form->accident_place->html;?>        </td></tr>

              <tr>
                <td class="label">   <?php echo $t->form->phone_message_required->label;?></td>
                <td class="control"> <?php echo $t->form->phone_message_required->html;?> </td>
                <td class="label">   <?php echo $t->form->animal_bite_trauma->label;?>    </td>
                <td class="control" colspan="5"> <?php echo $t->form->animal_bite_trauma->html;?>     </td>
              </tr>
              <tr>
                <td class="label">   <?php echo $t->form->ice_trauma->label;?>    </td>
                <td class="control"> <?php echo $t->form->ice_trauma->html;?>     </td>
                <td class="label">   <?php echo $t->form->ixodes_trauma->label;?> </td>
                <td class="control" colspan="5"> <?php echo $t->form->ixodes_trauma->html;?>  </td>
              </tr>
              <tr><td class="label"> <?php echo $t->form->ses_message_required->label;?>  </td><td class="control" colspan="7"> <?php echo $t->form->ses_message_required->html;?>  </td></tr>
              <tr><td class="label"> <?php echo $t->form->message_number->label;?>        </td><td class="control" colspan="7"> <?php echo $t->form->message_number->html;?>        </td></tr>
              <tr><td class="label"> <?php echo $t->form->notes->label;?></td><td class="control" colspan="7"> <?php echo $t->form->notes->html;?></td></tr>
              <tr><td align="right" colspan="8">   <?php echo $t->form->Submit->html;?>          </td>
              </tr>
            </table>
            <table>
              <tr><td><?php echo $t->form->requirednote;?></td></tr>
            </table>
          </form>
        </td>
      </tr>
    </table>
  </body>
  <script>
      $("input[name='last_name']").autocomplete({
          source: function( request, response ) {
              $.post('autocompl_lastname.php',
                      {
                          term        : request.term,
                          field       : 'lastName',
                          filter1_name: 'firstName',
                          filter1     : $("input[name='first_name']").val(),
                          filter2_name: 'patrName',
                          filter2     : $("input[name='patr_name']").val()
                      },
                      function (data)
                      {
                          response(data.split("\n"));
                      }
              );
          },
          minLength: 0,
          select: function(event, ui) {
              console.log(ui.item ?
                      "Selected: " + ui.item.value + " aka " + ui.item.id :
                      "Nothing selected, input was " + this.value
              );
          }
      });
      $("input[name='first_name']").autocomplete({
          source: function( request, response ) {
              $.post('autocompl_lastname.php',
                      {
                          term        : request.term,
                          field       : 'firstName',
                          filter1_name: 'lastName',
                          filter1     : $("input[name='last_name']").val(),
                          filter2_name: 'patrName',
                          filter2     : $("input[name='patr_name']").val()
                      },
                      function (data)
                      {
                          response(data.split("\n"));
                      }
              );
          },
          minLength: 0,
          select: function(event, ui) {
              console.log(ui.item ?
                      "Selected: " + ui.item.value + " aka " + ui.item.id :
                      "Nothing selected, input was " + this.value
              );
          }
      });
      $("input[name='patr_name']").autocomplete({
          source: function( request, response ) {
              $.post('autocompl_lastname.php',
                      {
                          term        : request.term,
                          field       : 'patrName',
                          filter1_name: 'firstName',
                          filter1     : $("input[name='first_name']").val(),
                          filter2_name: 'lastName',
                          filter2     : $("input[name='last_name']").val()
                      },
                      function (data)
                      {
                         response(data.split("\n"));
                      }
              );
          },
          minLength: 0,
          select: function(event, ui) {
              console.log(ui.item ?
                      "Selected: " + ui.item.value + " aka " + ui.item.id :
                      "Nothing selected, input was " + this.value
              );
          }
      });
      $("input[name='insurance_company_id']").autocomplete({
          source: function( request, response ) {
              $.ajax({
                  url:'autocompl_insurance_company.php',
                  type: "POST",
                  data:  {
                          term        : request.term,
                          field       : 'insurance_company_id'
                         },
                  success: function (data)
                  {
                           response(data.split("\n"));
                  },
                  error:function(xhr){
                      alert("An error occured: " + xhr.status + " " + xhr.statusText);
                  }
              });
          },
          minLength: 0,
          select: function(event, ui) {
              console.log(ui.item ?
                      "Selected: " + ui.item.value + " aka " + ui.item.id :
                      "Nothing selected, input was " + this.value
              );
          }
      });
      //$('').
      $('#patientResult').delegate('tr', 'click', function(e) {
          var id = $(e.target).closest('tr').data('id');
          console.log('You chosen patient:', id);

          $.post('filling_field.php', {
              'id': id
          }, function(data) {
              data = data.split('#');
              console.log(data);
              var dates = [];
              var dates_of_accident = [];
	      var stringAddrReg = data[16] + '.' + data[17] + ' ' + data[18] + '.' + data[19];
 	      var stringAddrLoc = data[23] + '.' + data[24] + ' ' + data[25] + '.' + data[26];
              $("input[name='last_name']").val(data[1]);
              $("input[name='first_name']").val(data[2]);
              $("input[name='patr_name']").val(data[3]);
              $("select[name='is_male']").val(data[4]);
              $("select[name='doc_type_id']").val(data[6]);
              $("input[name='doc_series']").val(data[7]);
              $("input[name='doc_number']").val(data[8]);
              $("input[name='polis_series']").val(data[9]);
              $("input[name='polis_number']").val(data[10]);
              //$("input[name='phone']").val(data[11]);
	      $("input[name='addr_reg_apartment']").val(data[13]);
              $("input[name='addr_reg_num']").val(data[14]);
              $("input[name='addr_reg_subnum']").val(data[15]);
              $("input[name='addr_reg_street']").val(stringAddrReg);
              
	      $("input[name='addr_phys_apartment']").val(data[20]);
              $("input[name='addr_phys_num']").val(data[21]);
              $("input[name='addr_phys_subnum']").val(data[22]);
              $("input[name='addr_phys_street']").val(stringAddrLoc);

              //$("input[name='employment_place']").val(data[20]);
              //$("input[name='profession']").val(data[21]);
              //$("input[name='insurance_company_id']").val(data[22]);
              //$("select[name='employment_category_id']").val(data[23]);
              //$("select[name='trauma_type_id']").val(data[24]);
              //$("textarea[name='accident']").val(data[25]);
              //$("textarea[name='accident_place']").val(data[27]);
              //$("input[name='ice_trauma']").val(data[28]);
              //$("input[name='animal_bite_trauma']").val(data[29]);
              //$("input[name='ixodes_trauma']").val(data[30]);
              //$("input[name='phone_message_required']").val(data[31]);
              //$("input[name='message_number']").val(data[32]);
              //$("textarea[name='notes']").val(data[33]);

              /*if(data[26]){
                  dates_of_accident = data[26].split("-");
                  for(var i = 0; i < dates_of_accident.length; i++){
                      dates[i] = parseInt(dates[i],10);
                  }
                  $("select[name='accident_datetime[i]']").val(dates_of_accident[4]);
                  $("select[name='accident_datetime[H]']").val(dates_of_accident[3]);
                  $("select[name='accident_datetime[d]']").val(dates_of_accident[2]);
                  $("select[name='accident_datetime[M]']").val(dates_of_accident[1]);
                  $("select[name='accident_datetime[Y]']").val(dates_of_accident[0]);
              }*/
              if (data[5]) {
                  dates = data[5].split("-");
                  for(var i = 0; i < dates.length; i++){
                      dates[i] = parseInt(dates[i],10);
                  }
                  $("select[name='born_date[d]']").val(dates[2]);
                  $("select[name='born_date[M]']").val(dates[1]);
                  $("select[name='born_date[Y]']").val(dates[0]);
              }

              if(data[11]){
                  var polis_dates = data[11].split("-");
                  for(var i = 0; i < polis_dates.length; i++){
                      polis_dates[i] = parseInt(polis_dates[i],10);
                  }
                  $("select[name='patient_polis_from[d]']").val(polis_dates[2]);
                  $("select[name='patient_polis_from[M]']").val(polis_dates[1]);
                  $("select[name='patient_polis_from[Y]']").val(polis_dates[0]);
              }
              if(data[12]){
                  var polist_dates = data[12].split("-");
                  for(var i = 0; i < polist_dates.length; i++){
                      polist_dates[i] = parseInt(polist_dates[i],10);
                  }
                  $("select[name='patient_polis_to[d]']").val(polist_dates[2]);
                  $("select[name='patient_polis_to[M]']").val(polist_dates[1]);
                  $("select[name='patient_polis_to[Y]']").val(polist_dates[0]);
              }
          });
      });
  </script>
</html>
