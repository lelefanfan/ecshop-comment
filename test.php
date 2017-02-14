<?php
/*
 * Test 测试模板页
*/
define('IN_ECS', true);

// 载入前台公用文件
require(dirname(__FILE__) . '/includes/init.php');


// 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang'])); // 缓存编号

if (!$smarty->is_cached('test.dwt', $cache_id))
{
    //分派相关变量
}

$smarty->display('test.dwt', $cache_id);