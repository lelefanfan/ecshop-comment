<!-- $Id: agency_info.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'validator.js,../js/transport.js,../js/region.js')); ?>
<div class="main-div">
<form method="post" action="suppliers.php" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_name']; ?></td>
    <td><input type="text" name="suppliers_name" maxlength="60" value="<?php echo $this->_var['suppliers']['suppliers_name']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_desc']; ?></td>
    <td><textarea  name="suppliers_desc" cols="60" rows="4"  ><?php echo $this->_var['suppliers']['suppliers_desc']; ?></textarea></td>
  </tr>
  <tr>
    <td class="label">
    <a href="javascript:showNotice('noticeAdmins');" title="<?php echo $this->_var['lang']['form_notice']; ?>"><img src="images/notice.gif" width="16" height="16" border="0" alt="<?php echo $this->_var['lang']['form_notice']; ?>"></a><?php echo $this->_var['lang']['label_admins']; ?></td>
    <td><?php $_from = $this->_var['suppliers']['admin_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'admin');if (count($_from)):
    foreach ($_from AS $this->_var['admin']):
?>
      <input type="checkbox" name="admins[]" value="<?php echo $this->_var['admin']['user_id']; ?>" <?php if ($this->_var['admin']['type'] == "this"): ?>checked="checked"<?php endif; ?> />
      <?php echo $this->_var['admin']['user_name']; ?><?php if ($this->_var['admin']['type'] == "other"): ?>(*)<?php endif; ?>&nbsp;&nbsp;
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?><br />
    <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeAdmins"><?php echo $this->_var['lang']['notice_admins']; ?></span></td>
  </tr>
</table>

<table align="center">
  <tr>
    <td colspan="2" align="center">
      <input type="submit" class="button" value="<?php echo $this->_var['lang']['button_submit']; ?>" />
      <input type="reset" class="button" value="<?php echo $this->_var['lang']['button_reset']; ?>" />
      <input type="hidden" name="act" value="<?php echo $this->_var['form_action']; ?>" />
      <input type="hidden" name="id" value="<?php echo $this->_var['suppliers']['suppliers_id']; ?>" />
    </td>
  </tr>
</table>
</form>
</div>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js')); ?>

<script language="JavaScript">
<!--
document.forms['theForm'].elements['suppliers_name'].focus();

onload = function()
{
    // 开始检查订单
    startCheckOrder();
}
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("suppliers_name",  no_suppliers_name);
    return validator.passed();
}
//-->
</script>

<?php echo $this->fetch('pagefooter.htm'); ?>