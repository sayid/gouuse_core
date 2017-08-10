<?php
namespace App\Models;

use GouuseCore\Models\BaseModel;

/**
 * 分布式事务基础类
 * @author zhangyubo
 *
 */
class TcBaseModel extends BaseModel
{

    //表名
    protected $table = "tc_log_local";
    
    public function __construct()
    {
        parent::__construct();
    }

}
