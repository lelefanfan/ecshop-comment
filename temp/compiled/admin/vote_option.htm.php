<!-- $Id: vote_option.htm 16902 2009-12-18 03:56:55Z sxc_shop $ -->
<?php if ($this->_var['full_page']): ?>
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>

<div class="form-div">
<form method="post" action="javascript:newVoteOption()" name="theForm">
    <?php echo $this->_var['lang']['add_vote_option']; ?>：<input type="text" name="option_name" maxlength="100" size="30" />
    <input type="hidden" name="id" value="<?php echo $this->_var['id']; ?>" size="30" />
    <input type="submit" value="<?php echo $this->_var['lang']['button_submit']; ?>" class="button" />
</form>
</div>

<!-- start option list -->
<div class="list-div" id="listDiv">
<?php endif; ?>

<table cellspacing='1' cellpadding='3' id='listTable'>
  <tr>
    <th><?php echo $this->_var['lang']['option_name']; ?></th>
    <th><?php echo $this->_var['lang']['option_order']; ?></th>
    <th><?php echo $this->_var['lang']['vote_count']; ?></th>
    <th><?php echo $this->_var['lang']['handler']; ?></th>
  </tr>
  <?php $_from = $this->_var['option_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>
    <tr align="center">
      <td align="left" class="first-cell">
      <span onclick="javascript:listTable.edit(this, 'edit_option_name', <?php echo $this->_var['list']['option_id']; ?>)"><?php echo htmlspecialchars($this->_var['list']['option_name']); ?></span>
      </td>
      <td><span onclick="javascript:listTable.edit(this, 'edit_option_order', <?php echo $this->_var['list']['option_id']; ?>)"><?php echo $this->_var['list']['option_order']; ?></span></td>
      <td><?php echo $this->_var['list']['option_count']; ?></td>
      <td><a href="javascript:;" onclick="listTable.remove(<?php echo $this->_var['list']['option_id']; ?>, '<?php echo $this->_var['lang']['drop_confirm']; ?>', 'remove_option')" title="<?php echo $this->_var['lang']['remove']; ?>"><img src="images/icon_drop.gif" border="0" height="16" width="16"></a>
      </td>
    </tr>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>

<?php if ($this->_var['full_page']): ?>
</div>

<script language="JavaScript">

onload = function()
{
  document.forms['theForm'].elements['option_name'].focus();

  // 开始检查订单
  startCheckOrder();
}

function newVoteOption()
{
  var option_name = Utils.trim(document.forms['theForm'].elements['option_name'].value);
  var id          = Utils.trim(document.forms['theForm'].elements['id'].value);

  if (Utils.trim(option_name).length > 0)
  {
    Ajax.call('vote.php?is_ajax=1&act=new_option', 'option_name=' + option_name +'&id=' + id, newGoodsTypeResponse, "POST", "JSON");
  }
}

function newGoodsTypeResponse(result)
{
  if (result.error == 0)
  {
    document.getElementById('listDiv').innerHTML = result.content;
    document.forms['theForm'].elements['option_name'].value = '';
    document.forms['theForm'].elements['option_name'].focus();
  }

  if (result.message.length > 0)
  {
    alert(result.message);
  }
}

</script>

<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>