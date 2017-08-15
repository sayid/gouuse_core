<?php
/**
 * @author zhangyubo
 *
 */
namespace GouuseCore\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Auth;
use GouuseCore\Core\BaseGouuse;

abstract class BaseModel extends BaseGouuse
{
    
    /**
     * 表名
     * @var string
     */
    protected $table;
    
    protected $mysql_dont_plain = 0;
    
    
    public function __construct(array $attributes = [])
    {
        parent::__construct();
    }
    
    public function getTable()
    {
    	return $this->table;
    }
    
    /**
     * 获取 mysql表的字段
     * @param  [type] $table [description]
     * @return [type]        [description]
     */
    private function checkTable($table)
    {
        $table = $table ? $table : $this->table;
        if ($table == "") {
            echo "getTableFileds error:Empty tables;";
            exit;
        }
        return strtolower($table);
    }
    private function parseWhere($where, $table)
    {
        $where_list = "1";
        $val=array();
    
        if (is_object($where) == true) {
            $where = get_object_vars($where);
        }
        $where = $this->filter($where, $table);
        if (count($where) > 0) {
            foreach ($where as $k => $v) {
                if ($v["sign"] == "in") {
                    $fileds=" (".substr(str_repeat("?,", count($v['value'])), 0, -1).")";
                    $where_list = $where_list ? $where_list . " and " . $k
                    . " " .$v["sign"] . $fileds : $k . " " . $v["sign"] . $fileds;
                    $val = array_merge($val, $v['value']);
                } elseif ($v["sign"] == "not in") {
                    $fileds = " (".substr(str_repeat("?,", count($v['value'])), 0, -1).")";
                    $where_list = $where_list ? $where_list . " and " . $k
                    . " " .$v["sign"] . $fileds : $k . " " . $v["sign"] . $fileds;
                    $val = array_merge($val, $v['value']);
                } elseif ($v["sign"] == "between") {
                    $str = $k." between ? and ?";
                    $where_list = $where_list ? $where_list . " and ". $str : $str;
                    $val[] = $v['value'][0];
                    $val[] = $v['value'][1];
                } elseif ($v["sign"] == "more" && count($v['value']>1)) {
                    foreach ($v['value'] as $k_more => $v_more) {
                        $where_list = $where_list ? $where_list . " and "
                            . $k . $v_more["sign"] . "?" : $k . $v_more["sign"] . "?";
                        $val[] = $v_more['value'];
                    }
                } else {
                    $where_list = $where_list ? $where_list . " and "
                        . $k . $v["sign"] . "?" : $k . $v["sign"] . "?";
                    $val[] = $v['value'];
                }
            }
        }
        return array($where_list, $val);
    }
    
    public function getTableFileds($table = "")
    {
        $table=$this->checkTable($table);
    
        $cache_key = env('SERVICE_ID') . ":getTableFileds_".$table;
        $cache_data = Cache::get($cache_key);
        if (!$cache_data) {
            $this->mysql_dont_plain=1;
            $sql="select COLUMN_NAME,COLUMN_KEY from 
                INFORMATION_SCHEMA.COLUMNS where table_name =? and  table_schema =?";
            $rows=DB::select($sql, array($table, env('DB_DATABASE')));
            $num = count($rows);
            if ($rows) {
                $field_list = array();
                $field_pri = "";
                for ($i = 0; $i < $num; $i++) {
                    $field_list[] = $rows[$i]['COLUMN_NAME'];
    
                    if ($rows[$i]['COLUMN_KEY']=="PRI") {
                        $field_pri = $rows[$i]['COLUMN_NAME'];
                    }
                }
                 
                $cache_data = array();
                $cache_data["field_list"] = $field_list;
                $cache_data["field_pri"] = $field_pri;
                Cache::put($cache_key, $cache_data, 36000);
            }
        }
        return $cache_data;
    }
    public function filter($data, $table = "")
    {
        $table = $this->checkTable($table);
        extract($this->getTableFileds($table));
        if ($field_list == "") {
            echo $table." empty!";
            exit;
        }
        if (is_object($data) == true) {
            $data=get_object_vars($data);
        }
        if (is_array($data) == true) {
            foreach ($data as $index => $val) {
                $index_format = str_replace(array("+", "-"), array("", ""), $index);
                if (in_array($index_format, $field_list) == false && is_numeric($index_format) == false) {
                	throw new \Exception($index_format . ' is not a field of ' . $table);
                	unset($data[$index]);
                }
            }
            return $data;
        } else {
            return array();
        }
    }
    /**
     * 插入数据
     * @param [type] $table [description]
     * @param [type] $data  [description]
     */
    public function add($data)
    {
        $table="";
        $table=$this->checkTable($table);
    
        $data=$this->filter($data, $table);
        if (count($data) > 0) {
            return DB::table($table)->insertGetId($data);
        } else {
            return "";
        }
    }
    
    /**
     * 批量添加
     * @param $data 多维数组（批量添加数据不能超过100条）
     * @return string
     */
    public function addAll($data)
    {
    	$table="";
    	$table=$this->checkTable($table);
    	if (count($data) > 100) {
    		return false;
    	}
    	foreach ($data as $key => $val) {
    		$data[$key] = $this->filter($val, $table);
    	}
    	
    	if (count($data) > 0) {
    		return DB::table($table)->insert($data);
    	} else {
    		return "";
    	}
    }
    
    /**
     * 修改数据
     * @param  [type] $table [description]
     * @param  [type] $data  [description]
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    public function update($data, $where)
    {
        $table="";
        $table=$this->checkTable($table);
    
        $data=$this->filter($data, $table);
        $where=$this->filter($where, $table);
    
        if (count($data) > 0) {
            $val = array();
            $fileds = "";
            $where_list = "";
            foreach ($data as $k => $v) {
                $end=substr($k, -1);
    
                if (in_array($end, array("+", "-")) == true) {
                    $this_field=substr($k, 0, -1);
                    $fileds = $fileds ? $fileds . "," . $this_field . "=`".$this_field."`".$end
                    ."?" : $this_field . "=`".$this_field."`".$end."?";
                } else {
                    $fileds = $fileds ? $fileds . "," . $k . "=?" : $k . "=?";
                }
                $val[] = $v;
            }
    
            list($where_list, $val_where) = $this->parseWhere($where, $table);
            if ($where_list == "1") {
                $this->fullTableWriteErrorReport('update:'.$table);
            }
    
            $sql = "update " . $table . " set " . $fileds . " where " . $where_list;
            return DB::update($sql, array_merge($val, $val_where));
        } else {
            return "";
        }
    }
    /**
     * 根据条件删除数据
     * @param  [string] $table [表名]
     * @param  [array] $where [条件]
     * @return [string]
     */
    public function delete($where)
    {
        $table = "";
        $table = $this->checkTable($table);
        $where = $this->filter($where, $table);
        list($where_list, $val) = $this->parseWhere($where, $table);
    
        if ($where_list == "1") {
            //记录错误日志 不能整表删除
            $this->fullTableWriteErrorReport('delete:'.$table);
        }
    
        if ($where_list) {
            $sql = "delete from " . $table . " where " . $where_list;
            return DB::delete($sql, $val);
        } else {
            return "";
        }
    }
    
    public function replace($data, $where, $table = "", $frequency_control = 0)
    {
        $table=$this->checkTable($table);
    
        $data_key = $data;
        $where_key = $where;
    
        natsort($data_key);
        $where_key = sort($where_key);
        $key = "db_replace_".md5(http_build_query($data_key))."_".md5(json_encode($where_key));
        $value = Cache::get($key);
        if ($frequency_control == 1 || $value == false) {
            $affected_rows = $this->update($data, $where, $table);
            if ($affected_rows == 0) {
                $affected_rows = $this->add($data, $table);
            }
            Cache::put($key, 1, 2);//3秒内不允许执行同样的操作
    
            return $affected_rows;
        }
    
    
    }
    
    ////////////////////////////////////////////扩展的操作部分
    public function getById($id, $id_field = "", $table = "", $need_field = "", $is_lock = "0")
    {
        $table = $this->checkTable($table);
    
        extract($this->getTableFileds($table));
        if ($id_field == "") {
            $id_field = $field_pri;
        }
        if ($need_field == "") {
            $need_field = join(",", $field_list);
        }
    
        $lock_str = "";
        if ($is_lock == 1) {
            $lock_str = "FOR UPDATE";
        }
        $sql = "select ".$need_field." from ".$table." where ".$id_field." = ? limit 1 ".$lock_str;
        $rows = DB::select($sql, array($id));
        return $rows[0] ?? null;
    }
    
    /**
     * 从缓存里面读取
     * @param unknown $id
     * @param string $id_field
     * @param number $expire_time
     * @return NULL|unknown
     */
    public function getByIdFromCache($id, $id_field = "", $expire_time = 3600)
    {
    	$table = $this->checkTable($table);
    	
    	extract($this->getTableFileds($table));
    	if ($id_field == "") {
    		$id_field = $field_pri;
    	}
    	
    	$need_field = join(",", $field_list);
    	$key == get_class($this) . __FUNCTION__ . $id_field . '_' . $id;
    	$data = $this->CacheLib->get($key);
    	if (empty($data)) {
    		$data = $this->getById($id, $id_field);
    		$this->CacheLib->set($key, $data, $expire_time);
    	}
    	if (!empty($data)) {
    		//将缓存key写入数据方便清理
    		$data['cache_key'] = $key;
    	}
    	return $data;
    }
    
    public function updateById($id, $data= "", $table = "")
    {
    	$table = $this->checkTable($table);
    	
    	extract($this->getTableFileds($table));
    	$id_field = $field_pri;
    	
    	$where = [];
    	$where[$field_pri] = array(
    			"sign" => "=",
    			"value" => $id
    	);
    	return $this->update($data, $where);
    }
    
    public function count($where = "", $distinct = "")
    {
        $table = "";
        $table = $this->checkTable($table);
        list($where_list,$val) = $this->parseWhere($where, $table);
    
        if ($where_list) {
            if (!empty($distinct)) {
                $sql="select count(distinct ".$distinct.") as total from ".$table." where ".$where_list." limit 1";
            } else {
                $sql="select count(1) as total from ".$table." where ".$where_list." limit 1";
            }
            $num = DB::select($sql, $val);
        } else {
            $sql = "select count(1) as total from ".$table." limit 1";
            $num = DB::select($sql);
        }
        return $num[0]['total'] ?? 0;
    }
    
    
    public function sum($sum_field, $where = "", $table = "")
    {
        $table = $this->checkTable($table);
        list($where_list, $val) = $this->parseWhere($where, $table);
    
        if ($where_list) {
            $sql = "select sum(".$sum_field.") as total from ".$table." where ".$where_list." limit 1";
            $num = DB::select($sql, $val);
        } else {
            $sql = "select sum(".$sum_field.") as total from ".$table." limit 1";
            $num = DB::select($sql);
        }
        return $num;
    }
    
    
    /**
     * 封装getSelectAll 以某个字段查询
     * Enter description here ...
     * @param unknown_type $need_field
     * @param unknown_type $value
     * @param unknown_type $field
     * @param unknown_type $offset
     * @param unknown_type $perpage
     * @param unknown_type $order_by
     * @param unknown_type $distinct
     * @param unknown_type $table
     */
    public function getSelectAllByFiled(
        $need_field,
        $value,
        $field,
        $offset = 0,
        $perpage = 0,
        $order_by = "",
        $distinct = false,
        $table = ""
    ) {
        if (empty($field)) {
            return;
        }
        $where = array();
        $where[$field] = array('sign'=>'=','value' => $value);
        return $this->getSelectAll($need_field, $where, $offset, $perpage, $order_by, $distinct, $table);
    }
    
    public function getSelectAll(
        $need_field = "",
        $where = "",
        $offset = 0,
        $perpage = 0,
        $order_by = "",
        $distinct = false,
        $table = ""
    ) {
        $sql_limit = "";
        $sql_distinct = "";
        $sql_order = "";
        $sql_where = "";
        $table=$this->checkTable($table);
    
        extract($this->getTableFileds($table));
    
        if ($need_field == "") {
            $need_field = join(",", $field_list);
        }
    
        list($where_list, $val) = $this->parseWhere($where, $table);
    
        if ($where_list) {
            $sql_where=" where ".$where_list;
        }
        if ($order_by) {
            $sql_order=" order by ".$order_by;
        }
        if ($distinct == true) {
            $sql_distinct = "distinct";
        }
        if ($perpage>0) {
            $sql_limit = " limit " . $offset . "," .$perpage;
        }
        $sql = "select " . $sql_distinct ." ".$need_field . " from " . $table . $sql_where . $sql_order . $sql_limit;
        $rows = DB::select($sql, $val);
        return $rows;
    }
    
    public function getOne($need_field = "", $where = "", $table = "")
    {
        $sql_where = "";
        $table = $this->checkTable($table);
    
        extract($this->getTableFileds($table));
    
        if ($need_field == "") {
            $need_field = join(",", $field_list);
        }
    
        list($where_list, $val) = $this->parseWhere($where, $table);
    
        if ($where_list) {
            $sql_where=" where ".$where_list;
        }
    
        $sql_limit=" limit 1";
    
        $sql="select ".$need_field." from ".$table.$sql_where.$sql_limit;
        $rows=DB::select($sql, $val);
        return isset($rows[0])?$rows[0]:array();
    }
    
    
    public function fullTableWriteErrorReport($type)
    {
        $error_message = "url:http://".@$_SERVER['HTTP_HOST'].@$_SERVER["REQUEST_URI"];
        $error_message = $error_message."\r\n";
        $error_message = $error_message."compoany_id:".@$this->company_info['company_id'].
        "--member_id:".@$this->company_info['member_id'];
        $error_message = $error_message."\r\n";
        $error_message = $error_message.print_r($_POST, true);
        $error_message = $error_message."\r\n";
        $error_message = $error_message."time:".date("Y-m-d H:i:s");
        $error_message = $error_message."\r\n----------------------------\r\n\r\n\r\n\r\n";
    
        //file_put_contents(WRITEABLE_DIR.'error/'.$type.'_all_data.txt', $error_message, FILE_APPEND);
        Log::error($error_message);
        echo "Not allow to ".$type." all data!";
        exit;
    }
}
