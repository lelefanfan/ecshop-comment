<div class="box boxcategory">
  <div class="box_1">
    <h3><span class="text">商品全部分类</span></h3>
    <div id="category_tree">
      <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat_0_59617800_1485221784');if (count($_from)):
    foreach ($_from AS $this->_var['cat_0_59617800_1485221784']):
?>
      <dl>
        <dt><a href="<?php echo $this->_var['cat_0_59617800_1485221784']['url']; ?>"><?php echo htmlspecialchars($this->_var['cat_0_59617800_1485221784']['name']); ?></a></dt>
        <?php $_from = $this->_var['cat_0_59617800_1485221784']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child_0_59617800_1485221784');if (count($_from)):
    foreach ($_from AS $this->_var['child_0_59617800_1485221784']):
?>
        <dd><a href="<?php echo $this->_var['child_0_59617800_1485221784']['url']; ?>"><?php echo htmlspecialchars($this->_var['child_0_59617800_1485221784']['name']); ?></a></dd>
        <?php $_from = $this->_var['child_0_59617800_1485221784']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'childer_0_59617800_1485221784');if (count($_from)):
    foreach ($_from AS $this->_var['childer_0_59617800_1485221784']):
?>
        <dd>&nbsp;&nbsp;<a href="<?php echo $this->_var['childer_0_59617800_1485221784']['url']; ?>"><?php echo htmlspecialchars($this->_var['childer_0_59617800_1485221784']['name']); ?></a></dd>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </dl>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      <div class="clear0"></div>
    </div>
  </div>
</div>
<div class="blank5"></div>
