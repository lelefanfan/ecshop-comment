<?php if ($this->_var['full_page']): ?>
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>
<!--<?php echo $this->smarty_insert_scripts(array('files'=>'dialog.js')); ?>-->
<!-- 订单搜索 -->
<link href="../js/calendar/calendar.css" rel="stylesheet" type="text/css" />
<div class="form-div">
  <form action="javascript:searchOrder()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    <?php echo $this->_var['lang']['order_sn']; ?><input name="order_sn" type="text" id="order_sn" size="15">
    <?php echo htmlspecialchars($this->_var['lang']['consignee']); ?><input name="consignee" type="text" id="consignee" size="15">
    <?php echo $this->_var['lang']['all_status']; ?>
    <select name="status" id="status">
      <option value="-1"><?php echo $this->_var['lang']['select_please']; ?></option>
      <?php echo $this->html_options(array('options'=>$this->_var['status_list'])); ?>
    </select>
    <input type="submit" value="<?php echo $this->_var['lang']['button_search']; ?>" class="button" />
    <span class="btn-search cl-link" style="padding-left: 20px" onclick="detailSearch()"><?php echo $this->_var['lang']['advanced_search']; ?></span>
    <!--ERP link-->

      <?php echo $this->_var['erp_icon_html']; ?>
      <!--<a href="" class="btn-ERP">进入<i class="cl-red">ERP</i>处理订单</a>
      <a href="" class="btn-ERP">授权绑定<i class="cl-red">ERP</i></a>
      <a href="" class="btn-ERP">了解详情开通<i class="cl-red">ERP</i></a>-->



    <!--ERP link-->
    <!--<a href="order.php?act=list&composite_status=<?php echo $this->_var['os_unconfirmed']; ?>"><?php echo $this->_var['lang']['cs'][$this->_var['os_unconfirmed']]; ?></a>-->
    <!--<a href="order.php?act=list&composite_status=<?php echo $this->_var['cs_await_pay']; ?>"><?php echo $this->_var['lang']['cs'][$this->_var['cs_await_pay']]; ?></a>-->
    <!--<a href="order.php?act=list&composite_status=<?php echo $this->_var['cs_await_ship']; ?>"><?php echo $this->_var['lang']['cs'][$this->_var['cs_await_ship']]; ?></a>-->

  </form>
</div>

<!-- 订单列表 -->
<form method="post" action="order.php?act=operate" name="listForm" onsubmit="return check()">
  <div class="list-div" id="listDiv">
<?php endif; ?>

<table cellpadding="3" cellspacing="1">
  <tr>
    <th>
      <input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" <?php if ($this->_var['is_bind_erp']): ?>disabled<?php endif; ?>/>
      <a href="javascript:listTable.sort('order_sn', 'DESC'); "><?php echo $this->_var['lang']['order_sn']; ?></a><?php echo $this->_var['sort_order_sn']; ?>
    </th>
    <th><a href="javascript:listTable.sort('add_time', 'DESC'); "><?php echo $this->_var['lang']['order_time']; ?></a><?php echo $this->_var['sort_order_time']; ?></th>
    <th><a href="javascript:listTable.sort('consignee', 'DESC'); "><?php echo $this->_var['lang']['consignee']; ?></a><?php echo $this->_var['sort_consignee']; ?></th>
    <th><a href="javascript:listTable.sort('total_fee', 'DESC'); "><?php echo $this->_var['lang']['total_fee']; ?></a><?php echo $this->_var['sort_total_fee']; ?></th>
    <th><a href="javascript:listTable.sort('order_amount', 'DESC'); "><?php echo $this->_var['lang']['order_amount']; ?></a><?php echo $this->_var['sort_order_amount']; ?></th>
    <th><?php echo $this->_var['lang']['all_status']; ?></th>
    <th><?php echo $this->_var['lang']['handler']; ?></th>
  <tr>
  <?php $_from = $this->_var['order_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('okey', 'order');if (count($_from)):
    foreach ($_from AS $this->_var['okey'] => $this->_var['order']):
?>
  <tr>
    <td valign="top" nowrap="nowrap"><input type="checkbox" name="checkboxes" value="<?php echo $this->_var['order']['order_sn']; ?>" <?php if ($this->_var['is_bind_erp']): ?>disabled<?php endif; ?> /><a href="order.php?act=info&order_id=<?php echo $this->_var['order']['order_id']; ?>" id="order_<?php echo $this->_var['okey']; ?>"><?php echo $this->_var['order']['order_sn']; ?><?php if ($this->_var['order']['extension_code'] == "group_buy"): ?><br /><div align="center"><?php echo $this->_var['lang']['group_buy']; ?></div><?php elseif ($this->_var['order']['extension_code'] == "exchange_goods"): ?><br /><div align="center"><?php echo $this->_var['lang']['exchange_goods']; ?></div><?php endif; ?></a></td>
    <td><?php echo htmlspecialchars($this->_var['order']['buyer']); ?><br /><?php echo $this->_var['order']['short_order_time']; ?></td>
    <td align="left" valign="top"><a href="mailto:<?php echo $this->_var['order']['email']; ?>"> <?php echo htmlspecialchars($this->_var['order']['consignee']); ?></a><?php if ($this->_var['order']['tel']): ?> [TEL: <?php echo htmlspecialchars($this->_var['order']['tel']); ?>]<?php endif; ?> <br /><?php echo htmlspecialchars($this->_var['order']['address']); ?></td>
    <td align="right" valign="top" nowrap="nowrap"><?php echo $this->_var['order']['formated_total_fee']; ?></td>
    <td align="right" valign="top" nowrap="nowrap"><?php echo $this->_var['order']['formated_order_amount']; ?></td>
    <td align="center" valign="top" nowrap="nowrap"><?php echo $this->_var['lang']['os'][$this->_var['order']['order_status']]; ?>,<?php echo $this->_var['lang']['ps'][$this->_var['order']['pay_status']]; ?>,<?php echo $this->_var['lang']['ss'][$this->_var['order']['shipping_status']]; ?></td>
    <td align="center" valign="top"  nowrap="nowrap">
     <a href="order.php?act=info&order_id=<?php echo $this->_var['order']['order_id']; ?>"><?php echo $this->_var['lang']['detail']; ?></a>
     <?php if ($this->_var['order']['can_remove']): ?>
     <br /><?php if (! $this->_var['is_bind_erp']): ?><a href="javascript:;" onclick="listTable.remove(<?php echo $this->_var['order']['order_id']; ?>, remove_confirm, 'remove_order')"><?php echo $this->_var['lang']['remove']; ?></a><?php endif; ?>
     <?php endif; ?>
     <?php if ($this->_var['order']['callback_status'] == 'false' && ( $this->_var['is_bind_taoda'] || $this->_var['is_bind_erp'] )): ?>
     <a href="javascript:;" onclick="listTable.retry(<?php echo $this->_var['order']['order_sn']; ?>)"><?php echo $this->_var['lang']['retry']; ?></a>
     <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>


<!-- 分页 -->
<table id="page-table" cellspacing="0">
  <tr>
    <td align="right" nowrap="true">
    <?php echo $this->fetch('page.htm'); ?>
    </td>
  </tr>
</table>

<!--两个温馨提示-->
<!-- start upload template -->
<?php if ($this->_var['panel_flag'] == 1): ?>
<div class="panel-hint panel-hint-ERP" id="erpPanel" style="display:<?php echo $this->_var['panel_display']; ?>">
  <div class="panel-hd">
    <span class="hd-icon"></span>
    <span class="hd-title"><?php echo $this->_var['lang']['erp_reminder']; ?></span>
  </div>
  <div class="panel-bd">
      <h5><?php echo $this->_var['erp_str']['0']; ?></h5>
  </div>
  <div class="panel-ft">
    <a href="<?php echo $this->_var['erp_url']; ?>" class="btn-act btn-confirm" data-role="true" target="_blank" onclick="goBind(this)">
        <?php echo $this->_var['erp_str']['1']; ?>
    </a>
    <button class="btn-act btn-cancel" onclick="btnCancel(this);"><?php echo $this->_var['lang']['erp_no_longer_prompt']; ?></button>
  </div>
</div>
<!-- end upload template -->
<!--两个温馨提示-->

<!--遮罩-->
    <div class="mask-black" id="Mask" style="display:<?php echo $this->_var['panel_display']; ?>"></div>
<!--遮罩-->
 <?php endif; ?>
<?php if ($this->_var['full_page']): ?>
  </div>
  <div>
    <input name="confirm" type="submit" id="btnSubmit" value="<?php echo $this->_var['lang']['op_confirm']; ?>" class="button" disabled="true" onclick="this.form.target = '_self'" />
    <input name="invalid" type="submit" id="btnSubmit1" value="<?php echo $this->_var['lang']['op_invalid']; ?>" class="button" disabled="true" onclick="this.form.target = '_self'" />
    <input name="cancel" type="submit" id="btnSubmit2" value="<?php echo $this->_var['lang']['op_cancel']; ?>" class="button" disabled="true" onclick="this.form.target = '_self'" />
    <input name="remove" type="submit" id="btnSubmit3" value="<?php echo $this->_var['lang']['remove']; ?>" class="button" disabled="true" onclick="this.form.target = '_self'" />
    <input name="print" type="submit" id="btnSubmit4" value="<?php echo $this->_var['lang']['print_order']; ?>" class="button" disabled="true" onclick="this.form.target = '_blank'" />
    <input name="batch" type="hidden" value="1" />
    <input name="order_id" type="hidden" value="" />
  </div>
</form>

<div class="main-div">
  <form action="order.php?act=list" method="post" enctype="multipart/form-data" name="searchForm">
    <div class="panel-hint panel-order-query" id="panelQuery" style="display:none">
      <div class="panel-hd">
        <span class="hd-title">ECshop管理中心 - 订单查询</span>
        <span class="hd-cross" onclick="btnClose(this);"></span>
      </div>
      <div class="panel-bd">
        <table cellspacing="1" cellpadding="3" width="100%">
          <tr>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_order_sn']; ?></strong></div></td>
            <td colspan="3"><input name="order_sn" type="text" id="order_sn" size="30"></td>
          </tr>
          <tr>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_email']; ?></strong></div></td>
            <td colspan="3"><input name="email" type="text" id="email" size="30"></td>
          </tr>
          <tr>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_user_name']; ?></strong></div></td>
            <td><input name="user_name" type="text" id="user_name" size="20"></td>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_consignee']; ?></strong></div></td>
            <td><input name="consignee" type="text" id="consignee" size="20"></td>
          </tr>
          <tr>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_address']; ?></strong></div></td>
            <td><input name="address" type="text" id="address" size="20"></td>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_zipcode']; ?></strong></div></td>
            <td><input name="zipcode" type="text" id="zipcode" size="20"></td>
          </tr>
          <tr>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_tel']; ?></strong></div></td>
            <td><input name="tel" type="text" id="tel" size="20"></td>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_mobile']; ?></strong></div></td>
            <td><input name="mobile" type="text" id="mobile" size="20"></td>
          </tr>
          <tr>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_area']; ?></strong></div></td>
            <td colspan="3"><select name="country" id="selCountries" onchange="region.changed(this, 1, 'selProvinces')">
              <option value="0"><?php echo $this->_var['lang']['select_please']; ?></option>
              <?php $_from = $this->_var['country_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'country');if (count($_from)):
    foreach ($_from AS $this->_var['country']):
?>
              <option value="<?php echo $this->_var['country']['region_id']; ?>"><?php echo $this->_var['country']['region_name']; ?></option>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </select>
              <select name="province" id="selProvinces" onchange="region.changed(this, 2, 'selCities')">
                <option value="0"><?php echo $this->_var['lang']['select_please']; ?></option>
              </select>
              <select name="city" id="selCities" onchange="region.changed(this, 3, 'selDistricts')">
                <option value="0"><?php echo $this->_var['lang']['select_please']; ?></option>
              </select>
              <select name="district" id="selDistricts">
                <option value="0"><?php echo $this->_var['lang']['select_please']; ?></option>
              </select></td>
          </tr>
          <tr>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_shipping']; ?></strong></div></td>
            <td><select name="shipping_id" id="select4">
              <option value="0"><?php echo $this->_var['lang']['select_please']; ?></option>
              <?php $_from = $this->_var['shipping_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'shipping');if (count($_from)):
    foreach ($_from AS $this->_var['shipping']):
?>
              <option value="<?php echo $this->_var['shipping']['shipping_id']; ?>"><?php echo $this->_var['shipping']['shipping_name']; ?></option>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </select></td>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_payment']; ?></strong></div></td>
            <td><select name="pay_id" id="select5">
              <option value="0"><?php echo $this->_var['lang']['select_please']; ?></option>
              <?php $_from = $this->_var['pay_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'pay');if (count($_from)):
    foreach ($_from AS $this->_var['pay']):
?>
              <option value="<?php echo $this->_var['pay']['pay_id']; ?>"><?php echo $this->_var['pay']['pay_name']; ?></option>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </select></td>
          </tr>
          <tr>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_time']; ?></strong></div></td>
            <td>
              <input type="text" name="start_time" maxlength="60" size="20" readonly="readonly" id="start_time_id" />
              <input name="start_time_btn" type="button" id="start_time_btn" onclick="return showCalendar('start_time_id', '%Y-%m-%d %H:%M', '24', false, 'start_time_btn');" value="<?php echo $this->_var['lang']['btn_select']; ?>" class="button"/>
              ~
              <input type="text" name="end_time" maxlength="60" size="20" readonly="readonly" id="end_time_id" />
              <input name="end_time_btn" type="button" id="end_time_btn" onclick="return showCalendar('end_time_id', '%Y-%m-%d %H:%M', '24', false, 'end_time_btn');" value="<?php echo $this->_var['lang']['btn_select']; ?>" class="button"/>
            </td>
          </tr>
          <tr>
            <td><div align="right"><strong><?php echo $this->_var['lang']['label_order_status']; ?></strong></div></td>
            <td colspan="3">
              <select name="order_status" id="select9">
                <option value="-1"><?php echo $this->_var['lang']['select_please']; ?></option>
                <?php echo $this->html_options(array('options'=>$this->_var['os_list'],'selected'=>'-1')); ?>
              </select>
              <strong><?php echo $this->_var['lang']['label_pay_status']; ?></strong>        <select name="pay_status" id="select11">
              <option value="-1"><?php echo $this->_var['lang']['select_please']; ?></option>
              <?php echo $this->html_options(array('options'=>$this->_var['ps_list'],'selected'=>'-1')); ?>
            </select>
              <strong><?php echo $this->_var['lang']['label_shipping_status']; ?></strong>        <select name="shipping_status" id="select10">
              <option value="-1"><?php echo $this->_var['lang']['select_please']; ?></option>
              <?php echo $this->html_options(array('options'=>$this->_var['ss_list'],'selected'=>'-1')); ?>
            </select></td>
          </tr>
        </table>
      </div>
      <div class="panel-ft">
        <tr>
          <td colspan="4"><div align="center">
          </div></td>
        </tr>
        <!--<button class="btn-act btn-confirm" data-role="true" onclick="goBind(this)">去绑定</button>-->
        <input class="btn-act btn-confirm" name="query" type="submit" class="button" id="query" value="<?php echo $this->_var['lang']['button_search']; ?>" />
        <input class="btn-act btn-cancel" name="reset" type="reset" class='button' value='<?php echo $this->_var['lang']['button_reset']; ?>' />
        <!--<button class="btn-act btn-cancel" onclick="btnCancel(this);">知道了，不再提示</button>-->
      </div>
    </div>
  </form>
</div>
<script language="JavaScript">
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

    /**
     * 搜索订单
     */
    function searchOrder()
    {
        listTable.filter['order_sn'] = Utils.trim(document.forms['searchForm'].elements['order_sn'].value);
        listTable.filter['consignee'] = Utils.trim(document.forms['searchForm'].elements['consignee'].value);
        listTable.filter['composite_status'] = document.forms['searchForm'].elements['status'].value;
        listTable.filter['page'] = 1;
        listTable.loadList();
    }

    function check()
    {
      var snArray = new Array();
      var eles = document.forms['listForm'].elements;
      for (var i=0; i<eles.length; i++)
      {
        if (eles[i].tagName == 'INPUT' && eles[i].type == 'checkbox' && eles[i].checked && eles[i].value != 'on')
        {
          snArray.push(eles[i].value);
        }
      }
      if (snArray.length == 0)
      {
        return false;
      }
      else
      {
        eles['order_id'].value = snArray.toString();
        return true;
      }
    }
    /**
     * 显示订单商品及缩图
     */
    var show_goods_layer = 'order_goods_layer';
    var goods_hash_table = new Object;
    var timer = new Object;

    /**
     * 绑定订单号事件
     *
     * @return void
     */
    function bind_order_event()
    {
        var order_seq = 0;
        while(true)
        {
            var order_sn = Utils.$('order_'+order_seq);
            if (order_sn)
            {
                order_sn.onmouseover = function(e)
                {
                    try
                    {
                        window.clearTimeout(timer);
                    }
                    catch(e)
                    {
                    }
                    var order_id = Utils.request(this.href, 'order_id');
                    show_order_goods(e, order_id, show_goods_layer);
                }
                order_sn.onmouseout = function(e)
                {
                    hide_order_goods(show_goods_layer)
                }
                order_seq++;
            }
            else
            {
                break;
            }
        }
    }
    listTable.listCallback = function(result, txt) 
    {
        if (result.error > 0) 
        {
            alert(result.message);
        }
        else 
        {
            try 
            {
                document.getElementById('listDiv').innerHTML = result.content;
                bind_order_event();
                if (typeof result.filter == "object") 
                {
                    listTable.filter = result.filter;
                }
                listTable.pageCount = result.page_count;
            }
            catch(e)
            {
                alert(e.message);
            }
        }
    }
    /**
     * 浏览器兼容式绑定Onload事件
     *
     */
    if (Browser.isIE)
    {
        window.attachEvent("onload", bind_order_event);
    }
    else
    {
        window.addEventListener("load", bind_order_event, false);
    }

    /**
     * 建立订单商品显示层
     *
     * @return void
     */
    function create_goods_layer(id)
    {
        if (!Utils.$(id))
        {
            var n_div = document.createElement('DIV');
            n_div.id = id;
            n_div.className = 'order-goods';
            document.body.appendChild(n_div);
            Utils.$(id).onmouseover = function()
            {
                window.clearTimeout(window.timer);
            }
            Utils.$(id).onmouseout = function()
            {
                hide_order_goods(id);
            }
        }
        else
        {
            Utils.$(id).style.display = '';
        }
    }

    /**
     * 显示订单商品数据
     *
     * @return void
     */
    function show_order_goods(e, order_id, layer_id)
    {
        create_goods_layer(layer_id);
        $layer_id = Utils.$(layer_id);
        $layer_id.style.top = (Utils.y(e) + 12) + 'px';
        $layer_id.style.left = (Utils.x(e) + 12) + 'px';
        if (typeof(goods_hash_table[order_id]) == 'object')
        {
            response_goods_info(goods_hash_table[order_id]);
        }
        else
        {
            $layer_id.innerHTML = loading;
            Ajax.call('order.php?is_ajax=1&act=get_goods_info&order_id='+order_id, '', response_goods_info , 'POST', 'JSON');
        }
    }

    /**
     * 隐藏订单商品
     *
     * @return void
     */
    function hide_order_goods(layer_id)
    {
        $layer_id = Utils.$(layer_id);
        window.timer = window.setTimeout('$layer_id.style.display = "none"', 500);
    }

    /**
     * 处理订单商品的Callback
     *
     * @return void
     */
    function response_goods_info(result)
    {
        if (result.error > 0)
        {
            alert(result.message);
            hide_order_goods(show_goods_layer);
            return;
        }
        if (typeof(goods_hash_table[result.content[0].order_id]) == 'undefined')
        {
            goods_hash_table[result.content[0].order_id] = result;
        }
        Utils.$(show_goods_layer).innerHTML = result.content[0].str;
    }
      /*授权绑定面板*/
      function showPanel(){

          var panel = document.getElementById('erpPanel');
          var mask  = document.getElementById('Mask')||null;

          panel.style.display = 'block';
          mask.style.display = 'block';

          return;

        var panel = document.getElementById('erpPanel');
        var innText = panel.children[1];
        var confBtn = panel.children[2].firstElementChild;
        if(item.classList[0] == 'btn-ERP'){
          innText.innerHTML = '<h5>已有99%的用户使用ERP处理订单</h5>';
          confBtn.innerHTML = '去开通';
          confBtn.attributes[1].value = 'false';
        }else{
          innText.innerHTML = '<h5>您已开通ERP，请授权绑定</h5>';
          confBtn.innerHTML = '去绑定';
          confBtn.attributes[1].value = 'true';
        }
        panel.style.display = 'block';
      }


      /*去绑定*/
      function goBind(item){
        var role = item.attributes[1].value;
        var cloud = document.getElementById('panelCloud');
        var mask  = document.getElementById('Mask');
        if(role == "true"){
          btnCancel(item);
          mask.style.display = 'block';
          cloud.style.display = 'block';
        }
      }

      /*关闭按钮*/
      function btnCancel(item){
        var par  = item.offsetParent;
        var mask  = document.getElementById('Mask')||null;
        par.style.display = 'none';
        if(mask){mask.style.display = 'none';}
          Ajax.call('order.php?is_ajax=1&act=cancelErpPanel','',null,'GET','JSON');


      }
      /*重新获取云起安装产品列表*/
      function getSnList(){
        Ajax.call('order.php?is_ajax=1&act=getSnList','',null,'GET');
      }

      /*ERP处理订单*/

</script>

<?php echo $this->smarty_insert_scripts(array('files'=>'../js/transport.js,../js/region.js')); ?>
<script language="JavaScript">
  region.isAdmin = true;
  onload = function() {
    // 开始检查订单
    startCheckOrder();
  }
  /*关闭按钮*/
  function btnClose(item){
    var par  = item.parentElement.parentElement;
    par.style.display = 'none';
  }

  /*高级搜索*/
  function detailSearch(){
    var search = document.getElementById('panelQuery');
    search.style.display = 'block';
  }
</script>

<!--<?php echo $this->smarty_insert_scripts(array('files'=>'../js/transport.js,../js/region.js')); ?>-->
<!---->
<!---->

<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>