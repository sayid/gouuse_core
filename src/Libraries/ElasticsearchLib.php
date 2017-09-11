<?php
/**
 * elk基类
 * @author zyb
 */
namespace GouuseCore\Libraries;

use Elasticsearch\ClientBuilder;

class ElasticsearchLib extends Lib
{
	private $host;
	private $client;
	private $type;
	
    public function __construct()
    {
    	$this->type = env('ELASTICSEARCH_TYPE', 'gouuse');
    	$this->host = env('ELASTICSEARCH_SERVER', '192.168.5.223:9200');
    	$hosts = [$this->host];
    	$this->client = ClientBuilder::create()
    	->setHosts($hosts)
    	->build();
    }

    /**
     * $params = [
		    'index' => 'my_index',
		    'id' => 'my_id',
		    'body' => ['testField' => 'abc']
		];
     * @param unknown $param
     * @return unknown
     */
    public function index($index, $id, $body)
    {
    	$params = [
    			'index' => $index,
    			'type' => $this->type,
    			'id' => $id,
    			'body' => $body
    	];
    	
    	return $this->client->index($params);
    }


    /**
     * 检查索引是否存在
     * @param $index
     * @return mixed
     */
    public function isExistIndex($index)
    {
        $params['index']  = $index;
        return $this->client->indices()->exists($params);
    }
    
    /**
     * $params = [
		    'index' => 'my_index',
		    'type' => 'my_type',
		    'id' => 'my_id'
		];
     * @param unknown $param
     * @return unknown
     */
    public function get($index, $id)
    {
    	$params = [
    			'index' => $index,
    			'type' => $this->type,
    			'id' => $id
    	];
    	return $this->client->get($params);
    }
    
    /**
     *  $params = [
		    'index' => 'my_index',
		    'type' => 'my_type',
		    'id' => 'my_id'
		];
     * @param unknown $param
     * @return unknown
     */
    public function getSource($index, $id)
    {
    	$params = [
    			'index' => $index,
    			'type' => $this->type,
    			'id' => $id
    	];
    	return $this->client->getSource($params);
    }
    
    /**
     * 搜索关键词
     * $params = [
		    'index' => 'my_index',
		    'type' => 'my_type',
		    'body' => [
		        'query' => [
		            'match' => [
		                'testField' => 'abc'
		            ]
		        ]
		    ]
		];
     * @param unknown $param
     * @return unknown
     */
    public function search($index, $where)
    {
    	$params = [
    		'index' => $index,
    		'type' => $this->type,
    		'body' => [
    			'query' => [
    				'match' => $where
    			]
    		]
    	];
    	return $this->client->search($params);
    }


    /**
     * 组合多查询
     * @param $index        索引
     * @param $body        查询条件
     * @param int $from     分页开始位置
     * @param int $size     分页数据条数
     * @return mixed
     */
    public function multi_search($index, $body,  $from = 0, $size = 0)
    {
        $params = [
            'index' => $index,
            'type' => $this->type,
            'body' => $body
        ];
        if (!empty($size)) {
            $params['from'] = $from;
            $params['size'] = $size;
        }
        return $this->client->search($params);
    }
    
    /**
     * $params = [
		    'index' => 'my_index',
		    'type' => 'my_type',
		    'id' => 'my_id'
		];

     * @param unknown $param
     * @return unknown
     */
    public function delete($index, $id)
    {
    	$params = [
    			'index' => $index,
    			'type' => $this->type,
    			'id' => $id
    	];
    	return $this->client->delete($params);
    }
    
    /**
     * 
     * @param unknown $param
     * @return unknown
     */
    public function del_index($index)
    {
    	$params = [
    			'index' => $index,
    	];
    	return $this->client->indices()->delete($params);
    }
    
    /**
     * $params = [
		    'index' => 'my_index',
		    'body' => [
		        'settings' => [
		            'number_of_shards' => 2,
		            'number_of_replicas' => 0
		        ]
		    ]
		];
     * @param unknown $param
     * @return unknown
     */
    public function create_index($index, $setting)
    {
    	$params = [
    			'index' => $index,
    			'body' => [
    					$setting
    			]
    	];
    	return $this->client->indices()->create($params);
    }
    

}
