<?php
namespace GouuseCore\Libraries;

use GouuseCore\Helpers\OptionHelper;
// 引入数据库包
use Illuminate\Support\Facades\DB;

/**
 * 将数据放入缓存中
 * @author zhangyubo
 *
 */
class DBCacheLib extends Lib
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 *
	 * @param unknown $model_names 模型名字,组合查询传多个数组
	 * @param array $where 条件
	 * @param unknown $array_cache 缓存
	 * @param unknown $sql_type 0只返回数组 1数组+总行数 2总行数
	 * @return unknown|array[]|unknown[]|number[]
	 */
	public function get($model_names, $extra = [], $array_cache = [])
	{
		$where = $extra['where'] ?? [];//条件语句
		$need_field = $extra['need_field'] ?? [];//要查询的字段
		/**
		 * [['model' => 'AppModel', 'on' => $where]]
		 * @var Ambiguous $join
		 */
		$join = $extra['join'] ?? [];//联表查
		$left_join = $extra['left_join'] ?? [];//联表查
		$sort = $extra['sort'] ?? "";
		$group_by = $extra['group_by'] ?? "";
		$limit = $extra['limit'] ?? [];
		list ($group_cache_key, $cache_time) = $array_cache;
		if ($cache_time > 0) {
			ksort($extra);
			$cache_key = $group_cache_key . "|" . md5(json_encode($model_names)) . '|' . md5(json_encode($extra))  . __FUNCTION__;
			$cache_data = $this->CacheLib->get($cache_key);
			if ($cache_data) {
				return $cache_data;
			}
		}
		$need_field_is_empty = false;
		if (empty($need_field)) {
			$need_field_is_empty = true;
		}
		$models = [];
		$tables = [];
		if (is_array($model_names)) {
			//多个model联表查
			/**
			 * [['model' => 'MemberModel', 'as' => 'user'],['model' => 'CompanyModel', 'as' => 'company']] or ['model' => 'MemberModel', 'as' => 'user']
			 */
			if (isset($model_names['model'])) {
				//一维数组
				$models[isset($model_names['as']) ? $model_names['as'] : $model_names['model']] = $this->{$model_names['model']};
				$tables[$model_names['model']] = isset($model_names['as']) ? ($this->{$model_names['model']}->getTable() . ' as ' . $model_names['as']) : $this->{$model_names['model']}->getTable();
				if ($need_field_is_empty) {
					$field = $this->{$model_names['model']}->getTableFileds();
					$need_field = implode(',', $field['field_list']);
				}
				
			} else {
				//多维数组
				foreach ($model_names as $row_model) {
					$models[isset($row_model['as']) ? $row_model['as'] : $row_model['model']] = $row_model['load_obj'] = $this->{$row_model['model']};
					$tables[$row_model['model']] = isset($row_model['as']) ? ($this->{$row_model['model']}->getTable() . ' as ' . $row_model['as']) : $this->{$row_model['model']}->getTable();
					if ($need_field_is_empty) {
						$field = $this->{$model_names}->getTableFileds();
						$need_field = array_merge($need_field, $field['field_list']);
					}
				}
				if ($need_field_is_empty) {
					$need_field = implode(',', $need_field);
				}
			}
		} else {
			$models[$model_names] = $this->{$model_names};
			$tables[$model_names] = $this->{$model_names}->getTable();
			if ($need_field_is_empty) {
				$field = $this->{$model_names}->getTableFileds();
				$need_field = implode(',', $field['field_list']);
			}
		}
		$limit_sql = "";
		if ($limit) {
			if (is_array($limit) && count($limit) == 2) {
				$limit_sql = " limit " . intval($limit[0]) . ',' . intval($limit[1]);
			} else {
				$limit_sql = " limit ".intval($limit[0]);
			}
		} else {
			$limit_sql = " limit 1";
		}
		$order_sql = "";
		if ($sort) {
			$order_sql = " order by " . $sort;
		}
		$group_sql = "";
		if ($group_by) {
			$group_sql= " group by " . $group_by;
		}
		list ($where_sql, $val) = $this->parseWhere($where);
		
		$out_data = [];
		
		$sql = "select " . $need_field . ' from ' . implode(',', $tables) . ' where '
				. $where_sql
				. $group_sql
				. $order_sql
				. $limit_sql;
				// 执行sql
				$rows = DB::select($sql, $val);
				$num = count($rows);
				$out_data = $rows;
				
				if ($cache_time > 0) {
					$this->CacheLib->saveWithKey($group_cache_key, $cache_key, $out_data, $cache_time);
				}
				return $out_data;
	}
	
	public function total($model_names, $extra = [], $array_cache = [])
	{
		$where = $extra['where'] ?? [];//条件语句
		$need_field = $extra['need_field'] ?? [];//要查询的字段
		/**
		 * [['model' => 'AppModel', 'on' => $where]]
		 * @var Ambiguous $join
		 */
		$join = $extra['join'] ?? [];//联表查
		$left_join = $extra['left_join'] ?? [];//联表查
		$group_by = $extra['group_by'] ?? "";
		list ($group_cache_key, $cache_time) = $array_cache;
		if ($cache_time > 0) {
			ksort($extra);
			$cache_key = $group_cache_key . "|" . md5(json_encode($model_names)) . '|' . md5(json_encode($extra)) . $sql_type . __FUNCTION__;
			$cache_data = $this->CacheLib->get($cache_key);
			if ($cache_data) {
				return $cache_data;
			}
		}
		$need_field_is_empty = false;
		if (empty($need_field)) {
			$need_field_is_empty = true;
		}
		$models = [];
		$tables = [];
		if (is_array($model_names)) {
			//多个model联表查
			/**
			 * [['model' => 'MemberModel', 'as' => 'user'],['model' => 'CompanyModel', 'as' => 'company']] or ['model' => 'MemberModel', 'as' => 'user']
			 */
			if (isset($model_names['model'])) {
				//一维数组
				$models[isset($model_names['as']) ? $model_names['as'] : $model_names['model']] = $this->{$model_names['model']};
				$tables[$model_names['model']] = isset($model_names['as']) ? ($this->{$model_names['model']}->getTable() . ' as ' . $model_names['as']) : $this->{$model_names['model']}->getTable();
				if ($need_field_is_empty) {
					$field = $this->{$model_names['model']}->getTableFileds();
					$need_field = implode(',', $field['field_list']);
				}
				
			} else {
				//多维数组
				foreach ($model_names as $row_model) {
					$models[isset($row_model['as']) ? $row_model['as'] : $row_model['model']] = $row_model['load_obj'] = $this->{$row_model['model']};
					$tables[$row_model['model']] = isset($row_model['as']) ? ($this->{$row_model['model']}->getTable() . ' as ' . $row_model['as']) : $this->{$row_model['model']}->getTable();
					if ($need_field_is_empty) {
						$field = $this->{$model_names}->getTableFileds();
						$need_field = array_merge($need_field, $field['field_list']);
					}
				}
				if ($need_field_is_empty) {
					$need_field = implode(',', $need_field);
				}
			}
		} else {
			$models[$model_names] = $this->{$model_names};
			$tables[$model_names] = $this->{$model_names}->getTable();
			if ($need_field_is_empty) {
				$field = $this->{$model_names}->getTableFileds();
				$need_field = implode(',', $field['field_list']);
			}
		}
		
		$group_sql = "";
		if ($group_by) {
			$group_sql= " group by " . $group_by;
		}
		list ($where_sql, $val) = $this->parseWhere($where);
		
		$sql = 'select count(*) as total from ' . implode(',', $tables) . ' where '
				. $where_sql
				. $group_sql
				. " limit 1";
				$result = DB::select($sql, $val);
				$out_put_data= $result[0]["total"] ?? 0;
				
				if ($cache_time > 0) {
					$this->CacheLib->saveWithKey($group_cache_key, $cache_key, $out_put_data, $cache_time);
				}
				return $out_put_data;
	}
	
	private function parseWhere($where)
	{
		$where_list = "1";
		$val=array();
		
		if (is_object($where) == true) {
			$where = get_object_vars($where);
		}
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
				} else {
					if (strpos($v["value"], '.')) {
						$where_list = $where_list ? $where_list . " and "
								. $k . $v["sign"] . $v['value'] : $k . $v["sign"] . "?";
					} else {
						$where_list = $where_list ? $where_list . " and "
								. $k . $v["sign"] . "?" : $k . $v["sign"] . "?";
						$val[] = $v['value'];
					}
				}
			}
		}
		return array($where_list, $val);
	}
	
}
