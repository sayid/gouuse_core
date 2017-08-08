<?php
namespace GouuseCore\Rpcs\TcCenter\Libraries;

use GouuseCore\Rpcs\TcCenter\Rpc;

/**
 * 分布式事务类
 */
class TcLib extends Rpc
{
	public function addTcLog($field_data)
	{
		return $this->do('TcLib', 'addTcLog', [$field_data]);
	}
}