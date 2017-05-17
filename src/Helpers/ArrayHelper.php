<?php
namespace GouuseCore\Helpers;

class ArrayHelper
{
    public static function filterArray($keys, $data, $signname = 'keep')
    {
        if (isarray($keys) == false) {
            $keys = explode(",", $keys);
        }
        $sign = $signname == "keep" ? false : true;
        foreach ($data as $index => $indexdata) {
            if (inarray($index, $keys) == $sign) {
                unset($data[$index]);
            }
        }
        return $data;
    }
    
    public static function filterMultiArray($keys, $data, $signname = 'keep')
    {
        foreach ($data as $index => $singledata) {
            $data[$index] = self::filterarray($keys, $singledata, $signname);
        }
        return $data;
    }
    
    /**
     * [changekeyarray 数组中，以 $filedid(唯一) 字段来替代key]
     * @param [type] $array    [description]
     * @param [type] $fieldid [description]
     */
    public static function changeKeyArray($array, $fieldid)
    {
        $newarray = array();
        foreach ($array as $value) {
            $newarray[$value[$fieldid]] = $value;
        }
        return $newarray;
    }
    /**
     * [reducearray 对一个多维的2维数组进行降维]
     * @param  [type] $array [description]
     * @param  [type] $key   [description]
     * @param  [type] $filed [description]
     * @return [type]        [description]
     */
    public static function reduceArray($array, $key, $filed, $prefix = "")
    {
        $newarray = array();
        if (isarray($array) == true) {
            foreach ($array as $singlevalue) {
                if (isset($singlevalue[$key]) && isset($singlevalue[$filed])) {
                    $newarray[$prefix . $singlevalue[$key]] = $singlevalue[$filed];
                }
            }
        }
        return $newarray;
    }

    public static function arrayOrderBy()
    {
        $args = funcgetargs();
        $data = arrayshift($args);
        foreach ($args as $n => $field) {
            if (isstring($field)) {
                $tmp = array();
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = & $data;
        calluserfuncarray('arraymultisort', $args);
        return arraypop($args);
    }
    public static function arrayKey2Value($source, $keys, $sign = ",", $keepkey = false)
    {
        $val = array();
        $arraykeys = isarray($keys) == false ? explode($sign, $keys) : $keys;
        foreach ($arraykeys as $singlekey) {
            if (isset($source[$singlekey])) {
                if ($keepkey == true) {
                    $val[$singlekey] = $source[$singlekey];
                } else {
                    $val[] = $source[$singlekey];
                }
            }
        }
        return $val;
    }
    public static function getFieldSum($array, $field)
    {
        $total = 0;
        foreach ($array as $singleinfo) {
            if (isset($singleinfo[$field]) == true) {
                $total = $total + $singleinfo[$field];
            } else {
                continue;
            }
        }
        return $total;
    }
    //回调试用
    public static function removeEmptyValue($a)
    {
        return $a != "";
    }
    
    //2维数组猎取某一个字段的集合
    public static function arrayListByField($array, $field)
    {
        $returnlist = array();
        foreach ($array as $singlearray) {
            $returnlist[] = $singlearray[$field];
        }
        return $returnlist;
    }
    //取$key之后的部分
    public static function arrayKeyEnd($array, $findkey)
    {
        $findkey = strrev($findkey);
        if (arraykeyexists($findkey, $array) == true) {
            $start = 0;
            $newarray = array();
            foreach ($array as $key => $val) {
                if ($start == 0) {
                    if ($key != $findkey) {
                        continue;
                    } else {
                        $start = 1;
                    }
                } else {
                    $newarray[$key] = $val;
                }
            }
            return $newarray;
        } else {
            return $array;
        }
    }
    public static function findInArray($array, $find, $field)
    {
        foreach ($array as $val) {
            $allmatch = 1;
            foreach ($find as $findname => $findval) {
                if ($val[$findname] != $findval) {
                    $allmatch = 0;
                    break;
                }
            }
            if ($allmatch == 1) {
                return $val[$field];
            }
        }
        return "";
    }
    //把一个多维数组，按field 顺序建一个新的多维数组，层数为arrayfield的个数
    public static function extendedArray($array, $fields, $clear = 1)
    {
        $arrayfield = explode(",", $fields);
        $arrayfieldlength = count($arrayfield);
        $tmp = array();
        foreach ($array as $singleinfo) {
            $next = & $tmp;
            foreach ($arrayfield as $singlefield) {
                $v = $singleinfo[$singlefield];
                if (isset($next[$v]) == false) {
                    $next[$v] = array();
                }
                $next = & $next[$v];
                if ($clear == 1) {
                    unset($singleinfo[$singlefield]);
                }
            }
            $next[] = $singleinfo;
        }
        return $tmp;
    }
    
    /*
     * 从{
     1: "个体客户",
     2: "小微客户（1-50人）",
     }
     * 转换为
     [
     {
     key: 1,
     name: "个体客户"
     },
     {
     key: 2,
     name: "小微客户（1-50人）"
     }
     ]
     */
    public static function changeArrayKeyName($redata)
    {
        $renewdata = array();
        $redata = $redata;
        foreach ($redata as $k => $v) {
            $renewdata[]=array('key' => $k, 'name' => $v);
        }
        return $renewdata;
    }
    //数组根据第一key值唯插入
    public static function arrayInsertAfterKey($arraysource, $arrayinsert, $key)
    {
        $k = $key;
        $arrayresult=array();
        foreach ($arraysource as $loopkey => $singledata) {
            $arrayresult[$loopkey]=$singledata;
            if ($loopkey == $k) {
                foreach ($arrayinsert as $insertkey => $insertdata) {
                    $arrayresult[$insertkey]=$insertdata;
                }
            }
        }
        return $arrayresult;
    }
}
