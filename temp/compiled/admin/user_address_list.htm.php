<!-- $Id: user_address_list.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<div class="list-div">
  <table width="100%" cellpadding="3" cellspacing="1">
     <tr>
      <th><?php echo $this->_var['lang']['consignee']; ?></th>
      <th><?php echo $this->_var['lang']['address']; ?></th>
      <th><?php echo $this->_var['lang']['link']; ?></th>
      <th><?php echo $this->_var['lang']['other']; ?></th>
    </tr>
  <?php $_from = $this->_var['address']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('Key', 'val');if (count($_from)):
    foreach ($_from AS $this->_var['Key'] => $this->_var['val']):
?>
    <tr>
      <td><?php echo htmlspecialchars($this->_var['val']['consignee']); ?></td>
      <td><?php echo $this->_var['val']['country_name']; ?>&nbsp;&nbsp;<?php echo $this->_var['val']['province_name']; ?>&nbsp;&nbsp;<?php echo $this->_var['val']['city_name']; ?>&nbsp;&nbsp;<?php echo $this->_var['val']['district_name']; ?><br />
      <?php echo htmlspecialchars($this->_var['val']['address']); ?><?php if ($this->_var['val']['zipcode']): ?>[<?php echo htmlspecialchars($this->_var['val']['zipcode']); ?>]<?php endif; ?></td>
      <td><?php echo $this->_var['lang']['tel']; ?>：<?php echo $this->_var['val']['tel']; ?><br /><?php echo $this->_var['lang']['mobile']; ?>：<?php echo $this->_var['val']['mobile']; ?><br/>email: <?php echo $this->_var['val']['email']; ?></td>
      <td><?php echo $this->_var['lang']['best_time']; ?>:<?php echo htmlspecialchars($this->_var['val']['best_time']); ?><br/><?php echo $this->_var['lang']['sign_building']; ?>:<?php echo htmlspecialchars($this->_var['val']['sign_building']); ?></td>
    </tr>
  <?php endforeach; else: ?>
    <tr><td class="no-records" colspan="4"><?php echo $this->_var['lang']['no_records']; ?></td></tr>
  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </table>
</div>
<?php echo $this->fetch('pagefooter.htm'); ?>