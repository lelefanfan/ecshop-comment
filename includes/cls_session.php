<?php

/**
 * ECSHOP SESSION 公用类库
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: cls_session.php 17217 2011-01-19 06:29:08Z liubo $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

class cls_session
{
    var $db             = NULL; // 数据库句柄
    var $session_table  = ''; // SESSION存储表名

    var $max_life_time  = 1800; // SESSION最大生命周期（单位为秒）

    var $session_name   = ''; // 会话名
    var $session_id     = ''; // 会话ID

    var $session_expiry = ''; // SESSION 有效期
    var $session_md5    = ''; // 加密

    // 下面的三个参数都为setcookie中的参数
    var $session_cookie_path   = '/'; // COOKIE 服务器路径
    var $session_cookie_domain = ''; // COOKIE 域名
    var $session_cookie_secure = false; // 规定是否通过安全的 HTTPS 连接来传输 cookie。

    // 客户端真实IP
    var $_ip   = '';
    // 当前时间
    var $_time = 0;

    function __construct(&$db, $session_table, $session_data_table, $session_name = 'ECS_ID', $session_id = '')
    {
        $this->cls_session($db, $session_table, $session_data_table, $session_name, $session_id);
    }

    function cls_session(&$db, $session_table, $session_data_table, $session_name = 'ECS_ID', $session_id = '')
    {
        // 初始化一个全局SESSION变量
        $GLOBALS['_SESSION'] = array();

        // 初始化 setcookie() 三个参数
        if (!empty($GLOBALS['cookie_path']))
        {
            $this->session_cookie_path = $GLOBALS['cookie_path'];
        }
        else
        {
            $this->session_cookie_path = '/';
        }

        if (!empty($GLOBALS['cookie_domain']))
        {
            $this->session_cookie_domain = $GLOBALS['cookie_domain'];
        }
        else
        {
            $this->session_cookie_domain = '';
        }

        if (!empty($GLOBALS['cookie_secure']))
        {
            $this->session_cookie_secure = $GLOBALS['cookie_secure'];
        }
        else
        {
            $this->session_cookie_secure = false;
        }

        $this->session_name       = $session_name;
        $this->session_table      = $session_table; // SESSION 表
        $this->session_data_table = $session_data_table; // SESSION 数据库表

        // 数据库句柄
        $this->db  = $db;
        // 用户真实id
        $this->_ip = real_ip();

        // 检测COOKIE中是否存在SESSION的ID
        if ($session_id == '' && !empty($_COOKIE[$this->session_name]))
        {
            $this->session_id = $_COOKIE[$this->session_name];
        }
        else
        {
            $this->session_id = $session_id;
        }

        // 检测COOKIE中存在SESSION的ID合法性 
        if ($this->session_id)
        {
            // 截取COOKIE中SESSION的ID前32位
            $tmp_session_id = substr($this->session_id, 0, 32);

            if ($this->gen_session_key($tmp_session_id) == substr($this->session_id, 32))
            {
                $this->session_id = $tmp_session_id;
            }
            else
            {
                $this->session_id = '';
            }
        }

        // 获取当前时间
        $this->_time = time();

        if ($this->session_id)
        {
            $this->load_session(); // 加载session
        }
        else // 不存在session id
        {
            $this->gen_session_id(); // 生成session id，并初始化。
            // 生成session的cookie
            setcookie($this->session_name, $this->session_id . $this->gen_session_key($this->session_id), 0, $this->session_cookie_path, $this->session_cookie_domain, $this->session_cookie_secure);
        }

        register_shutdown_function(array(&$this, 'close_session'));
    }

    /**
     * gen_session_id 生成一个唯一的session_id并插入数据库
     * @return string   返回32位session id
     */
    function gen_session_id()
    {
        $this->session_id = md5(uniqid(mt_rand(), true));

        return $this->insert_session();
    }

    /**
     * gen_session_key 获取 SESSION KEY，根据 $session_id等 加密的key
     * @param  [string] $session_id  SESSION ID
     * @return string             session key
     */
    function gen_session_key($session_id)
    {
        static $ip = '';

        if ($ip == '')
        {
            $ip = substr($this->_ip, 0, strrpos($this->_ip, '.'));
        }

        return sprintf('%08x', crc32(ROOT_PATH . $ip . $session_id));
    }

    /**
     * insert_session 初始化session，将 SESSION ID 插入数据库
     * @return boolean 成功返回true，失败返回false
     */
    function insert_session()
    {
        return $this->db->query('INSERT INTO ' . $this->session_table . " (sesskey, expiry, ip, data) VALUES ('" . $this->session_id . "', '". $this->_time ."', '". $this->_ip ."', 'a:0:{}')");
    }

    /**
     * load_session 加载session到$GLOBALS['_SESSION']全局变量中
     */
    function load_session()
    {
        // 根据session的id从 ecs_sessions 表获取当前session数据
        $session = $this->db->getRow('SELECT userid, adminid, user_name, user_rank, discount, email, data, expiry FROM ' . $this->session_table . " WHERE sesskey = '" . $this->session_id . "'");

        if (empty($session))
        {
            // 初始化session信息
            $this->insert_session();
            $this->session_expiry = 0;
            $this->session_md5    = '40cd750bba9870f18aada2478b24840a';
            $GLOBALS['_SESSION']  = array();
        }
        else
        {
            // 存在数据并且在有效期范围，'expiry'字段表示session创建时间
            if (!empty($session['data']) && $this->_time - $session['expiry'] <= $this->max_life_time)
            {
                // 重新设置有效期
                $this->session_expiry = $session['expiry'];
                // session md5
                $this->session_md5    = md5($session['data']);
                // 将session内容保存到全局变量 '_SESSION' 中
                $GLOBALS['_SESSION']  = unserialize($session['data']); // session内容
                $GLOBALS['_SESSION']['user_id'] = $session['userid']; // userid
                $GLOBALS['_SESSION']['admin_id'] = $session['adminid']; // adminid
                $GLOBALS['_SESSION']['user_name'] = $session['user_name']; // user_name
                $GLOBALS['_SESSION']['user_rank'] = $session['user_rank']; // user_rank
                $GLOBALS['_SESSION']['discount'] = $session['discount']; // discount
                $GLOBALS['_SESSION']['email'] = $session['email']; // 邮箱
            }
            else
            {
                // 根据session的id获取sessions_data表信息
                $session_data = $this->db->getRow('SELECT data, expiry FROM ' . $this->session_data_table . " WHERE sesskey = '" . $this->session_id . "'");
                if (!empty($session_data['data']) && $this->_time - $session_data['expiry'] <= $this->max_life_time)
                {
                    $this->session_expiry = $session_data['expiry'];
                    $this->session_md5    = md5($session_data['data']);
                    // 将session内容保存到全局变量 '_SESSION' 中
                    $GLOBALS['_SESSION']  = unserialize($session_data['data']);
                    $GLOBALS['_SESSION']['user_id'] = $session['userid'];
                    $GLOBALS['_SESSION']['admin_id'] = $session['adminid'];
                    $GLOBALS['_SESSION']['user_name'] = $session['user_name'];
                    $GLOBALS['_SESSION']['user_rank'] = $session['user_rank'];
                    $GLOBALS['_SESSION']['discount'] = $session['discount'];
                    $GLOBALS['_SESSION']['email'] = $session['email'];
                }
                else
                {
                    // 清空session信息
                    $this->session_expiry = 0;
                    $this->session_md5    = '40cd750bba9870f18aada2478b24840a';
                    $GLOBALS['_SESSION']  = array();
                }
            }
        }
    }

    /**
     * update_session 更新session
     * @return [type] [description]
     */
    function update_session()
    {
        $adminid = !empty($GLOBALS['_SESSION']['admin_id']) ? intval($GLOBALS['_SESSION']['admin_id']) : 0;
        $userid  = !empty($GLOBALS['_SESSION']['user_id'])  ? intval($GLOBALS['_SESSION']['user_id'])  : 0;
        $user_name  = !empty($GLOBALS['_SESSION']['user_name'])  ? trim($GLOBALS['_SESSION']['user_name'])  : 0;
        $user_rank  = !empty($GLOBALS['_SESSION']['user_rank'])  ? intval($GLOBALS['_SESSION']['user_rank'])  : 0;
        $discount  = !empty($GLOBALS['_SESSION']['discount'])  ? round($GLOBALS['_SESSION']['discount'], 2)  : 0;
        $email  = !empty($GLOBALS['_SESSION']['email'])  ? trim($GLOBALS['_SESSION']['email'])  : 0;
        unset($GLOBALS['_SESSION']['admin_id']);
        unset($GLOBALS['_SESSION']['user_id']);
        unset($GLOBALS['_SESSION']['user_name']);
        unset($GLOBALS['_SESSION']['user_rank']);
        unset($GLOBALS['_SESSION']['discount']);
        unset($GLOBALS['_SESSION']['email']);

        $data        = serialize($GLOBALS['_SESSION']);
        $this->_time = time();

        if ($this->session_md5 == md5($data) && $this->_time < $this->session_expiry + 10)
        {
            return true;
        }

        $data = addslashes($data);

        if (isset($data{255}))
        {
            $this->db->autoReplace($this->session_data_table, array('sesskey' => $this->session_id, 'expiry' => $this->_time, 'data' => $data), array('expiry' => $this->_time,'data' => $data));

            $data = '';
        }

        return $this->db->query('UPDATE ' . $this->session_table . " SET expiry = '" . $this->_time . "', ip = '" . $this->_ip . "', userid = '" . $userid . "', adminid = '" . $adminid . "', user_name='" . $user_name . "', user_rank='" . $user_rank . "', discount='" . $discount . "', email='" . $email . "', data = '$data' WHERE sesskey = '" . $this->session_id . "' LIMIT 1");
    }

    /**
     * close_session 删除session
     * @return boolean 成功返回true，失败返回false
     */
    function close_session()
    {
        $this->update_session();

        /* 随机对 sessions_data 的库进行删除操作，但是只删除过期的session */
        if (mt_rand(0, 2) == 2)
        {
            $this->db->query('DELETE FROM ' . $this->session_data_table . ' WHERE expiry < ' . ($this->_time - $this->max_life_time));
        }

        /* 随机对 sessionss 的库进行删除操作，但是只删除过期的session */
        if ((time() % 2) == 0)
        {
            return $this->db->query('DELETE FROM ' . $this->session_table . ' WHERE expiry < ' . ($this->_time - $this->max_life_time));
        }

        return true;
    }

    /**
     * delete_spec_admin_session 根据管理员id删除session
     * @param  string $adminid 管理员id
     * @return boolean         成功返回true，失败返回false
     */
    function delete_spec_admin_session($adminid)
    {
        if (!empty($GLOBALS['_SESSION']['admin_id']) && $adminid)
        {
            return $this->db->query('DELETE FROM ' . $this->session_table . " WHERE adminid = '$adminid'");
        }
        else
        {
            return false;
        }
    }

    /**
     * destroy_session 销毁session
     * @return boolean         成功返回true，失败返回false
     */
    function destroy_session()
    {
        $GLOBALS['_SESSION'] = array();

        setcookie($this->session_name, $this->session_id, 1, $this->session_cookie_path, $this->session_cookie_domain, $this->session_cookie_secure);

        /* ECSHOP 自定义执行部分 */
        if (!empty($GLOBALS['ecs']))
        {
            $this->db->query('DELETE FROM ' . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '$this->session_id'");
        }
        /* ECSHOP 自定义执行部分 */

        $this->db->query('DELETE FROM ' . $this->session_data_table . " WHERE sesskey = '" . $this->session_id . "' LIMIT 1");

        return $this->db->query('DELETE FROM ' . $this->session_table . " WHERE sesskey = '" . $this->session_id . "' LIMIT 1");
    }

    /**
     * get_session_id 获取session的id
     * @return int 返回session的id
     */
    function get_session_id()
    {
        return $this->session_id;
    }

    /**
     * get_users_count 获取session的条数
     * @return int session条数
     */
    function get_users_count()
    {
        return $this->db->getOne('SELECT count(*) FROM ' . $this->session_table);
    }
}

?>