<?php
namespace GouuseCore\Helpers;

class FormHelper
{
    /*
     * __get_data(取值数组,数组下标,不存在时设定的默认值)
     */
    public static function __getData($data, $key, $value = '')
    {
        $key_array = explode(',', $key);
        foreach ($key_array as $v) {
            if (isset($data[$v])) {
                $data = $data[$v];
            } else {
                $data = $value;
            }
        }
        return $data;
    }
    
    /*
     * __check_in_data(要检查的值, 取值范围数组,不在取值范围内设定一个默认值);
     */
    public static function __checkInData($value, $data, $default = '')
    {
        $re_data = $default;
        if (in_array($value, $data)) {
            $re_data = $value;
        }
        return $re_data;
    }
    
    /**
     * crm 格式化金额
     * @param unknown $money
     * @return number
     */
    public static function parseMoney($money)
    {
        $money = str_replace(array(',','￥'), '', $money);
        $money = abs(floatval($money));
        $money = sprintf("%.2f", $money);
        return $money;
    }
}
