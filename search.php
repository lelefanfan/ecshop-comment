<?php

/**
 * ECSHOP 搜索程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: search.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

if (!function_exists("htmlspecialchars_decode"))
{
    // 将特定html实体解码
    function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT)
    {
        return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
    }
}

if (empty($_GET['encode'])) // 无编码
{
    $string = array_merge($_GET, $_POST);
    if (get_magic_quotes_gpc()) // magic_quotes_gpc 开启
    {
        require(dirname(__FILE__) . '/includes/lib_base.php');
        //require(dirname(__FILE__) . '/includes/lib_common.php');
        $string = stripslashes_deep($string); // 递归方式的对变量中的特殊字符去除转义
    }
    $string['search_encode_time'] = time(); // 搜索编码时间
    $string = str_replace('+', '%2b', base64_encode(serialize($string))); // 编码

    header("Location: search.php?encode=$string\n"); // 跳转

    exit;
}
else
{
    $string = base64_decode(trim($_GET['encode'])); // 解码
    if ($string !== false) // 存在数据
    {
        $string = unserialize($string); // 反序列化
        if ($string !== false) // 存在数据
        {
            /* 用户在重定向的情况下当作一次访问 */
            if (!empty($string['search_encode_time']))
            {
                if (time() > $string['search_encode_time'] + 2)
                {
                    define('INGORE_VISIT_STATS', true);
                }
            }
            else
            {
                define('INGORE_VISIT_STATS', true);
            }
        }
        else // 不存在数据
        {
            $string = array();
        }
    }
    else // 不存在数据
    {
        $string = array();
    }
}

require(dirname(__FILE__) . '/includes/init.php'); // 载入初始化文件

$_REQUEST = array_merge($_REQUEST, addslashes_deep($string)); // 合并请求变量



$_REQUEST['act'] = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : ''; // 动作
/*------------------------------------------------------ */
//-- 高级搜索
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'advanced_search')
{
    $goods_type = !empty($_REQUEST['goods_type']) ? intval($_REQUEST['goods_type']) : 0; // 商品类型id
    $attributes = get_seachable_attributes($goods_type); // 商品类型及属性组合的集合
    $smarty->assign('goods_type_selected', $goods_type); // 商品类型id
    $smarty->assign('goods_type_list',     $attributes['cate']); // 商品类型名称
    $smarty->assign('goods_attributes',    $attributes['attr']); // 商品属性集合

    assign_template(); // 分派相关变量到模板
    assign_dynamic('search'); // 设置搜索页面动态内容
    $position = assign_ur_here(0, $_LANG['advanced_search']);
    $smarty->assign('page_title', $position['title']);    // 页面标题
    $smarty->assign('ur_here',    $position['ur_here']);  // 当前位置

    $smarty->assign('categories', get_categories_tree()); // 分类树
    $smarty->assign('helps',      get_shop_help());       // 网店帮助
    $smarty->assign('top_goods',  get_top10());           // 销售排行
    $smarty->assign('promotion_info', get_promotion_info()); // 促销信息
    $smarty->assign('cat_list',   cat_list(0, 0, true, 2, false)); // 获取全部分类
    $smarty->assign('brand_list', get_brand_list());
    $smarty->assign('action',     'form');

    $smarty->assign('use_storage', $_CFG['use_storage']);

    $smarty->display('search.dwt');

    exit;
}
/*------------------------------------------------------ */
//-- 搜索结果
/*------------------------------------------------------ */
else
{
     // 关键字
    $_REQUEST['keywords']   = !empty($_REQUEST['keywords'])   ? htmlspecialchars(trim($_REQUEST['keywords']))     : '';
    // 品牌
    $_REQUEST['brand']      = !empty($_REQUEST['brand'])      ? intval($_REQUEST['brand'])      : 0; 
    // 分类
    $_REQUEST['category']   = !empty($_REQUEST['category'])   ? intval($_REQUEST['category'])   : 0;
    // 最低价格
    $_REQUEST['min_price']  = !empty($_REQUEST['min_price'])  ? intval($_REQUEST['min_price'])  : 0; 
    // 最高价格
    $_REQUEST['max_price']  = !empty($_REQUEST['max_price'])  ? intval($_REQUEST['max_price'])  : 0;
    // 商品类型
    $_REQUEST['goods_type'] = !empty($_REQUEST['goods_type']) ? intval($_REQUEST['goods_type']) : 0; 
    // 搜索简介，值为 1或0
    $_REQUEST['sc_ds']      = !empty($_REQUEST['sc_ds']) ? intval($_REQUEST['sc_ds']) : 0;
    // 隐藏已脱销的商品，值为 1或0
    $_REQUEST['outstock']   = !empty($_REQUEST['outstock']) ? 1 : 0;

    $action = '';
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'form') // 高级搜索
    {
        /* 要显示高级搜索栏 */
        $adv_value['keywords']  = htmlspecialchars(stripcslashes($_REQUEST['keywords']));
        $adv_value['brand']     = $_REQUEST['brand'];
        $adv_value['min_price'] = $_REQUEST['min_price'];
        $adv_value['max_price'] = $_REQUEST['max_price'];
        $adv_value['category']  = $_REQUEST['category'];

        $attributes = get_seachable_attributes($_REQUEST['goods_type']);

        /* 将提交数据重新赋值 */
        foreach ($attributes['attr'] AS $key => $val)
        {
            if (!empty($_REQUEST['attr'][$val['id']]))
            {
                if ($val['type'] == 2)
                {
                    $attributes['attr'][$key]['value']['from'] = !empty($_REQUEST['attr'][$val['id']]['from']) ? htmlspecialchars(stripcslashes(trim($_REQUEST['attr'][$val['id']]['from']))) : '';
                    $attributes['attr'][$key]['value']['to']   = !empty($_REQUEST['attr'][$val['id']]['to'])   ? htmlspecialchars(stripcslashes(trim($_REQUEST['attr'][$val['id']]['to'])))   : '';
                }
                else
                {
                    $attributes['attr'][$key]['value'] = !empty($_REQUEST['attr'][$val['id']]) ? htmlspecialchars(stripcslashes(trim($_REQUEST['attr'][$val['id']]))) : '';
                }
            }
        }
        if ($_REQUEST['sc_ds'])
        {
            $smarty->assign('scck',            'checked');
        }
        $smarty->assign('adv_val',             $adv_value);
        $smarty->assign('goods_type_list',     $attributes['cate']);
        $smarty->assign('goods_attributes',    $attributes['attr']);
        $smarty->assign('goods_type_selected', $_REQUEST['goods_type']);
        $smarty->assign('cat_list',            cat_list(0, $adv_value['category'], true, 2, false));
        $smarty->assign('brand_list',          get_brand_list());
        $smarty->assign('action',              'form');
        $smarty->assign('use_storage',          $_CFG['use_storage']);

        $action = 'form';
    }


    /* 初始化搜索条件 */
    $keywords  = '';
    $tag_where = '';
    if (!empty($_REQUEST['keywords'])) // 存在搜索关键词
    {
        $arr = array(); // 存放关键词转换的条件
        if (stristr($_REQUEST['keywords'], ' AND ') !== false) // 存在 AND
        {
            /* 检查关键字中是否有AND，如果存在就是并 */
            $arr        = explode('AND', $_REQUEST['keywords']);
            $operator   = " AND ";
        }
        elseif (stristr($_REQUEST['keywords'], ' OR ') !== false) // 存在 OR
        {
            /* 检查关键字中是否有OR，如果存在就是或 */
            $arr        = explode('OR', $_REQUEST['keywords']);
            $operator   = " OR ";
        }
        elseif (stristr($_REQUEST['keywords'], ' + ') !== false) // 存在 +
        {
            /* 检查关键字中是否有加号，如果存在就是或 */
            $arr        = explode('+', $_REQUEST['keywords']);
            $operator   = " OR ";
        }
        else
        {
            /* 检查关键字中是否有空格，如果存在就是并 */
            $arr        = explode(' ', $_REQUEST['keywords']); // 存在空格 ' '
            $operator   = " AND ";
        }

        $keywords = 'AND (';
        $goods_ids = array(); // 商品id集合
        // 循环关键词条件，组合查询字符串条件
        foreach ($arr AS $key => $val)
        {
            // 判断是否添加操作符（AND 或 OR）
            if ($key > 0 && $key < count($arr) && count($arr) > 1)
            {
                $keywords .= $operator;
            }
            $val        = mysql_like_quote(trim($val)); // 对 MYSQL LIKE 的内容进行转义
            $sc_dsad    = $_REQUEST['sc_ds'] ? " OR goods_desc LIKE '%$val%'" : ''; // 搜索简介
            $keywords  .= "(goods_name LIKE '%$val%' OR goods_sn LIKE '%$val%' OR keywords LIKE '%$val%' $sc_dsad)"; // LIKE 商品名称、货号、商品关键字

            /* 根据关键词到 tag表 获取所有相关商品id */
            $sql = 'SELECT DISTINCT goods_id FROM ' . $ecs->table('tag') . " WHERE tag_words LIKE '%$val%' ";
            $res = $db->query($sql);
            while ($row = $db->FetchRow($res))
            {
                // 相关商品id
                $goods_ids[] = $row['goods_id'];
            }

            $db->autoReplace($ecs->table('keywords'), array('date' => local_date('Y-m-d'),
                'searchengine' => 'ecshop', 'keyword' => addslashes(str_replace('%', '', $val)), 'count' => 1), array('count' => 1));
        }
        $keywords .= ')';

        $goods_ids = array_unique($goods_ids); // 去重复商品id
        $tag_where = implode(',', $goods_ids); // 将商品id集合转为字符串
        if (!empty($tag_where))  //存在 $tag_where 条件
        {
            $tag_where = 'OR g.goods_id ' . db_create_in($tag_where);
        }
    }

    // 栏目id
    $category   = !empty($_REQUEST['category']) ? intval($_REQUEST['category'])        : 0;
    // 关联子栏目id
    $categories = ($category > 0)               ? ' AND ' . get_children($category)    : '';
    // 品牌id
    $brand      = $_REQUEST['brand']            ? " AND brand_id = '$_REQUEST[brand]'" : '';
    // 商品数量必须大于0
    $outstock   = !empty($_REQUEST['outstock']) ? " AND g.goods_number > 0 "           : '';
    // 最小价格
    $min_price  = $_REQUEST['min_price'] != 0                               ? " AND g.shop_price >= '$_REQUEST[min_price]'" : '';
    // 最大价格
    $max_price  = $_REQUEST['max_price'] != 0 || $_REQUEST['min_price'] < 0 ? " AND g.shop_price <= '$_REQUEST[max_price]'" : '';

    /* 排序、显示方式以及类型 */
    // 这里有3种显示方式（0：list；1：grid；否则就是text）
    $default_display_type = $_CFG['show_order_type'] == '0' ? 'list' : ($_CFG['show_order_type'] == '1' ? 'grid' : 'text');
    // 默认排序规则，升序或降序
    $default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
    // 默认3种排序类型（0：goods_id；1：shop_price；否则就是last_update）
    $default_sort_order_type   = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'shop_price' : 'last_update');
    // 类型
    $sort = (isset($_REQUEST['sort'])  && in_array(trim(strtolower($_REQUEST['sort'])), array('goods_id', 'shop_price', 'last_update'))) ? trim($_REQUEST['sort'])  : $default_sort_order_type;
    // 顺序
    $order = (isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC'))) ? trim($_REQUEST['order']) : $default_sort_order_method;
    // 显示方式
    $display  = (isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'grid', 'text'))) ? trim($_REQUEST['display'])  : (isset($_SESSION['display_search']) ? $_SESSION['display_search'] : $default_display_type);

    $_SESSION['display_search'] = $display;
    // 页码
    $page       = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
    // 每页条数
    $size       = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;

    $intromode = '';    //方式，用于决定搜索结果页标题图片

    if (!empty($_REQUEST['intro']))
    {
        // 关联 精品、新品、热销
        switch ($_REQUEST['intro'])
        {
            case 'best':
                $intro   = ' AND g.is_best = 1'; // 精品
                $intromode = 'best';
                $ur_here = $_LANG['best_goods'];
                break;
            case 'new':
                $intro   = ' AND g.is_new = 1'; // 新品
                $intromode ='new';
                $ur_here = $_LANG['new_goods'];
                break;
            case 'hot':
                $intro   = ' AND g.is_hot = 1'; // 热销
                $intromode = 'hot';
                $ur_here = $_LANG['hot_goods'];
                break;
            case 'promotion': // 促销
                $time    = gmtime();
                $intro   = " AND g.promote_price > 0 AND g.promote_start_date <= '$time' AND g.promote_end_date >= '$time'";
                $intromode = 'promotion';
                $ur_here = $_LANG['promotion_goods'];
                break;
            default:
                $intro   = '';
        }
    }
    else
    {
        $intro = '';
    }

    if (empty($ur_here))
    {
        $ur_here = $_LANG['search_goods'];
    }

    /*------------------------------------------------------ */
    //-- 属性检索
    /*------------------------------------------------------ */
    $attr_in  = '';
    $attr_num = 0; // 数量
    $attr_url = '';
    $attr_arg = array();

    if (!empty($_REQUEST['attr'])) // 存在属性
    {
        $sql = "SELECT goods_id, COUNT(*) AS num FROM " . $ecs->table("goods_attr") . " WHERE 0 ";
        // 循环请求属性
        foreach ($_REQUEST['attr'] AS $key => $val)
        {
            // $val不为空值 且 $key为数值型
            if (is_not_null($val) && is_numeric($key))
            {
                $attr_num++;
                $sql .= " OR (1 ";

                if (is_array($val))
                {
                    $sql .= " AND attr_id = '$key'";

                    if (!empty($val['from']))
                    {
                        $sql .= is_numeric($val['from']) ? " AND attr_value >= " . floatval($val['from'])  : " AND attr_value >= '$val[from]'";
                        $attr_arg["attr[$key][from]"] = $val['from'];
                        $attr_url .= "&amp;attr[$key][from]=$val[from]";
                    }

                    if (!empty($val['to']))
                    {
                        $sql .= is_numeric($val['to']) ? " AND attr_value <= " . floatval($val['to']) : " AND attr_value <= '$val[to]'";
                        $attr_arg["attr[$key][to]"] = $val['to'];
                        $attr_url .= "&amp;attr[$key][to]=$val[to]";
                    }
                }
                else
                {
                    /* 处理选购中心过来的链接 */
                    $sql .= isset($_REQUEST['pickout']) ? " AND attr_id = '$key' AND attr_value = '" . $val . "' " : " AND attr_id = '$key' AND attr_value LIKE '%" . mysql_like_quote($val) . "%' ";
                    $attr_url .= "&amp;attr[$key]=$val";
                    $attr_arg["attr[$key]"] = $val;
                }

                $sql .= ')';
            }
        }

        /* 如果检索条件都是无效的，就不用检索 */
        if ($attr_num > 0)
        {
            $sql .= " GROUP BY goods_id HAVING num = '$attr_num'";

            $row = $db->getCol($sql);
            if (count($row))
            {
                $attr_in = " AND " . db_create_in($row, 'g.goods_id');
            }
            else
            {
                $attr_in = " AND 0 ";
            }
        }
    }
    elseif (isset($_REQUEST['pickout']))
    {
        /* 从选购中心进入的链接 */
        $sql = "SELECT DISTINCT(goods_id) FROM " . $ecs->table('goods_attr');
        $col = $db->getCol($sql);
        //如果商店没有设置商品属性,那么此检索条件是无效的
        if (!empty($col))
        {
            $attr_in = " AND " . db_create_in($col, 'g.goods_id');
        }
    }

    /* 获得符合条件的商品总数 */
    $sql   = "SELECT COUNT(*) FROM " .$ecs->table('goods'). " AS g ".
        "WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 $attr_in ".
        "AND (( 1 " . $categories . $keywords . $brand . $min_price . $max_price . $intro . $outstock ." ) ".$tag_where." )";
    $count = $db->getOne($sql);

    $max_page = ($count> 0) ? ceil($count / $size) : 1; // 根据商品总数获取最大页面数
    if ($page > $max_page)
    {
        $page = $max_page;
    }

    /* 查询商品 */
    $sql = "SELECT g.goods_id, g.goods_name, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ".
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
                "g.promote_price, g.promote_start_date, g.promote_end_date, g.goods_thumb, g.goods_img, g.goods_brief, g.goods_type ".
            "FROM " .$ecs->table('goods'). " AS g ".
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
                    "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            "WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 $attr_in ".
                "AND (( 1 " . $categories . $keywords . $brand . $min_price . $max_price . $intro . $outstock . " ) ".$tag_where." ) " .
            "ORDER BY $sort $order";
    $res = $db->SelectLimit($sql, $size, ($page - 1) * $size);

    $arr = array();
    while ($row = $db->FetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }

        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($promote_price != 0)
        {
            $watermark_img = "watermark_promote_small";
        }
        elseif ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new_small";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best_small";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '')
        {
            $arr[$row['goods_id']]['watermark_img'] =  $watermark_img;
        }

        $arr[$row['goods_id']]['goods_id']      = $row['goods_id'];
        if($display == 'grid')
        {
            $arr[$row['goods_id']]['goods_name']    = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        }
        else
        {
            $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
        }
        $arr[$row['goods_id']]['type']          = $row['goods_type'];
        $arr[$row['goods_id']]['market_price']  = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
        $arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
        $arr[$row['goods_id']]['goods_brief']   = $row['goods_brief'];
        $arr[$row['goods_id']]['goods_thumb']   = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']     = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url']           = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
    }

    if($display == 'grid')
    {
        if(count($arr) % 2 != 0)
        {
            $arr[] = array();
        }
    }
    $smarty->assign('goods_list', $arr);
    $smarty->assign('category',   $category);
    $smarty->assign('keywords',   htmlspecialchars(stripslashes($_REQUEST['keywords'])));
    $smarty->assign('search_keywords',   stripslashes(htmlspecialchars_decode($_REQUEST['keywords'])));
    $smarty->assign('brand',      $_REQUEST['brand']);
    $smarty->assign('min_price',  $min_price);
    $smarty->assign('max_price',  $max_price);
    $smarty->assign('outstock',  $_REQUEST['outstock']);

    /* 分页 */
    $url_format = "search.php?category=$category&amp;keywords=" . urlencode(stripslashes($_REQUEST['keywords'])) . "&amp;brand=" . $_REQUEST['brand']."&amp;action=".$action."&amp;goods_type=" . $_REQUEST['goods_type'] . "&amp;sc_ds=" . $_REQUEST['sc_ds'];
    if (!empty($intromode))
    {
        $url_format .= "&amp;intro=" . $intromode;
    }
    if (isset($_REQUEST['pickout']))
    {
        $url_format .= '&amp;pickout=1';
    }
    $url_format .= "&amp;min_price=" . $_REQUEST['min_price'] ."&amp;max_price=" . $_REQUEST['max_price'] . "&amp;sort=$sort";

    $url_format .= "$attr_url&amp;order=$order&amp;page=";

    $pager['search'] = array(
        'keywords'   => stripslashes(urlencode($_REQUEST['keywords'])),
        'category'   => $category,
        'brand'      => $_REQUEST['brand'],
        'sort'       => $sort,
        'order'      => $order,
        'min_price'  => $_REQUEST['min_price'],
        'max_price'  => $_REQUEST['max_price'],
        'action'     => $action,
        'intro'      => empty($intromode) ? '' : trim($intromode),
        'goods_type' => $_REQUEST['goods_type'],
        'sc_ds'      => $_REQUEST['sc_ds'],
        'outstock'   => $_REQUEST['outstock']
    );
    $pager['search'] = array_merge($pager['search'], $attr_arg);

    $pager = get_pager('search.php', $pager['search'], $count, $page, $size);
    $pager['display'] = $display;

    $smarty->assign('url_format', $url_format);
    $smarty->assign('pager', $pager);

    assign_template();
    assign_dynamic('search');
    $position = assign_ur_here(0, $ur_here . ($_REQUEST['keywords'] ? '_' . $_REQUEST['keywords'] : ''));
    $smarty->assign('page_title', $position['title']);    // 页面标题
    $smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
    $smarty->assign('intromode',      $intromode);
    $smarty->assign('categories', get_categories_tree()); // 分类树
    $smarty->assign('helps',       get_shop_help());      // 网店帮助
    $smarty->assign('top_goods',  get_top10());           // 销售排行
    $smarty->assign('promotion_info', get_promotion_info());

    $smarty->display('search.dwt');
}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */
/**
 *
 *
 * @access public
 * @param
 *
 * @return void
 */
function is_not_null($value)
{
    if (is_array($value))
    {
        return (!empty($value['from'])) || (!empty($value['to']));
    }
    else
    {
        return !empty($value);
    }
}

/**
 * 获得可以检索的属性
 *
 * @access  public
 * @params  integer $cat_id 商品类型id
 * @return  void
 */
function get_seachable_attributes($cat_id = 0)
{
    // 属性列表
    $attributes = array(
        'cate' => array(),
        'attr' => array()
    );

    /* 获得可用的商品类型 */
    // SELECT t.cat_id, cat_name FROM `ec3`.`ecs_goods_type` AS t, `ec3`.`ecs_attribute` AS a WHERE t.cat_id = a.cat_id AND t.enabled = 1 AND a.attr_index > 0 
    $sql = "SELECT t.cat_id, cat_name FROM " .$GLOBALS['ecs']->table('goods_type'). " AS t, ".
           $GLOBALS['ecs']->table('attribute') ." AS a".
           " WHERE t.cat_id = a.cat_id AND t.enabled = 1 AND a.attr_index > 0 ";
    $cat = $GLOBALS['db']->getAll($sql);

    /* 获取可以检索的属性 */
    if (!empty($cat))
    {
        foreach ($cat AS $val)
        {
            $attributes['cate'][$val['cat_id']] = $val['cat_name'];
        }

        $where = $cat_id > 0 ? ' AND a.cat_id = ' . $cat_id : " AND a.cat_id = " . $cat[0]['cat_id'];

        $sql = 'SELECT attr_id, attr_name, attr_input_type, attr_type, attr_values, attr_index, sort_order ' .
               ' FROM ' . $GLOBALS['ecs']->table('attribute') . ' AS a ' .
               ' WHERE a.attr_index > 0 ' .$where.
               ' ORDER BY cat_id, sort_order ASC';
        $res = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->FetchRow($res))
        {
            if ($row['attr_index'] == 1 && $row['attr_input_type'] == 1)
            {
                $row['attr_values'] = str_replace("\r", '', $row['attr_values']);
                $options = explode("\n", $row['attr_values']);

                $attr_value = array();
                foreach ($options AS $opt)
                {
                    $attr_value[$opt] = $opt;
                }
                $attributes['attr'][] = array(
                    'id'      => $row['attr_id'],
                    'attr'    => $row['attr_name'],
                    'options' => $attr_value,
                    'type'    => 3
                );
            }
            else
            {
                $attributes['attr'][] = array(
                    'id'   => $row['attr_id'],
                    'attr' => $row['attr_name'],
                    'type' => $row['attr_index']
                );
            }
        }
    }

    return $attributes;
}
?>