<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="../emst.css" type="text/css" rel="stylesheet">
    <link href="../favicon.ico" rel="shortcut icon">
    <title><?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetUserName'))) echo htmlspecialchars($t->GetUserName());?>: Регистратура: Загрузка отчёта из ЕИС ОМС</title>
  </head>

  <body>
    <table cellspacing="0" cellpadding="4">
      <tr>
        <td valign="top" class="maintable">
          <?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetMenu'))) echo $t->GetMenu();?>
        </td>
        <td valign="top">
          <h1>Загрузка отчёта из ЕИС ОМС</h1>
          <?php if ($this->options['strict'] || (isset($t->form) && method_exists($t->form, 'outputHeader'))) echo $t->form->outputHeader();?>
            <?php echo $t->form->hidden;?>
            <table class=maintable>
<!--
              <tr>
                <th colspan="2">{form.header.Header:h}</th>
              </tr>
-->
              <tr>
                <td class="label"> <?php echo $t->form->dbf_file->label;?> </td>
                <td class="control"> <?php echo $t->form->dbf_file->html;?>  </td>
              </tr>
              <tr>
                <td colspan="2"><hr></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td align="right"><?php echo $t->form->Submit->html;?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo $t->form->report->html;?></td>
              </tr>
            </table>
          </form>
        </td>
      </tr>
    </table>
  </body>
</html>
