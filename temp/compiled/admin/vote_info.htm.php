<!-- $Id: vote_info.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<script type="text/javascript" src="../js/calendar.php?lang=<?php echo $this->_var['cfg_lang']; ?>"></script>
<link href="../js/calendar/calendar.css" rel="stylesheet" type="text/css" />
<div class="main-div">
<form action="vote.php" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table width="100%" id="general-table">
  <tr>
    <td class="label"><?php echo $this->_var['lang']['vote_name']; ?></td>
    <td>
      <input type='text' name='vote_name' value='<?php echo $this->_var['vote_arr']['vote_name']; ?>' size='40' />
    </td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['begin_date']; ?></td>
    <td>
      <input name="start_time" type="text" id="start_time" size="22" value='<?php echo $this->_var['vote_arr']['start_time']; ?>' readonly="readonly" /><input name="selbtn1" type="button" id="selbtn1" onclick="return showCalendar('start_time', '%Y-%m-%d', false, false, 'selbtn1');" value="<?php echo $this->_var['lang']['btn_select']; ?>" class="button"/>
    </td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['end_date']; ?></td>
    <td>
      <input name="end_time" type="text" id="end_time" size="22" value='<?php echo $this->_var['vote_arr']['end_time']; ?>' readonly="readonly" /><input name="selbtn2" type="button" id="selbtn2" onclick="return showCalendar('end_time', '%Y-%m-%d', false, false, 'selbtn2');" value="<?php echo $this->_var['lang']['btn_select']; ?>" class="button"/>
    </td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['can_multi']; ?></td>
    <td>
      <input type="radio" name="can_multi" value="0"<?php if ($this->_var['vote_arr']['can_multi'] == 0): ?> checked="true" <?php endif; ?>/><?php echo $this->_var['lang']['is_multi']; ?>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <input type="radio" name="can_multi" value="1"<?php if ($this->_var['vote_arr']['can_multi'] == 1): ?> checked="true" <?php endif; ?>/><?php echo $this->_var['lang']['no_multi']; ?>
    </td>
  </tr>
  <tr>
    <td class="label">&nbsp;</td>
    <td>
      <input type="submit" value="<?php echo $this->_var['lang']['button_submit']; ?>" class="button" />
      <input type="reset" value="<?php echo $this->_var['lang']['button_reset']; ?>" class="button" />
    </td>
  </tr>
</table>
    <input type="hidden" name="act" value="<?php echo $this->_var['form_act']; ?>" />
    <input type="hidden" name="id" value="<?php echo $this->_var['vote_arr']['vote_id']; ?>" />
</form>
</div>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js')); ?>
<script>
var date_error = '<?php echo $this->_var['lang']['date_error']; ?>';
</script>

<script language="JavaScript">
<!--
document.forms['theForm'].elements['vote_name'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("vote_name",      vote_name_empty);
    if(document.getElementById('start_time').value > document.getElementById('end_time').value)
    {
      alert(date_error);
      return false;
    }
    return validator.passed();
}

onload = function()
{
    // 开始检查订单
    startCheckOrder();
}
//-->
</script>

<?php echo $this->fetch('pagefooter.htm'); ?>