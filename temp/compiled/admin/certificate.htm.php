<!-- $Id: sitemap.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<div class="main-div">
<p style="padding: 0 10px"><?php echo $this->_var['lang']['license_notice']; ?></p>
</div>
<div class="main-div">
<table width="100%">
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_shopex']; ?></td>
    <td></td>
</tr>
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_shopex_id']; ?></td>
    <td><?php echo $this->_var['certificate']['passport_uid']; ?></td>
</tr>

</table>
</div>
<div class="main-div">
<table width="100%">
<form method="POST" action="certificate.php?act=download" enctype="multipart/form-data" name="theForm">
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_certificate']; ?></td>
    <td></td>
</tr>
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_node_id']; ?></td>
    <td><?php echo $this->_var['certificate']['node_id']; ?></td>
</tr>
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_certificate_id']; ?></td>
    <td><?php echo $this->_var['certificate']['certificate_id']; ?></td>
</tr>
<?php if ($this->_var['certificate']['certificate_id']): ?>
<tr>
    <td></td>
    <td><input type="submit" value="<?php echo $this->_var['lang']['download_license']; ?>" class="button" />
    </td>
</tr>
<?php endif; ?>
</form>
<?php if (! $this->_var['certificate']['certificate_id']): ?>
<tr>
    <td></td>
    <td><button class="button" onclick="get_certificate();"><?php echo $this->_var['lang']['yunqi_certicate']; ?></button></td>
</tr>
<?php endif; ?>
</table>
</div>
<div class="main-div">
  <table width="100%">
  <form method="POST" action="certificate.php?act=delete" enctype="multipart/form-data" name="theForm">
    <tr>
      <td class="label"><?php echo $this->_var['lang']['label_delete_certificate']; ?></td>
      <td><input type="submit" value="<?php echo $this->_var['lang']['delete_certificate']; ?>" class="button" /></td>
    </tr>
  </form>
  </table>
</div>
<div class="main-div">
<table width="100%">
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_bindrelation']; ?></td>
    <?php if (! $this->_var['is_bind']): ?><td><a href="certificate.php?act=apply_bindrelation" target="_blank" class="button"><?php echo $this->_var['lang']['apply_bindrelation']; ?></a></td><?php endif; ?>
    <td><a href="certificate.php?act=accept_bindrelation" target="_blank" class="button"><?php echo $this->_var['lang']['accept_bindrelation']; ?></a></td>
    <td></td>
</tr>

</table>
</div>
<div class="main-div">
<table width="100%">
<tr>
    <td class="label"><?php echo $this->_var['lang']['label_my_version']; ?></td>
    <td><?php echo $this->_var['message']; ?></td>
    <td></td>
</tr>

</table>
</div>
<?php if (! $this->_var['certificate']['certificate_id']): ?>
<!--云起激活系统面板-->
<div class="panel-hint panel-icloud" id="panelCloudCerti" style="display:none;">
  <div class="panel-cross"><span onclick="btnCancel(this)">跳过激活</span></div>
  <div class="panel-title">
    <span class="tit">您需要激活系统</span>
    <p>需要激活您的系统，请先登录或免费注册云起账号</p>
  </div>
  <div class="panel-left">
    <span>没有云起账号吗？</span>
    <p>点击下列按钮一步完成注册激活！</p>
    <a href="https://account.shopex.cn/reg?refer=yunqi_ecshop" target="_blank" class="btn btn-yellow">免费注册云起账号</a>
  </div>
  <div class="panel-right">
    <h5 class="logo">云起</h5>
    <p>正在激活中</p>
    <iframe src="" frameborder="0" id="CFrame"></iframe>
    <div class="cloud-passw">
      <a target="_blank" href="#">忘记密码？</a>
    </div>
  </div>
</div>
<!--云起激活系统面板-->
<!--遮罩-->
<div class="mask-black" id="CMask"></div>
<!--遮罩-->
<?php endif; ?>

<script type="text/javascript" language="JavaScript">
function get_certificate(){
  var panel = document.getElementById('panelCloudCerti');
  var mask  = document.getElementById('CMask')||null;
  var frame = document.getElementById('CFrame');
  if(panel&&CMask&&frame){
      panel.style.display = 'block';
      mask.style.display = 'block';
      frame.src = '<?php echo $this->_var['iframe_url']; ?>';
    }
}

/*关闭按钮*/
function btnCancel(item){
  var par  = item.offsetParent;
  var mask  = document.getElementById('CMask')||null;
  var frame = document.getElementById('CFrame');
  par.style.display = 'none';
  if(mask){mask.style.display = 'none';}
  frame.src = '';
}

</script>

<?php echo $this->fetch('pagefooter.htm'); ?>