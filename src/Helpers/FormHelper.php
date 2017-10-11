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
            $post = $request[$value] ?? '';
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
    
    /**
     * 断点续传 下载
     * @param unknown $path
     * @param unknown $file
     * @return boolean
     */
   
    function download($path,$file) {
    	$real = $path.'/'.$file;
    	if(!file_exists($real)) {
    		return false;
    	}
    	$size = filesize($real);
    	$size2 = $size-1;
    	$range = 0;
    	if(isset($_SERVER['HTTP_RANGE'])) {   //http_range表示请求一个实体/文件的一个部分,用这个实现多线程下载和断点续传！
    		header('HTTP /1.1 206 Partial Content');
    		$range = str_replace('=','-',$_SERVER['HTTP_RANGE']);
    		$range = explode('-',$range);
    		$range = trim($range[1]);
    		header('Content-Length:'.$size);
    		header('Content-Range: bytes '.$range.'-'.$size2.'/'.$size);
    	} else {
    		header('Content-Length:'.$size);
    		header('Content-Range: bytes 0-'.$size2.'/'.$size);
    	}
    	header('Accenpt-Ranges: bytes');
    	header('application/octet-stream');
    	header("Cache-control: public");
    	header("Pragma: public");
    	//解决在IE中下载时中文乱码问题
    	$ua = $_SERVER['HTTP_USER_AGENT'];
    	if(preg_match('/MSIE/',$ua)) {    //表示正在使用 Internet Explorer。
    		$ie_filename = str_replace('+','%20',urlencode($file));
    		header('Content-Dispositon:attachment; filename='.$ie_filename);
    	} else {
    		header('Content-Dispositon:attachment; filename='.$file);
    	}
    	$fp = fopen($real,'rb+');
    	fseek($fp,$range);                //fseek:在打开的文件中定位,该函数把文件指针从当前位置向前或向后移动到新的位置，新位置从文件头开始以字节数度量。成功则返回 0；否则返回 -1。注意，移动到 EOF 之后的位置不会产生错误。
    	while(!feof($fp)) {               //feof:检测是否已到达文件末尾 (eof)
    		set_time_limit(0);              //注释①
    		print(fread($fp,1024));         //读取文件（可安全用于二进制文件,第二个参数:规定要读取的最大字节数）
    		ob_flush();                     //刷新PHP自身的缓冲区
    		flush();                       //刷新缓冲区的内容(严格来讲, 这个只有在PHP做为apache的Module(handler或者filter)安装的时候, 才有实际作用. 它是刷新WebServer(可以认为特指apache)的缓冲区.)
    	}
    	fclose($fp);
    }
}
