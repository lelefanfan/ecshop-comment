<!-- $Id: link_list.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php if ($this->_var['full_page']): ?>
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>

<form method="post" action="" name="listForm">
<!-- start ads list -->
<div class="list-div" id="listDiv">
<?php endif; ?>

<table cellpadding="3" cellspacing="1">
  <tr>
    <th><a href="javascript:listTable.sort('link_name'); "><?php echo $this->_var['lang']['link_name']; ?></a><?php echo $this->_var['sort_link_name']; ?></th>
    <th><a href="javascript:listTable.sort('link_url'); "><?php echo $this->_var['lang']['link_url']; ?></a><?php echo $this->_var['sort_link_url']; ?></th>
    <th><a href="javascript:listTable.sort('link_logo'); "><?php echo $this->_var['lang']['link_logo']; ?></a><?php echo $this->_var['sort_link_logo']; ?></th>
    <th><a href="javascript:listTable.sort('show_order'); "><?php echo $this->_var['lang']['show_order']; ?></a><?php echo $this->_var['sort_show_order']; ?></th>
    <th><?php echo $this->_var['lang']['handler']; ?></th>
  </tr>
  <tr>
  <?php $_from = $this->_var['links_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'link');if (count($_from)):
    foreach ($_from AS $this->_var['link']):
?>
  <tr>
    <td class="first-cell"><span onclick="listTable.edit(this, 'edit_link_name', <?php echo $this->_var['link']['link_id']; ?>)"><?php echo htmlspecialchars($this->_var['link']['link_name']); ?></span></td>
    <td align="left"><span><a href="<?php echo $this->_var['link']['link_url']; ?>" target="_blank"><?php echo htmlspecialchars($this->_var['link']['link_url']); ?></a></span></td>
    <td align="center"><span><?php echo $this->_var['link']['link_logo']; ?></span></td>
    <td align="right"><span onclick="listTable.edit(this, 'edit_show_order', <?php echo $this->_var['link']['link_id']; ?>)"><?php echo $this->_var['link']['show_order']; ?></span></td>
    <td align="center"><span>
    <a href="friend_link.php?act=edit&id=<?php echo $this->_var['link']['link_id']; ?>" title="<?php echo $this->_var['lang']['edit']; ?>"><img src="images/icon_edit.gif" border="0" height="16" width="16" /></a>&nbsp;
    <a href="javascript:;" onclick="listTable.remove(<?php echo $this->_var['link']['link_id']; ?>, '<?php echo $this->_var['lang']['drop_confirm']; ?>')" title="<?php echo $this->_var['lang']['remove']; ?>"><img src="images/icon_drop.gif" border="0" height="16" width="16" /></a></span></td>
  </tr>
  <?php endforeach; else: ?>
    <tr><td class="no-records" colspan="10"><?php echo $this->_var['lang']['no_links']; ?></td></tr>
  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
  <tr>
    <td align="right" nowrap="true" colspan="10"><?php echo $this->fetch('page.htm'); ?></td>
  </tr>
</table>

<?php if ($this->_var['full_page']): ?>
</div>
<!-- end ad_position list -->
</form>

<script type="text/javascript" language="JavaScript">
  listTable.recordCount = <?php echo $this->_var['record_count']; ?>;
  listTable.pageCount = <?php echo $this->_var['page_count']; ?>;

  <?php $_from = $this->_var['filter']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
  listTable.filter.<?php echo $this->_var['key']; ?> = '<?php echo $this->_var['item']; ?>';
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  
  onload = function()
  {
    // 开始检查订单
    startCheckOrder();
  }
  
</script>
<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>
