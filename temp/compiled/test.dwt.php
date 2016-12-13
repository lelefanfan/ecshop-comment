<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" content="ECSHOP v3.0.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>测试模板文件</title>





</head>
<body>
<?php $_from = $this->_var['unames']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'name');if (count($_from)):
    foreach ($_from AS $this->_var['name']):
?>
  <?php echo $this->_var['name']; ?><br/>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</body>
</html>
