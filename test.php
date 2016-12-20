<?php
// 测试页面



define('IN_ECS', true);
// 载入前台公用文件
require(dirname(__FILE__) . '/includes/init.php');


// $time = '20-08-08';
// echo $time + 8 * 3600;die;


/*
$os = array('林武','王武','张三','李四');
// $unames = array('a'=>'林武','b'=>'王武','c'=>'张三','d'=>'李四','e'=>'王二麻子','f'=>'张宇');
// 
// $unames = array('林武','王武','张三','李四');
$vs = array('a','b','c','d');

$smarty->assign('vs',$vs);
$smarty->assign('os',$os);
*/
// $unames = array('林武','王武','张三','李四');
// $unames = array('a'=>'林武','b'=>'王武','c'=>'张三','d'=>'李四','e'=>'王二麻子','f'=>'张宇');
// $smarty->assign('unames',$unames);

$smarty->display('test.dwt');











/*------------------------测试账号------------------------
账号：linwu
密码：shaolin123
----------------------------------------------------------*/