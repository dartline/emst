<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="emst.css" rel="stylesheet" type="text/css">
    <link href="favicon.ico" rel="shortcut icon">
    <title><?php if ($this->options['strict'] || (isset($t) && method_exists($t, 'GetProjectName'))) echo htmlspecialchars($t->GetProjectName());?>: Login page</title>
  </head>

  <body>
    <table width="100%" height="100%">
      <tr>
        <td align="center" valign="middle" width="100%">
          <?php if ($this->options['strict'] || (isset($t->form) && method_exists($t->form, 'outputHeader'))) echo $t->form->outputHeader();?>
            <?php echo $t->form->hidden;?>
            <table class="maintable">
      <?php if ($t->Message)  {?>
              <tr>
                <th colspan="2"><?php echo $t->Message;?></th>
              </tr>
      <?php }?>
              <tr>
                <th colspan="2"><?php echo $t->form->header->Header;?></th>
              </tr>
              <tr>
                <td class="label"> <?php echo $t->form->Name->label;?> </td>
                <td class="control"> <?php echo $t->form->Name->html;?>  </td>
              </tr>
              <tr>
                <td class="label"> <?php echo $t->form->Password->label;?> </td>
                <td class="control"> <?php echo $t->form->Password->html;?>  </td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td align="right"><?php echo $t->form->Submit->html;?></td>
              </tr>
            </table>
      <!--
            <table>
              <tr><td>{form.requirednote:h}</td></tr>
            </table>
      -->
            </form>
      <!--

          <p>
            for admin login use user name <b>admin</b> and password <b>admin</b><br>
          </p>
      -->
        </td>
      </tr>
    </table>
  </body>
</html>
