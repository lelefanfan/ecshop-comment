<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $this->_var['lang']['cp_home']; ?><?php if ($this->_var['ur_here']): ?> - <?php echo $this->_var['ur_here']; ?> <?php endif; ?></title>
<meta name="robots" content="noindex, nofollow">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="styles/general.css" rel="stylesheet" type="text/css" />
<link href="styles/main.css" rel="stylesheet" type="text/css" />
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/transport.js,common.js')); ?>
<style>
  .panel-icloud .panel-right iframe {
    height: 300px;
    margin-top: 15px;
  }
  .panel-hint{
    top: 3%;
  }
</style>
<script language="JavaScript">
<!--
// 这里把JS用到的所有语言都赋值到这里
<?php $_from = $this->_var['lang']['js_languages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
//-->
/*关闭按钮*/
  function btnCancel(item){
    var par  = item.offsetParent;
    var mask  = document.getElementById('Mask')||null;
    var cloudFrame = document.getElementById('cloudFrame')||null;
    par.style.display = 'none';
    if(mask){mask.style.display = 'none';}
    if(cloudFrame){cloudFrame.src='';}
  }
</script>
</head>
<body>

<h1>
<?php if (! $this->_var['certi']['certificate_id']): ?>
<!--云起激活系统面板-->
<div class="panel-hint panel-icloud" id="panelCloud">
  <div class="panel-cross"><span onclick="btnCancel(this)">跳过激活</span></div>
  <div class="panel-title">
    <span class="tit">您需要激活系统</span>
    <p>需要激活您的系统，请先登录或免费注册云起账号</p>
  </div>
  <div class="panel-left">
    <span>没有云起账号吗？</span>
    <p>点击下列按钮一步完成注册激活！</p>
    <a href="https://account.shopex.cn/reg?refer=yunqi_ecshop" class="btn btn-yellow" target="_blank">免费注册云起账号</a>
  </div>
  <div class="panel-right">
    <h5 class="logo">云起</h5>
    <iframe id="cloudFrame" src="" frameborder="0"></iframe>
  </div>
</div>
<!--云起激活系统面板-->

<!--遮罩-->
<div class="mask-black" id="Mask"></div>
<!--遮罩-->
<?php endif; ?>
<?php if ($this->_var['action_link'] && $this->_var['pageHtml'] == 'goods_list.htm'): ?>
  <span class="action-span btn-add-goods"><a href="<?php echo $this->_var['action_link']['href']; ?>"></a></span>
  <span class="btn-quick-goods"><a href="http://yunqi.shopex.cn/products/huodiantong" target="_blank"></a></span>
<?php elseif ($this->_var['action_link']): ?>
	<span class="action-span"><a href="<?php echo $this->_var['action_link']['href']; ?>"><?php echo $this->_var['action_link']['text']; ?></a></span>
<?php endif; ?>

<?php if ($this->_var['action_link2']): ?>
<span class="action-span"><a href="<?php echo $this->_var['action_link2']['href']; ?>"><?php echo $this->_var['action_link2']['text']; ?></a>&nbsp;&nbsp;</span>
<?php endif; ?>
<span class="action-span1"><a href="index.php?act=main"><?php echo $this->_var['lang']['cp_home']; ?></a> </span><span id="search_id" class="action-span1"><?php if ($this->_var['ur_here']): ?> &nbsp;&nbsp;&nbsp;&nbsp; <?php echo $this->_var['ur_here']; ?> <?php endif; ?></span>
<div style="clear:both"></div>
</h1>