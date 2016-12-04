<?php

/**
 * ECSHOP 前台公用文件
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: init.php 17217 2011-01-19 06:29:08Z liubo $
*/
require_once(dirname(__FILE__) . '/debug.php'); // 载入调试文件
require_once(dirname(__FILE__) . '/safety.php'); // 载入安全性文件
if (!defined('IN_ECS'))
{
    die('Hacking attempt'); // 黑客攻击
}

// 设置报错级别
error_reporting(E_ALL);

if (__FILE__ == '')
{
    die('Fatal error code: 0'); // 致命错误
}

// 取得当前ecshop所在的根目录
define('ROOT_PATH', str_replace('includes/init.php', '', str_replace('\\', '/', __FILE__)));

// 安装检测
if (!file_exists(ROOT_PATH . 'data/install.lock') && !file_exists(ROOT_PATH . 'includes/install.lock')
    && !defined('NO_CHECK_INSTALL'))
{
    header("Location: ./install/index.php\n");

    exit;
}

/* 初始化设置 */
// 内存设置
@ini_set('memory_limit',          '64M'); 
// 缓存设置
@ini_set('session.cache_expire',  180); // 指定会话页面在客户端cache中的有效期限(分钟)
@ini_set('session.use_trans_sid', 0); // 是否使用明码在URL中显示SID(会话ID)
@ini_set('session.use_cookies',   1); // 是否使用cookie在客户端保存会话ID
@ini_set('session.auto_start',    0); // 在客户访问任何页面时都自动初始化会话，默认禁止
// ？？？
@ini_set('display_errors',        1); // 是否将错误信息作为输出的一部分显示

// include载入路径设置
if (DIRECTORY_SEPARATOR == '\\')
{
    @ini_set('include_path', '.;' . ROOT_PATH);
}
else
{
    @ini_set('include_path', '.:' . ROOT_PATH);
}

// 载入数据库信息
require(ROOT_PATH . 'data/config.php');

// 设置调试模式
if (defined('DEBUG_MODE') == false)
{
    define('DEBUG_MODE', 0);
}

// 设置时区
if (PHP_VERSION >= '5.1' && !empty($timezone))
{
    date_default_timezone_set($timezone);
}

// 设置当前脚本名称
$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'index.php';
}
define('PHP_SELF', $php_self);

// 载入核心文件
require(ROOT_PATH . 'includes/inc_constant.php'); // ECSHOP 常量
require(ROOT_PATH . 'includes/cls_ecshop.php'); // EC 基础类
require(ROOT_PATH . 'includes/cls_error.php'); // 错误处理类
require(ROOT_PATH . 'includes/lib_time.php'); // 时间函数
require(ROOT_PATH . 'includes/lib_base.php'); // 基础函数库
require(ROOT_PATH . 'includes/lib_common.php'); // 公用函数库
require(ROOT_PATH . 'includes/lib_main.php'); // 前台公用函数库
require(ROOT_PATH . 'includes/lib_insert.php'); // 动态内容函数库
require(ROOT_PATH . 'includes/lib_goods.php'); // 商品相关函数库
require(ROOT_PATH . 'includes/lib_article.php'); // 文章及文章分类相关函数库

/* 对用户传入的变量进行转义操作。*/
if (!get_magic_quotes_gpc())
{
    if (!empty($_GET))
    {
        $_GET  = addslashes_deep($_GET);
    }
    if (!empty($_POST))
    {
        $_POST = addslashes_deep($_POST);
    }

    $_COOKIE   = addslashes_deep($_COOKIE);
    $_REQUEST  = addslashes_deep($_REQUEST);
}

/* 创建 ECSHOP 对象 */
$ecs = new ECS($db_name, $prefix); // 实例化基础类
define('DATA_DIR', $ecs->data_dir()); // 获取数据目录路径
define('IMAGE_DIR', $ecs->image_dir()); // 获取图片目录路径


/* 初始化数据库类 */
require(ROOT_PATH . 'includes/cls_mysql.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);


// 设置不允许缓存的表
$db->set_disable_cache_tables(array($ecs->table('sessions'), $ecs->table('sessions_data'), $ecs->table('cart')));
// 安全起见，删除数据库相关变量
$db_host = $db_user = $db_pass = $db_name = NULL;

/* 创建错误处理对象 */
$err = new ecs_error('message.dwt');

/* 载入系统参数（商城配置信息） */
$_CFG = load_config();

/* 载入前台语言文件 */
require(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/common.php');

// 商店关闭设置
if ($_CFG['shop_closed'] == 1)
{
    /* 商店关闭了，输出关闭的消息 */
    header('Content-type: text/html; charset='.EC_CHARSET);

    die('<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $_LANG['shop_closed'] . '</p><p>' . $_CFG['close_comment'] . '</p></div>');
}

// 搜索引擎蜘蛛访问
if (is_spider())
{
    /* 如果是蜘蛛的访问，那么默认为访客方式，并且不记录到日志中 */
    if (!defined('INIT_NO_USERS'))
    {
        define('INIT_NO_USERS', true);
        /* 整合UC后，如果是蜘蛛访问，初始化UC需要的常量 */
        if($_CFG['integrate_code'] == 'ucenter')
        {
             $user = init_users();
        }
    }
    $_SESSION = array();
    $_SESSION['user_id']     = 0;
    $_SESSION['user_name']   = '';
    $_SESSION['email']       = '';
    $_SESSION['user_rank']   = 0;
    $_SESSION['discount']    = 1.00;
}
// 用户访问
if (!defined('INIT_NO_USERS'))
{
    /* 初始化session */
    include(ROOT_PATH . 'includes/cls_session.php');
    // session设置
    $sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'));

    define('SESS_ID', $sess->get_session_id());
}

if(isset($_SERVER['PHP_SELF']))
{
    $_SERVER['PHP_SELF']=htmlspecialchars($_SERVER['PHP_SELF']);
}
// SMARTY相关设置
if (!defined('INIT_NO_SMARTY'))
{
    header('Cache-control: private');
    header('Content-type: text/html; charset='.EC_CHARSET);

    /* 创建 Smarty 对象。*/
    require(ROOT_PATH . 'includes/cls_template.php');
    $smarty = new cls_template;

    $smarty->cache_lifetime = $_CFG['cache_time'];
    $smarty->template_dir   = ROOT_PATH . 'themes/' . $_CFG['template'];
    $smarty->cache_dir      = ROOT_PATH . 'temp/caches';
    $smarty->compile_dir    = ROOT_PATH . 'temp/compiled';

    if ((DEBUG_MODE & 2) == 2)
    {
        $smarty->direct_output = true;
        $smarty->force_compile = true;
    }
    else
    {
        $smarty->direct_output = false;
        $smarty->force_compile = false;
    }

    $smarty->assign('lang', $_LANG);
    $smarty->assign('ecs_charset', EC_CHARSET);
    if (!empty($_CFG['stylename']))
    {
        $smarty->assign('ecs_css_path', 'themes/' . $_CFG['template'] . '/style_' . $_CFG['stylename'] . '.css');
    }
    else
    {
       
        $smarty->assign('ecs_css_path', 'themes/' . $_CFG['template'] . '/style.css');
    }

}

// 会员相关设置
if (!defined('INIT_NO_USERS'))
{
    // 会员信息，返回会员数据处理类
    $user = init_users();

    // 如果不存在 user_id
    if (!isset($_SESSION['user_id']))
    {
        // 获取投放站点的名称
        $site_name = isset($_GET['from'])   ? htmlspecialchars($_GET['from']) : addslashes($_LANG['self_site']);

        // 获取广告id
        $from_ad   = !empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;

        $_SESSION['from_ad'] = $from_ad; // 用户点击的广告ID
        $_SESSION['referer'] = stripslashes($site_name); // 用户来源

        unset($site_name);

        if (!defined('INGORE_VISIT_STATS'))
        {
            // 统计访问信息
            visit_stats();
        }
    }

    // 如果 user_id 为空
    if (empty($_SESSION['user_id']))
    {
        if ($user->get_cookie())
        {
            /* 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 */
            if ($_SESSION['user_id'] > 0)
            {
                update_user_info();
            }
        }
        else
        {
            $_SESSION['user_id']     = 0;
            $_SESSION['user_name']   = '';
            $_SESSION['email']       = '';
            $_SESSION['user_rank']   = 0;
            $_SESSION['discount']    = 1.00;
            if (!isset($_SESSION['login_fail']))
            {
                $_SESSION['login_fail'] = 0;
            }
        }
    }

    /* 设置推荐会员 */
    if (isset($_GET['u']))
    {
        set_affiliate();
    }

    /* session 不存在，检查cookie */
    if (!empty($_COOKIE['ECS']['user_id']) && !empty($_COOKIE['ECS']['password']))
    {
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT user_id, user_name, password ' .
                ' FROM ' .$ecs->table('users') .
                " WHERE user_id = '" . intval($_COOKIE['ECS']['user_id']) . "' AND password = '" .$_COOKIE['ECS']['password']. "'";

        $row = $db->GetRow($sql);

        if (!$row)
        {
            // 没有找到这个记录
           $time = time() - 3600;
           setcookie("ECS[user_id]",  '', $time, '/');
           setcookie("ECS[password]", '', $time, '/');
        }
        else
        {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            update_user_info();
        }
    }

    // 注入 $_SESSION 到模板
    if (isset($smarty))
    {
        $smarty->assign('ecs_session', $_SESSION);
    }
}

// 设置调试模式与之对应的报错级别
if ((DEBUG_MODE & 1) == 1)
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); 
}
if ((DEBUG_MODE & 4) == 4)
{
    include(ROOT_PATH . 'includes/lib.debug.php');
}

/* 判断是否支持 Gzip 模式 */
if (!defined('INIT_NO_SMARTY') && gzip_enabled())
{
    ob_start('ob_gzhandler');
}
else
{
    ob_start();
}
?>