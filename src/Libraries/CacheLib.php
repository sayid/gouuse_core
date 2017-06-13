<?php
namespace GouuseCore\Libraries;

//引入缓存
use Illuminate\Support\Facades\Cache;

/**
 * 缓存封装类
 * @author W_wang
 *
 */
class CacheLib extends Lib
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 添加缓存
     * @param $id   缓存key
     * @param $data 缓存数据
     * @param $ttl  缓存时间
     * @return boolean|void
     */
    public function set($id, $data, $ttl = 1)
    {
        if (!$data || !$id) {
            return false;
        }
        return Cache::put($id, $data, $ttl);
    }


    /**
     * 获取缓存
     * @param $id   缓存key
     * @return 缓存结果
     */
    public function get($id = '')
    {
    	$result = Cache::get($id);
    	return is_null($result)?false:$result;
    }


    /**
     * 删除缓存
     * @param $id   缓存key
     * @return boolean|void
     */
    public function delete($id = '')
    {
        return Cache::forget($id);
    }


    /**
     * 自定义 迁移CI框架方法
     * @param unknown $ids
     * @param unknown $id
     * @param unknown $data
     * @param number $ttl
     * @return boolean|void
     */
    public function saveWithKey($ids, $id, $data, $ttl = 60)
    {
        if (!$ids || !$id) {
            return false;
        }
    
        $array_val = Cache::get($ids);
        if (is_array($array_val)) {
            if (!in_array($id, $array_val)) {
                array_push($array_val, $id);
                Cache::put($ids, $array_val, $ttl);
            }
        } else {
            $array_val = array($id);
            Cache::put($ids, $array_val, $ttl);
        }
    
        return Cache::put($id, $data, $ttl);
    }

    
    /**
     * 删除列表key
     *
     * @param string $ids
     * @return bool
     */
    public function delWithKey($ids)
    {
        if (!$ids) {
            return false;
        }
    
        $array_val = Cache::get($ids);
        if (is_array($array_val)) {
            foreach ($array_val as $v) {
                Cache::forget($v);
            }
        }
        Cache::forget($ids);
    
        return true;
    }
}
