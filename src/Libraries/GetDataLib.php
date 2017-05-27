<?php
namespace GouuseCore\Libraries;

use GouuseCore\Helpers\OptionHelper;
// 引入数据库包
use Illuminate\Support\Facades\DB;

class GetDataLib extends Lib
{

    public $ci;

    public $no_sort = 0;

    public $field_type = "1";
    // 1-所有字段，2-取‘1’
    public function __construct()
    {
        // echo 1;die;
        // $this->ci=& get_instance();
    }

    public function start($data_name, $data, $array_cache, $get_total_type = 1, $return_out_data = 1)
    {
        // $this->app->aa();die;
        $get_data = new GetDataLib();
        $sort_string = "";
        $config = OptionHelper::getOption($data_name, "data"); // 修改
        list ($group_cache_key, $cache_time) = $array_cache;
        if ($cache_time > 0) {
            ksort($data);
            $cache_key = $group_cache_key . "|" . md5(http_build_query($data));
            $cache_data = $this->CacheLib->get($cache_key);
            if ($cache_data["do"] == 1) {
                return $cache_data["data"];
            }
        }
        // $this->CacheLib->put('key', 'value', 10);
        // echo $this->CacheLib->get('key');die;
        // 构造表
        list ($table_list, $base_join_condition) = $this->makeTable($config["connection_mode"]);
        // 构造字段
        list ($field_list, $formats) = $this->makeField($config["field"], $data);
        // 构造where条件
        list ($condition_list, $condition_value) =
        $this->makeCondition($config["condition"], $base_join_condition, $data);
        // 构造order条件
        $sort = $this->makeSort($config["sort"], $data);
        if (strlen($sort) > 0) {
            $sort_string = " order by " . $sort;
        }
        // 构造limit
        $limit = $this->makeLimit($data["p"], $data["n"]);
        $sql = "select " . join(",", $field_list) . " from " .
        $table_list . " where " . join(" and ", $condition_list) . $sort_string . $limit;
        /*
         * if($data_name=="attendence_action"){
         * echo $sql;print_r($condition_value);
         * exit;
         * }
         */
        // echo $sql;die;
        if ($return_out_data == 1) {
            // 执行sql
            $rows = DB::select($sql, $condition_value);
            // p($rows);die;
            $num = count($rows);
            $out_data = $rows;
            // $rs=$this->ci->db->query($sql,$condition_value);
            // $num=$rs->GetRowCount();
            // $out_data=$rs->FetchAllInArray($num);
            if ($num > 0 && is_array($formats) == true) {
                // p($formats);
                foreach ($formats as $format_field => $format_method) {
                    if (strpos($format_method, "option:") === false) {
                        $is_option = false;
                    } else {
                        $is_option = true;
                        $is_multiple_option = false;
                        if (strpos($format_method, "multiple_option:") === false) {
                            $option_name = substr($format_method, 7);
                        } else {
                            $is_multiple_option = true;
                            $option_name = substr($format_method, 16);
                        }
                        $config = OptionHelper::getOption($option_name);
                    }
                    for ($i = 0; $i < $num; $i ++) {
                        if ($is_option === false) {
                            /*
                             * delete sling 2013-09-25
                             * if(method_exists(get_data, $format_method)==true){
                             */
                            if (method_exists($get_data, $format_method) == true) { // update sling 2013-09-25
                                $out_data[$i][$format_field] = $this->{$format_method}($out_data[$i][$format_field]);
                            } else {
                                if (strpos($format_method, "|") === false) {
                                    $out_data[$i][$format_field] = $format_method($out_data[$i][$format_field]);
                                } else {
                                    $array_funs = explode("|", $format_method);
                                    rsort($array_funs);
                                    foreach ($array_funs as $single_func) {
                                        $out_data[$i][$format_field] = $single_func($out_data[$i][$format_field]);
                                    }
                                }
                            }
                        } else {
                            if ($is_multiple_option == false) { // 单选
                                $out_data[$i][$format_field] = isset($config[$out_data[$i][$format_field]]) ?
                                $config[$out_data[$i][$format_field]] : ''; // update sling 2013-09-25
                                /*
                                 * delete sling 2013-09-25
                                 * $out_data[$i][$format_field]=$config[$out_data[$i][$format_field]];
                                 */
                            } else { // 多选
                                $bin_str = decbin($out_data[$i][$format_field]);
                                $bin_str_length = strlen($bin_str);
                                $change_str = "";
                                for ($n = $bin_str_length; $n >= 0; $n --) {
                                    if ($bin_str[$n] == "1") {
                                        $change_str = $change_str ? $change_str . "," .
                                        $config[pow(2, $bin_str_length - $n - 1)] :
                                        $config[pow(2, $bin_str_length - $n - 1)];
                                    }
                                }
                                $out_data[$i][$format_field] = $change_str;
                            }
                        }
                    }
                }
            }
        } else {
            $out_data = array();
        }
        // p($out_data);die;
        // 总数
        if ($get_total_type == 0) { // 不取总数
            $out_data_total = 0;
        } elseif ($get_total_type == 1) { // 取总数
            $sql = "select count(*) as total from " . $table_list .
            " where " . join(" and ", $condition_list) . ' limit 1';
            $result = DB::select($sql, $condition_value);
            // p($rows);die;
            // $rs=$this->ci->db->query($sql,$condition_value);
            // $num=$rs->GetRowCount();
            // $result=$rs->FetchAllInArray($num);
            $out_data_total = $result[0]["total"];
        } elseif ($get_total_type == 2) { // 取部分
            $sql = "select count(*) as total from (select 1  from " . $table_list .
                " where " . join(" and ", $condition_list) .
                $this->make_limit($data["p"], $data["n"], 1, 8, 1, $data['n_for_total']) . ") as rows";
            $rs = $this->ci->db->query($sql, $condition_value);
            $num = $rs->GetRowCount();
            $result = $rs->FetchAllInArray($num);
            $out_data_total = $result[0]["total"];
        } elseif ($get_total_type == 3) { // 取多一条，判断是否有下一页
            $sql = "select count(*) as total from (select 1  from " . $table_list .
                " where " . join(" and ", $condition_list) .
                $this->make_limit($data["p"], $data["n"], 0, 0, 1, $data['n'] + 1) . ") as rows";
            $rs = $this->ci->db->query($sql, $condition_value);
            $num = $rs->GetRowCount();
            $result = $rs->FetchAllInArray($num);
            $out_data_total = $result[0]["total"];
        }
        $out_put_data = array(
            $out_data_total,
            $out_data
        );
        if ($cache_time > 0) {
            $this->CacheLib->saveWithKey($group_cache_key, $cache_key, array(
                "do" => 1,
                "data" => $out_put_data
            ), $cache_time);
        }
        return $out_put_data;
    }

    public function makeTable($config)
    {
        $table = "";
        $base_join_condition = array();
        foreach ($config as $table_short_name => $table_info) {
            switch ($table_info['way']) {
                case "main":
                    $table_str = $table_info["table_name"] . " as " . $table_short_name;
                    if ($table) {
                        $this->notice_error("main table repeat!");
                    } else {
                        $table = $table_str;
                    }
                    break;
                case "join":
                    $table_str = $table_info["table_name"] . " as " . $table_short_name;
                    if (! $table) {
                        $this->notice_error("join need a main table!");
                    } else {
                        $table = $table . "," . $table_str;
                    }
                    if (isset($table_info["join_target"]) && isset($table_info["join_field"])) {
                        $base_join_condition[] = $table_info["join_target"] . "=" .
                        $table_short_name . "." . $table_info["join_field"];
                    } else {
                        $this->notice_error("join need join_target and join_field");
                    }
                    break;
                case "left_join":
                    $table_str = $table_info["table_name"] . " as " .
                    $table_short_name . " on (" . $table_info["condition"] . ")";
                    if (! $table) {
                        $this->notice_error("left join neaad a main table!");
                    } else {
                        $table = $table . " left join " . $table_str;
                    }
                    break;
            }
        }
        return array(
            $table,
            $base_join_condition
        );
    }

    public function makeField($config, $data)
    {
        if ($this->field_type == "1") {
            $fields = array();
            $formats = array();
            if (is_array($config) == true && count($config) > 0) {
                foreach ($config as $table_short_name => $array_field) {
                    if (is_array($array_field) == true && count($array_field) > 0) {
                        foreach ($array_field as $single_field) {
                            // 可选字段处理，
                            if (isset($single_field['is_extra_field']) == true &&
                                $single_field['is_extra_field'] == "1" &&
                                !(isset($data['field:with_extra_field']) &&
                                is_array($data['field:with_extra_field']) &&
                                in_array($single_field["name"], $data['field:with_extra_field']))) {
                                continue;
                            }
                            if (isset($single_field['prefix_db_field']) == true) {
                                switch ($single_field['prefix_db_field']) {
                                    case "sum":
                                        $fields[] = "sum(" . $table_short_name . "." .
                                            $single_field["db_field"] . ") as " . $single_field["name"];
                                        break;
                                }
                            } else {
                                // 增加子查询支持
                                if (isset($single_field['ignore_table_name']) &&
                                $single_field['ignore_table_name'] == "1") { // 子查询不需要表名
                                    $fields[] = $single_field["db_field"] . " as " . $single_field["name"];
                                } else {
                                    $fields[] = ((isset($single_field["is_distinct"]) &&
                                        $single_field["is_distinct"] == 1) ? "DISTINCT " : "") .
                                    $table_short_name . "." . $single_field["db_field"] . " as " .
                                    $single_field["name"];
                                }
                            }
                            if ($single_field["format"]) {
                                $formats[$single_field["name"]] = $single_field["format"];
                            }
                        }
                    }
                }
                return array(
                    $fields,
                    $formats
                );
            } else {
                $this->notice_error("field config file empty!");
            }
        } elseif ($this->field_type == "2") { // 取1
            $this->field_type = 1;
            return array(
                array(
                    "1"
                ),
                ""
            );
        }
    }

    public function makeCondition($config, $base_join_condition, $data)
    {
        $conditions = array();
        $conditions = array_merge($conditions, $base_join_condition);
        $conditions_value = array();
        if (is_array($config) == true && count($config) > 0) {
            foreach ($config as $table_short_name => $array_condition) {
                if (is_array($array_condition) == true && count($array_condition) > 0) {
                    foreach ($array_condition as $single_condition) {
                        if (isset($data[$single_condition["name"]]) == true &&
                            $data[$single_condition["name"]] !== "") {
                            $field = $single_condition["field"] ?
                            $single_condition["field"] : $single_condition["name"];
                            if ($single_condition["exist"] == "must" ||
                                ($single_condition["exist"] == "assign" && isset($data[$single_condition["name"]]))) {
                                if ($single_condition["type"] == "in") {
                                    $in_data = is_array($data[$single_condition["name"]]) == false ?
                                    explode(",", $data[$single_condition["name"]]) : $data[$single_condition["name"]];
                                    $in_data_str = substr(str_repeat("?,", count($in_data)), 0, - 1); // 组合?,?,?的格式
                                    $conditions[] = $table_short_name . "." . $field . " " .
                                    $single_condition["type"] . "(" . $in_data_str . ")";
                                    $conditions_value = array_merge($conditions_value, $in_data);
                                } elseif ($single_condition["type"] == "child_query") {
                                    $conditions[] = $field;
                                    if (isset($single_condition["no_value_bind"]) == false) {
                                        $conditions_value =
                                        array_merge(
                                            $conditions_value,
                                            is_array($data[$single_condition["name"]]) ?
                                            $data[$single_condition["name"]] :
                                            array($data[$single_condition["name"]])
                                        );
                                    }
                                } elseif ($single_condition["type"] == "is") {
                                    $conditions[] = $table_short_name . "." . $field . " is " .
                                    ($data[$single_condition["name"]] == "1" ? "null" : "not null");
                                } else {
                                    $conditions[] = $table_short_name . "." . $field . " " .
                                    $single_condition["type"] . " ?";
                                    $conditions_value[] = $data[$single_condition["name"]];
                                }
                            }
                        }
                    }
                }
            }
        }
        if (count($conditions) > 0) {
            return array(
                $conditions,
                $conditions_value
            );
        } else {
            return array(
                array(
                    "1=1"
                ),
                array()
            );
        }
    }

    public function makeSort($config, $data)
    {
        if ($this->no_sort == 1) {
            return array();
        } else {
            $sort = array();
            if (is_array($config) && count($config) > 0) {
                foreach ($config as $single_sort) {
                    switch ($single_sort["exist"]) {
                        case "must":
                            $sort[$single_sort["field"]] = $single_sort["table"] . "." .
                            $single_sort["field"] . " " . $single_sort["type"];
                            break;
                        case "assign":
                            if (isset($single_sort["name"]) == false) {
                                $single_sort["name"] = $single_sort["field"];
                            }
                            if (isset($data["order:" . $single_sort["name"]]) == true &&
                                $data["order:" . $single_sort["name"]] == 1) {
                                if ($single_sort["type"] == "rand") {
                                    $sort[$single_sort["field"]] = "rand()";
                                } else {
                                    $sort[$single_sort["field"]] = $single_sort["table"] . "." .
                                    $single_sort["field"] . " " . $single_sort["type"];
                                }
                            }
                    }
                }
                return join(",", array_values($sort));
            } else {
                return "";
            }
        }
    }

    public function makeLimit($p, $n, $get_more = 0, $more_page = 8, $usr_n_for_total = 0, $n_for_total = 0)
    {
        if ($n > 0) {
            if ($p < 1) {
                $p = 1;
            }
            if ($get_more == 0) {
                if ($usr_n_for_total == 1) {
                    return " limit " . ($p - 1) * $n . "," . $n_for_total;
                } else {
                    return " limit " . ($p - 1) * $n . "," . $n;
                }
            } else {
                return " limit " . ($p - 1) * $n . "," . ($n * $more_page);
            }
        } else {
            return "";
        }
    }

    public function noticeError($msg)
    {
        if (ENVIRONMENT !== "production") {
            trigger_error($msg, E_USER_ERROR);
        } else {
            header("Location:/");
        }
    }

    public function dateSimpleWithoutLeadingZeros($str)
    {
        return $str > 0 ? date("Y-n-j", $str) : "";
    }

    public function dateSimple($str)
    {
        return $str > 0 ? date("Y-m-d", $str) : "";
    }

    public function dateFull($str)
    {
        return $str > 0 ? date("Y-m-d H:i:s", $str) : "";
    }

    public function hourMinute($str)
    {
        if ($str > 0) {
            $hour = floor($str / 3600);
            $minute = floor(($str - $hour * 3600) / 60);
            return sprintf("%02d", $hour) . ":" . sprintf("%02d", $minute);
        } else {
            return "";
        }
    }

    public function noSpecialChars($str)
    {
        return htmlspecialchars($str);
    }

    public function noJs($str)
    {
        $str = preg_replace("/<script.*?>.*?<\/script>/is", "", $str);
        // todo 过滤on..事件
        return $str;
    }

    public function jsonDecode($str)
    {
        if (is_string($str) && strlen($str) > 0) {
            $array = json_decode($str, true);
            if (is_null($array) == true) {
                return $str;
            } else {
                return $array;
            }
        } else {
            return "";
        }
    }

    public function explode($str)
    {
        return $str == "" ? array() : preg_split("/[\-_, ]+/is", $str);
    }
    // 加密
    public function dataEncode($str)
    {
        $str = $this->ci->encrypt->encode($str, $this->ci->config->item('encryption_key'));
        return base64_url_encode($str);
        // $plaintext_string = $this->encrypt->decode(urldecode($str),'gouuse');解密
    }
}
