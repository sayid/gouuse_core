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
    
    /**
     * 表单获取数据
     * @param $request 底层request对像
     * @param $array 要获取的字段数组
     * @return array
     */
    public static function formPost($request, $array, $is_filter = true){
        $new_array=array();
        foreach($array as $key=>$value){
            $post = $request[$value];
            $$value = is_array($post) ? $post : trim($post);
            if($is_filter){
                if($$value !== ''){
                    $new_array[$value]=$$value;
                }
            }else{
                $new_array[$value]=$$value;
            }
        }
        return $new_array;
    }
}
