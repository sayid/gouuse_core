<?php
namespace GouuseCore\Rpcs\UserCenter\Models;

use GouuseCore\Rpcs\UserCenter\Rpc;
use GouuseCore\Helpers\OptionHelper;
use GouuseCore\Helpers\StringHelper;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class AreaDataModel extends Rpc
{
    function __construct()
    {
        parent::__construct();
        $this->obj = OptionHelper::getGouuse();
    }

    public function getAll()
    {
        $cache_key = $this->service_id . StringHelper::getClassname(get_class($this)) . __FUNCTION__;
        $cache_data= $this->obj->CacheLib->get($cache_key);
        if (empty($cache_data)) {
            $cache_data = $this->do('AreaDataModel', 'getSelectAll', []);
            $this->obj->CacheLib->save($cache_key, $cache_data, 3600);
        }
        return $cache_data;
    }
    /**
     * 获取省份名称
     * @param int $company_id
     * @return mixed
     */
    public function getProvinceName($province_id)
    {
        $cache_data = $this->getAll();
        foreach ($cache_data as $row) {
            if ($row['province_id'] == $row['province_id'] && $row['area_id'] == 0 && $row['city_id'] == 0) {
                return $row['name'];
            }
        }
    }

    public function getCityName($city_id)
    {
        $cache_data = $this->getAll();
        foreach ($cache_data as $row) {
            if ($row['area_id'] == 0 && $row['city_id'] == $city_id) {
                return $row['name'];
            }
        }
    }

    public function getAreaName($area_id)
    {
        $cache_data = $this->getAll();
        foreach ($cache_data as $row) {
            if ($row['area_id'] == $area_id) {
                return $row['name'];
            }
        }
    }
}