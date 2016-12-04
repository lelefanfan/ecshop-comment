<?php

/**
 * ECSHOP MYSQL 公用类库
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: cls_mysql.php 17217 2011-01-19 06:29:08Z liubo $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt'); // 黑客攻击
}

class cls_mysql
{
    var $link_id    = NULL; // 资源句柄

    var $settings   = array(); // 存储数据库用户名、密码等参数

    var $queryCount = 0; // 执行sql的次数
    var $queryTime  = ''; // 执行sql时间
    var $queryLog   = array(); // 记录被执行的sql语句

    var $max_cache_time = 300; // 最大的缓存时间，以秒为单位

    var $cache_data_dir = 'temp/query_caches/'; // 缓存目录
    var $root_path      = ''; //应用根路径

    var $error_message  = array(); 
    var $platform       = ''; // 服务器平台
    var $version        = ''; // MySQL版本
    var $dbhash         = ''; // 哈希值
    var $starttime      = 0; // 开始时间
    var $timeline       = 0;
    var $timezone       = 0;

    var $mysql_config_cache_file_time = 0;

    var $mysql_disable_cache_tables = array(); // 不允许被缓存的表，遇到将不会进行缓存

    function __construct($dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'gbk', $pconnect = 0, $quiet = 0)
    {
        $this->cls_mysql($dbhost, $dbuser, $dbpw, $dbname, $charset, $pconnect, $quiet);
    }

    /**
     * 类构造函数
     *
     * @access public
     * @param  string  $dbhost   [端口]
     * @param  string  $dbuser   [用户名]
     * @param  string  $dbpw     [密码]
     * @param  string  $dbname   [数据库名称]
     * @param  string  $charset  [编码]
     * @param  integer $pconnect [数据库持久连接标示]
     * @param  integer $quiet    [静默模式(是否显示错误信息)]
     * @return boolean           [成功返回true，失败返回false]
     */
    function cls_mysql($dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'gbk', $pconnect = 0, $quiet = 0)
    {
        if (defined('EC_CHARSET'))
        {
            $charset = strtolower(str_replace('-', '', EC_CHARSET));
        }

        if (defined('ROOT_PATH') && !$this->root_path)
        {
            $this->root_path = ROOT_PATH;
        }

        if ($quiet)
        {
            $this->connect($dbhost, $dbuser, $dbpw, $dbname, $charset, $pconnect, $quiet);
        }
        else
        {
            $this->settings = array(
                                    'dbhost'   => $dbhost,
                                    'dbuser'   => $dbuser,
                                    'dbpw'     => $dbpw,
                                    'dbname'   => $dbname,
                                    'charset'  => $charset,
                                    'pconnect' => $pconnect
                                    );
        }
    }

    /**
     * connect 连接数据库
     * @param  string  $dbhost   数据库地址
     * @param  string  $dbuser   用户名
     * @param  string  $dbpw     密码
     * @param  string  $dbname   数据库名称
     * @param  string  $charset  编码
     * @param  integer $pconnect 数据持久连接标识
     * @param  integer $quiet    静默模式(是否显示错误信息)
     * @return boolean           true连接成功,false连接失败
     */
    function connect($dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'utf8', $pconnect = 0, $quiet = 0)
    {
        if ($pconnect)
        {   /* 持久连接 */

            if (!($this->link_id = @mysql_pconnect($dbhost, $dbuser, $dbpw)))
            {
                // 非静默模式显示错误
                if (!$quiet)
                {
                    $this->ErrorMsg("Can't pConnect MySQL Server($dbhost)!");
                }

                return false;
            }
        }
        else
        {   /* 普通连接 */

            if (PHP_VERSION >= '4.2')
            {
                $this->link_id = @mysql_connect($dbhost, $dbuser, $dbpw, true);
            }
            else
            {
                $this->link_id = @mysql_connect($dbhost, $dbuser, $dbpw);
                // 对 PHP 4.2 以下的版本进行随机数函数的初始化工作
                mt_srand((double)microtime() * 1000000); 
            }
            // 连接不成功
            if (!$this->link_id)
            {
                // 非静默模式显示错误
                if (!$quiet)
                {
                    $this->ErrorMsg("Can't Connect MySQL Server($dbhost)!");
                }

                return false;
            }
        }
        // 数据库哈希值
        $this->dbhash  = md5($this->root_path . $dbhost . $dbuser . $dbpw . $dbname);
        // 取得 MySQL 服务器信息 
        $this->version = mysql_get_server_info($this->link_id);

        /* 如果mysql 版本是 4.1+ 以上，需要对字符集进行初始化 */
        if ($this->version > '4.1')
        {
            if ($charset != 'latin1')
            {
                mysql_query("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary", $this->link_id);
            }
            if ($this->version > '5.0.1')
            {
                mysql_query("SET sql_mode=''", $this->link_id);
            }
        }
        // 缓存文件地址
        $sqlcache_config_file = $this->root_path . $this->cache_data_dir . 'sqlcache_config_file_' . $this->dbhash . '.php';
        // 载入缓存文件
        @include($sqlcache_config_file);
        // 当前时间
        $this->starttime = time();

        // 缓存有效期检测，如果已经过期，执行if语句里的代码
        if ($this->max_cache_time && $this->starttime > $this->mysql_config_cache_file_time + $this->max_cache_time)
        {
            /* 检测数据库所在服务器平台 */
            if ($dbhost != '.')
            {
                $result = mysql_query("SHOW VARIABLES LIKE 'basedir'", $this->link_id);
                $row    = mysql_fetch_assoc($result);
                if (!empty($row['Value']{1}) && $row['Value']{1} == ':' && !empty($row['Value']{2}) && $row['Value']{2} == "\\")
                {
                    $this->platform = 'WINDOWS';
                }
                else
                {
                    $this->platform = 'OTHER';
                }
            }
            else
            {
                $this->platform = 'WINDOWS';
            }

            if ($this->platform == 'OTHER' &&
                ($dbhost != '.' && strtolower($dbhost) != 'localhost:3306' && $dbhost != '127.0.0.1:3306') ||
                (PHP_VERSION >= '5.1' && date_default_timezone_get() == 'UTC'))
            {
                $result = mysql_query("SELECT UNIX_TIMESTAMP() AS timeline, UNIX_TIMESTAMP('" . date('Y-m-d H:i:s', $this->starttime) . "') AS timezone", $this->link_id);
                $row    = mysql_fetch_assoc($result);

                if ($dbhost != '.' && strtolower($dbhost) != 'localhost:3306' && $dbhost != '127.0.0.1:3306')
                {
                    $this->timeline = $this->starttime - $row['timeline'];
                }

                if (PHP_VERSION >= '5.1' && date_default_timezone_get() == 'UTC')
                {
                    $this->timezone = $this->starttime - $row['timezone'];
                }
            }

            $content = '<' . "?php\r\n" .
                       '$this->mysql_config_cache_file_time = ' . $this->starttime . ";\r\n" .
                       '$this->timeline = ' . $this->timeline . ";\r\n" .
                       '$this->timezone = ' . $this->timezone . ";\r\n" .
                       '$this->platform = ' . "'" . $this->platform . "';\r\n?" . '>';

            // 写入缓存文件
            @file_put_contents($sqlcache_config_file, $content);
        }

        /* 选择数据库 */
        if ($dbname)
        {
            // 选择数据库
            if (mysql_select_db($dbname, $this->link_id) === false )
            {
                if (!$quiet)
                {
                    $this->ErrorMsg("Can't select MySQL database($dbname)!");
                }

                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }

    /**
     * select_database 选择数据库
     * @param  string $dbname 选择数据库名称
     * @return boolean        成功返回true，失败返回false
     */
    function select_database($dbname)
    {
        return mysql_select_db($dbname, $this->link_id);
    }

    /**
     * set_mysql_charset 设置数据库编码
     * @param string $charset 编码名称
     */
    function set_mysql_charset($charset)
    {
        /* 如果mysql 版本是 4.1+ 以上，需要对字符集进行初始化 */
        if ($this->version > '4.1')
        {
            if (in_array(strtolower($charset), array('gbk', 'big5', 'utf-8', 'utf8')))
            {
                $charset = str_replace('-', '', $charset);
            }
            if ($charset != 'latin1')
            {
                mysql_query("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary", $this->link_id);
            }
        }
    }

    /**
     * fetch_array 从结果集中取得一行作为关联数组，或数字数组，或二者兼有
     * @param  resource $query       资源句柄
     * @param  int $result_type      返回数组类型
     * @return array                 返回一行数组，失败返回false
     */
    function fetch_array($query, $result_type = MYSQL_ASSOC)
    {
        return mysql_fetch_array($query, $result_type);
    }

    /**
     * query 执行sql语句
     * @param  string $sql  需要执行的sql语句
     * @param  string $type 静默模式，如果值为'SILENT'表示连接失败不会报错
     * @return resource     成功返回资源（SELECT，SHOW，DESCRIBE, EXPLAIN等 ）或TRUE（INSERT, UPDATE, DELETE, DROP等 ），失败返回false
     */
    function query($sql, $type = '')
    {
        // 连接数据库
        if ($this->link_id === NULL)
        {
            $this->connect($this->settings['dbhost'], $this->settings['dbuser'], $this->settings['dbpw'], $this->settings['dbname'], $this->settings['charset'], $this->settings['pconnect']);
            $this->settings = array();
        }
        // 记录最近99条sql语句
        if ($this->queryCount++ <= 99)
        {
            $this->queryLog[] = $sql;
        }
        // 设置当前sql执行时间
        if ($this->queryTime == '')
        {
            if (PHP_VERSION >= '5.0.0')
            {
                $this->queryTime = microtime(true);
            }
            else
            {
                $this->queryTime = microtime();
            }
        }

        /* 当当前的时间大于类初始化时间的时候，自动执行 ping 这个自动重新连接操作 */
        if (PHP_VERSION >= '4.3' && time() > $this->starttime + 1)
        {
            mysql_ping($this->link_id);
        }

        // 如果执行sql失败，并且不是静默模式，就会报错
        if (!($query = mysql_query($sql, $this->link_id)) && $type != 'SILENT')
        {
            $this->error_message[]['message'] = 'MySQL Query Error';
            $this->error_message[]['sql'] = $sql;
            $this->error_message[]['error'] = mysql_error($this->link_id);
            $this->error_message[]['errno'] = mysql_errno($this->link_id);

            $this->ErrorMsg();

            return false;
        }

        // 调试模式，记录日志
        if (defined('DEBUG_MODE') && (DEBUG_MODE & 8) == 8)
        {
            // 日志文件路径
            $logfilename = $this->root_path . DATA_DIR . '/mysql_query_' . $this->dbhash . '_' . date('Y_m_d') . '.log';
            $str = $sql . "\n\n";

            // 记录日志
            if (PHP_VERSION >= '5.0')
            {
                file_put_contents($logfilename, $str, FILE_APPEND);
            }
            else
            {
                $fp = @fopen($logfilename, 'ab+');
                if ($fp)
                {
                    fwrite($fp, $str);
                    fclose($fp);
                }
            }
        }

        return $query;
    }

    /**
     * affected_rows    取得前一次 MySQL 操作所影响的记录行数
     * @return int      执行成功则返回受影响的行的数目，如果最近一次查询失败的话，函数返回 -1。 
     */
    function affected_rows()
    {
        return mysql_affected_rows($this->link_id);
    }

    /**
     * error 获取上一个 MySQL 操作产生的文本错误信息
     * @return string 成功返回错误信息，没有出错则返回 ''（空字符串）。
     */
    function error()
    {
        return mysql_error($this->link_id);
    }

    /**
     * errno 获取上一个 MySQL 操作中的错误信息的数字编码 
     * @return int 返回上一个 MySQL 函数的错误号码，如果没有出错则返回 0（零）。
     */
    function errno()
    {
        return mysql_errno($this->link_id);
    }

    /**
     * result 取得结果数据
     * @param  resource $query 资源句柄
     * @param  int $row   取得行数
     * @return array      成功以数组形式返回结果数据，失败返回false
     */
    function result($query, $row)
    {
        return @mysql_result($query, $row);
    }

    /**
     * num_rows 取得结果集中行的数目 
     * @param  resource $query 资源
     * @return int        返回结果集中行的数目。此命令仅对 SELECT 语句有效。
     */
    function num_rows($query)
    {
        return mysql_num_rows($query);
    }

    /**
     * num_fields 取得结果集中字段的数目
     * @param  resource $query 资源
     * @return int        返回结果集中字段的数目。
     */
    function num_fields($query)
    {
        return mysql_num_fields($query);
    }

    /**
     * free_result 释放结果内存
     * @param  resource $query 资源
     * @return [type]        成功时返回 TRUE，或者在失败时返回 FALSE 。
     */
    function free_result($query)
    {
        return mysql_free_result($query);
    }

    /**
     * insert_id 取得上一步 INSERT 操作产生的 ID
     * @return int        成功返回id号，失败返回0。
     */
    function insert_id()
    {
        return mysql_insert_id($this->link_id);
    }

    /**
     * fetchRow 从结果集中取得一行作为关联数组
     * @param  resource $query 资源
     * @return array        返回根据从结果集取得的行生成的关联数组；如果没有更多行则返回 FALSE 。
     */
    function fetchRow($query)
    {
        return mysql_fetch_assoc($query);
    }

    /**
     * fetch_fields 从结果集中取得列信息并作为对象返回
     * @param  resource $query 资源
     * @return object        返回一个包含字段信息的对象
     */
    function fetch_fields($query)
    {
        return mysql_fetch_field($query);
    }

    /**
     * version 获取MySQL版本信息
     * @return string 返回版本信息
     */
    function version()
    {
        return $this->version;
    }

    /**
     * ping 一个服务器连接，如果没有连接则重新连接 
     * @return boolean 成功返回 TRUE，失败返回 FALSE。
     */
    function ping()
    {
        if (PHP_VERSION >= '4.3')
        {
            return mysql_ping($this->link_id);
        }
        else
        {
            return false;
        }
    }

    /**
     * escape_string 转义 SQL 语句中使用的字符串中的特殊字符，并考虑到连接的当前字符集 
     * @param  string $unescaped_string 需要转义的字符
     * @return string                   转义后的字符串
     */
    static function escape_string($unescaped_string)
    {
        if (PHP_VERSION >= '4.3')
        {
            return mysql_real_escape_string($unescaped_string);
        }
        else
        {
            return mysql_escape_string($unescaped_string);
        }
    }

    /**
     * close 关闭 MySQL 连接 
     * @return boolean 成功时返回 TRUE ， 或者在失败时返回 FALSE 。 
     */
    function close()
    {
        return mysql_close($this->link_id);
    }

    /**
     * ErrorMsg 输出错误信息
     * @param string $message 要输出的错误信息
     * @param string $sql     该参数暂未启用
     */
    function ErrorMsg($message = '', $sql = '')
    {
        if ($message)
        {
            echo "<b>ECSHOP info</b>: $message\n\n<br /><br />";
            //print('<a href="http://faq.comsenz.com/?type=mysql&dberrno=2003&dberror=Can%27t%20connect%20to%20MySQL%20server%20on" target="_blank">http://faq.comsenz.com/</a>');
        }
        else
        {
            echo "<b>MySQL server error report:";
            print_r($this->error_message);
            //echo "<br /><br /><a href='http://faq.comsenz.com/?type=mysql&dberrno=" . $this->error_message[3]['errno'] . "&dberror=" . urlencode($this->error_message[2]['error']) . "' target='_blank'>http://faq.comsenz.com/</a>";
        }

        exit;
    }

    /**
     * selectLimit 为sql语句拼接 'LIMIT' 参数
     * @param  string   $sql   sql语句
     * @param  integer  $num   偏移数量
     * @param  integer  $start 开始偏移量
     * @return string         拼接 'LIMIT' 参数后的sql语句
     */
    function selectLimit($sql, $num, $start = 0)
    {
        if ($start == 0)
        {
            $sql .= ' LIMIT ' . $num;
        }
        else
        {
            $sql .= ' LIMIT ' . $start . ', ' . $num;
        }

        return $this->query($sql);
    }

    /**
     * getOne  从结果集中取得一行作为枚举数组 
     * @param  string  $sql     sql语句
     * @param  boolean $limited 如果为true，表示只查询一条数据。
     * @return array           返回一条数据
     */
    function getOne($sql, $limited = false)
    {
        if ($limited == true)
        {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = $this->query($sql);
        if ($res !== false)
        {
            $row = mysql_fetch_row($res);

            if ($row !== false)
            {
                return $row[0];
            }
            else
            {
                return '';
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * getOneCached 返回带缓存的一条数据库数据
     * @param  string $sql    sql语句
     * @param  string $cached 缓存类型,FILEFIRST,MYSQLFIRST
     * @return array         查询到的数据(array,int键值)
     */
    function getOneCached($sql, $cached = 'FILEFIRST')
    {
        // 为sql语句拼接 'LIMIT 1'
        $sql = trim($sql . ' LIMIT 1');

        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->platform != 'WINDOWS')) && $this->max_cache_time;


        if (!$cachefirst)
        {   //不缓存，直接返回
            return $this->getOne($sql, true);
        }
        else
        {
            //带缓存执行sql
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true)
            {
                return $result['data'];
            }
        }

        $arr = $this->getOne($sql, true);

        if ($arr !== false && $cachefirst)
        {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    /**
     * getAll 执行sql语句，并返回全部结果集
     * @param  string $sql sql语句
     * @return array      结果集
     */
    function getAll($sql)
    {
        $res = $this->query($sql);
        if ($res !== false)
        {
            $arr = array();
            while ($row = mysql_fetch_assoc($res))
            {
                $arr[] = $row;
            }

            return $arr;
        }
        else
        {
            return false;
        }
    }

    function getAllCached($sql, $cached = 'FILEFIRST')
    {
        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst)
        {
            return $this->getAll($sql);
        }
        else
        {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true)
            {
                return $result['data'];
            }
        }

        $arr = $this->getAll($sql);

        if ($arr !== false && $cachefirst)
        {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    /**
     * getRow 从结果集中取得一行作为关联数组 
     * @param  string  $sql     sql语句
     * @param  boolean $limited 如果为true，表示只查询一条数据。
     * @return array           一条结果集
     */
    function getRow($sql, $limited = false)
    {
        if ($limited == true)
        {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = $this->query($sql);
        if ($res !== false)
        {
            return mysql_fetch_assoc($res);
        }
        else
        {
            return false;
        }
    }

    function getRowCached($sql, $cached = 'FILEFIRST')
    {
        $sql = trim($sql . ' LIMIT 1');

        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst)
        {
            return $this->getRow($sql, true);
        }
        else
        {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true)
            {
                return $result['data'];
            }
        }

        $arr = $this->getRow($sql, true);

        if ($arr !== false && $cachefirst)
        {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    /**
     * getCol 获取结果集的第一个字段值
     * @param  string $sql sql语句
     * @return array      返回一个字段值
     */
    function getCol($sql)
    {
        $res = $this->query($sql);
        if ($res !== false)
        {
            $arr = array();
            while ($row = mysql_fetch_row($res))
            {
                $arr[] = $row[0];
            }

            return $arr;
        }
        else
        {
            return false;
        }
    }

    function getColCached($sql, $cached = 'FILEFIRST')
    {
        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst)
        {
            return $this->getCol($sql);
        }
        else
        {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true)
            {
                return $result['data'];
            }
        }

        $arr = $this->getCol($sql);

        if ($arr !== false && $cachefirst)
        {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    /**
     * autoExecute 自动执行INSERT或UPDATE
     * @param  string $table        需要操作的表名
     * @param  array $field_values  需要插入或更新的数据
     * @param  string $mode         执行的动作，包括'INSERT、UPDATE'
     * @param  string $where        执行'UPDATE'的条件
     * @param  string $querymode    静默模式，如果值为 'SILENT',表示为静默模式，执行错误不会报错
     * @return boolean              成功返回true，失败返回false
     */
    function autoExecute($table, $field_values, $mode = 'INSERT', $where = '', $querymode = '')
    {
        // 获取 $table 表的字段名称
        $field_names = $this->getCol('DESC ' . $table);

        $sql = '';
        if ($mode == 'INSERT') // 如果是插入数据
        {
            $fields = $values = array();
            // 循环 $table 所有字段，为插入语句 字段 和 值赋值  'INSERT INTO 表名 (字段) VALUES (值)'
            foreach ($field_names AS $value)
            {
                if (array_key_exists($value, $field_values) == true)
                {
                    $fields[] = $value;
                    $values[] = "'" . $field_values[$value] . "'";
                }
            }

            if (!empty($fields))
            {
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        }
        else
        {
            $sets = array();
            foreach ($field_names AS $value)
            {
                if (array_key_exists($value, $field_values) == true)
                {
                    $sets[] = $value . " = '" . $field_values[$value] . "'";
                }
            }

            if (!empty($sets))
            {
                $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
            }
        }

        if ($sql)
        {
            return $this->query($sql, $querymode);
        }
        else
        {
            return false;
        }
    }

    /**
     * autoReplace 自动添加更新数据（插入数据时会进行检测，如果存在主键，插入并更新，如果不存在，直接插入）
     * @param  string $table         需要操作的表名
     * @param  array  $field_values  字段数据
     * @param  array  $update_values 更新字段数据
     * @param  string $where         条件，如果mysql版本高于4.1，此参数无用
     * @param  string $querymode     静默模式
     * @return boolean               成功返回true，失败返回false
     */
    function autoReplace($table, $field_values, $update_values, $where = '', $querymode = '')
    {
        // 获取 $table 表字段信息
        $field_descs = $this->getAll('DESC ' . $table);

        $primary_keys = array();
        foreach ($field_descs AS $value)
        {
            $field_names[] = $value['Field']; // 获取 $table 表字段名称
            if ($value['Key'] == 'PRI')
            {
                $primary_keys[] = $value['Field']; //获取主键字段
            }
        }

        $fields = $values = array();
        // 循环 $table 表所有字段，分别设置字段和值
        foreach ($field_names AS $value)
        {
            if (array_key_exists($value, $field_values) == true)
            {
                $fields[] = $value;
                $values[] = "'" . $field_values[$value] . "'";
            }
        }

        $sets = array();
        foreach ($update_values AS $key => $value)
        {
            // 如果更新字段，存在与值字段
            if (array_key_exists($key, $field_values) == true)
            {
                if (is_int($value) || is_float($value)) // 如果是数值型
                {
                    $sets[] = $key . ' = ' . $key . ' + ' . $value;
                }
                else
                {
                    $sets[] = $key . " = '" . $value . "'";
                }
            }
        }

        $sql = '';
        if (empty($primary_keys)) // 如果不存在主键，直接插入
        {
            // 如果 $fields 有值
            if (!empty($fields))
            {
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        }
        else //如果存在主键，插入并更新
        {
            if ($this->version() >= '4.1')
            {
                // 如果 $fields 有值
                if (!empty($fields))
                {
                    $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                    if (!empty($sets))
                    {
                        $sql .=  'ON DUPLICATE KEY UPDATE ' . implode(', ', $sets);
                    }
                }
            }
            else
            {
                if (empty($where))
                {
                    $where = array();
                    foreach ($primary_keys AS $value)
                    {
                        if (is_numeric($value))
                        {
                            $where[] = $value . ' = ' . $field_values[$value];
                        }
                        else
                        {
                            $where[] = $value . " = '" . $field_values[$value] . "'";
                        }
                    }
                    $where = implode(' AND ', $where);
                }

                if ($where && (!empty($sets) || !empty($fields)))
                {
                    if (intval($this->getOne("SELECT COUNT(*) FROM $table WHERE $where")) > 0)
                    {
                        if (!empty($sets))
                        {
                            $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
                        }
                    }
                    else
                    {
                        if (!empty($fields))
                        {
                            $sql = 'REPLACE INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                        }
                    }
                }
            }
        }

        if ($sql)
        {
            return $this->query($sql, $querymode);
        }
        else
        {
            return false;
        }
    }

    function setMaxCacheTime($second)
    {
        $this->max_cache_time = $second;
    }

    function getMaxCacheTime()
    {
        return $this->max_cache_time;
    }

    /**
     * getSqlCacheData 获取数据缓存
     * @param  string $sql    sql语句
     * @param  string $cached 缓存类型(FILEFIRST,MYSQLFIRST)
     * @return array         结果数组$result[filename]缓存文件地址,$result[storecache]是否缓存，$result[data]缓存内容
     */
    function getSqlCacheData($sql, $cached = '')
    {
        $sql = trim($sql);

        $result = array();
        $result['filename'] = $this->root_path . $this->cache_data_dir . 'sqlcache_' . abs(crc32($this->dbhash . $sql)) . '_' . md5($this->dbhash . $sql) . '.php';

        $data = @file_get_contents($result['filename']);
        if (isset($data{23}))
        {
            $filetime = substr($data, 13, 10);
            $data     = substr($data, 23);

            if (($cached == 'FILEFIRST' && time() > $filetime + $this->max_cache_time) || ($cached == 'MYSQLFIRST' && $this->table_lastupdate($this->get_table_name($sql)) > $filetime))
            {
                $result['storecache'] = true;
            }
            else
            {
                $result['data'] = @unserialize($data);
                if ($result['data'] === false)
                {
                    $result['storecache'] = true;
                }
                else
                {
                    $result['storecache'] = false;
                }
            }
        }
        else
        {
            $result['storecache'] = true;
        }

        return $result;
    }

    function setSqlCacheData($result, $data)
    {
        if ($result['storecache'] === true && $result['filename'])
        {
            @file_put_contents($result['filename'], '<?php exit;?>' . time() . serialize($data));
            clearstatcache();
        }
    }

    /* 获取 SQL 语句中最后更新的表的时间，有多个表的情况下，返回最新的表的时间 */
    function table_lastupdate($tables)
    {
        if ($this->link_id === NULL)
        {
            $this->connect($this->settings['dbhost'], $this->settings['dbuser'], $this->settings['dbpw'], $this->settings['dbname'], $this->settings['charset'], $this->settings['pconnect']);
            $this->settings = array();
        }

        $lastupdatetime = '0000-00-00 00:00:00';

        $tables = str_replace('`', '', $tables);
        $this->mysql_disable_cache_tables = str_replace('`', '', $this->mysql_disable_cache_tables);

        foreach ($tables AS $table)
        {
            if (in_array($table, $this->mysql_disable_cache_tables) == true)
            {
                $lastupdatetime = '2037-12-31 23:59:59';

                break;
            }

            if (strstr($table, '.') != NULL)
            {
                $tmp = explode('.', $table);
                $sql = 'SHOW TABLE STATUS FROM `' . trim($tmp[0]) . "` LIKE '" . trim($tmp[1]) . "'";
            }
            else
            {
                $sql = "SHOW TABLE STATUS LIKE '" . trim($table) . "'";
            }
            $result = mysql_query($sql, $this->link_id);

            $row = mysql_fetch_assoc($result);
            if ($row['Update_time'] > $lastupdatetime)
            {
                $lastupdatetime = $row['Update_time'];
            }
        }
        $lastupdatetime = strtotime($lastupdatetime) - $this->timezone + $this->timeline;

        return $lastupdatetime;
    }

    function get_table_name($query_item)
    {
        $query_item = trim($query_item);
        $table_names = array();

        /* 判断语句中是不是含有 JOIN */
        if (stristr($query_item, ' JOIN ') == '')
        {
            /* 解析一般的 SELECT FROM 语句 */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?(?:\s*,\s*(?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?)*)/is', $query_item, $table_names))
            {
                $table_names = preg_replace('/((?:`?\w+`?\s*\.\s*)?`?\w+`?)[^,]*/', '\1', $table_names[1]);

                return preg_split('/\s*,\s*/', $table_names);
            }
        }
        else
        {
            /* 对含有 JOIN 的语句进行解析 */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)(?:(?:\s*AS)?\s*`?\w+`?)?.*?JOIN.*$/is', $query_item, $table_names))
            {
                $other_table_names = array();
                preg_match_all('/JOIN\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)\s*/i', $query_item, $other_table_names);

                return array_merge(array($table_names[1]), $other_table_names[1]);
            }
        }

        return $table_names;
    }

    /* 设置不允许进行缓存的表 */
    function set_disable_cache_tables($tables)
    {
        if (!is_array($tables))
        {
            $tables = explode(',', $tables);
        }

        foreach ($tables AS $table)
        {
            $this->mysql_disable_cache_tables[] = $table;
        }

        array_unique($this->mysql_disable_cache_tables);
    }
}

?>