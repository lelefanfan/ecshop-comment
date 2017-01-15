<?php echo $this->fetch('pageheader.htm'); ?>
<iframe  id="c_iframe"  width="100%" height="600" src="<?php echo $this->_var['iframe_url']; ?>" frameborder="0" scrolling="yes"></iframe>
<script>
	document.getElementById("c_iframe").height = window.screen.availHeight - 120;
</script>

