<?php

namespace GouuseCore\Rpcs\TcCenter\Libraries;


/**
 * 分布式事务类
 */
class TcLib extends Lib
{
	public function addTcLog($field_data)
	{
		return $this->do('TcLib', 'addTcLog', [$field_data]);
	}
}