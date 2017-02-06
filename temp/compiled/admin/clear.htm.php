<!-- $Id: agency_list.htm 14216 2008-03-10 02:27:21Z testyang $ -->

<?php echo $this->fetch('pageheader.htm'); ?>

<form method="post" action="database.php?act=cleardata" name="clearForm">
<div class="list-div" id="listDiv">
<?php if (! $this->_var['yunqi_login']): ?>
<table id="page-table" cellspacing="0">
  <tr>
    <th><?php echo $this->_var['lang']['manager_username']; ?></th>
    <td><input type="text" name="username"></td>
  </tr>
  <tr>
    <th><?php echo $this->_var['lang']['manager_password']; ?></th>
    <td><input type="password" name="password"></td>
  </tr>
</table>
<?php endif; ?>
<center>
    <input name="clear" type="submit" id="btnSubmit" value="<?php echo $this->_var['lang']['clear_demo_data']; ?>" class="button" />
</center>
</div>
</form>

<?php echo $this->fetch('pagefooter.htm'); ?>
