<?php
class DB{
	protected static $db = null;
	static public function getDB(){
		if(is_null(self::$db) || !isset(self::$db)){
			self::$db = new self();
		}
		return self::$db;
	}
	static public function delDB(){
		if(is_null(self::$db) || !isset(self::$db)){return;}
		self::$db->m_oMysql->close();
		self::$db = null;
	}
	
	private $m_oMysql = null;
	private function __construct(){
		$this->m_oMysql = mysqli_init();
		$this->m_oMysql->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0');
		$this->m_oMysql->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
	}
	protected function __clone(){}
	
	public function connect($host,$user,$pass,$db,$port=3306){
		$this->m_oMysql->real_connect($host,$user,$pass,$db,$port);
		$this->m_oMysql->autocommit(TRUE);
		$this->m_oMysql->real_query("SET NAMES UTF8;");
	}
	
	/**
	 * @param [$data]数组
		$data 
		[
			'table'=>"test",		//表名，字符串，必须 
			'data'=>['a'=>X,'b'=>Y],//插入值，数组，必须 
		]
	 * @return bool
	 * @return [false] - error or faild
	 * @return [true]- success and num of changed
	 */
	public function insert($data){
		if(isset($data) && is_object($data)){
			$data = (array)$data;
		}
		if(!(isset($data) && is_array($data) && isset($data['table']) && isset($data['data']))){
			return false;//['error'=>'argument error !'];
		}
		$table = $data['table'];
		if(is_object($data['data'])||is_array($data['data'])){
			if(is_object($data['data'])){
				$data['data'] = (array)$data['data'];
			}
			$keys = array_keys($data['data']);
			$vals = array_values($data['data']);
			if(is_string($keys[0])){
				$keys = '('.join(',',$keys).')';
			}else{
				$keys = "";
			}
			$vals = join(',', array_map(function($e){if(is_string($e)){$e='\''.$e.'\'';}return $e;}, $vals));
		}else{return false;}//else if(is_string($data['data'])){}
		$sql = sprintf("insert into %s %s values(%s);",$table,$keys,$vals);
//		echo $sql;
		$this->m_oMysql->real_query($sql);
	}
	
	/**
	 * @param [$data]数组或表名字符串
		$data 
		[
			'table'=>"test",		//表名，字符串，必须 
			'field'=>['a','b'],		//字段，数组或逗号分隔的字符串，默认"*" 
			'where'=>"1",			//条件，字符串，默认"1" 
			'option'=>''			//选项，字符串，默认空 
		]
	 */
	public function select($data){
		if(is_string($data)){
			$sql = sprintf("select * from %s;",$data);
		}else if(is_object($data)||is_array($data)){
			if(is_object($data)){
				$data = (array)$data;
			}
			$table = $data['table'];
			$field = isset($data['field'])?(is_string($data['field'])?$data['field']:(is_array($data['field'])?join(',',$data['field']):"*")):"*";
			$where = isset($data['where'])?sprintf("where %s",$data['where']):"";
			$option= isset($data['option'])?$data['option']:"";
			
			$sql = sprintf("select %s from %s %s %s;",$field,$table,$where,$option);
		}else{
			return ["error"=>"argument error !"];
		}
		//echo $sql;
		$res = $this->m_oMysql->query($sql);
		$result = array();
		if($res){
			while($ass = $res->fetch_assoc()){
				$result[]=$ass;
			}
		}
//		var_dump($result);
		return $result;
	}
	/**
	 * @param [$data]数组
		$data 
		[
			'table'=>"test",		//表名，字符串，必须 
			'set'=>['a'=>X,'b'=>Y],	//更新字段，数组或逗号分隔的字符串，必须 
			'where'=>"1",			//条件，字符串，默认"1" 
			'option'=>''			//选项，字符串，默认空 
		]
	 * @return bool
	 * [false] - error or faild
	 * [true]- success and num of changed
	 */
	public function update($data){
		if(isset($data) && is_object($data)){
			$data = (array)$data;
		}
		if(!(isset($data) && is_array($data) && isset($data['table']) && isset($data['set']))){
			return false;//['error'=>'argument error !'];
		}
		$table = $data['table'];
		if(is_string($data['set'])){
			$set = 'set '.$data['set'];
		}else if(is_object($data['set'])||is_array($data['set'])){
			if(is_object($data['set'])){
				$data['set'] = (array)$data['set'];
			}
			$set = 'set '.join(',',array_map(function($e){if(is_string($e)){$e='\''.$e.'\'';}return $e;}, $data['set']));
		}else{
			return false;//['error'=>'argument error !'];
		}
		$where = isset($data['where'])?sprintf("where %s",$data['where']):"";
		$option= isset($data['option'])?$data['option']:"";
		
		$sql = sprintf("update %s %s %s %s;",$table,$set,$where,$option);
		//echo $sql;
		return $this->m_oMysql->query($sql);
	}
	/**
	 * @param [$data]数组
		$data 
		[
			'table'=>"test",		//表名，字符串，必须 
			'where'=>"1",			//条件，字符串，默认"1" 
			'option'=>''			//选项，字符串，默认空 
		]
	 * @return bool
	 * [false] - error or faild
	 * [true]- success and num of changed
	 */
	public function delete($data){
		if(isset($data) && is_string($data)){
			$sql = sprintf("delete from %s;",$data);
		}else{
			if(is_object($data)){
				$data = (array)$data;
			}
			if(!(isset($data) && is_array($data) && isset($data['table']))){
				return false;//['error'=>'argument error !'];
			}
			$table = $data['table'];
			$where = isset($data['where'])?sprintf("where %s",$data['where']):"";
			$option= isset($data['option'])?$data['option']:"";
			
			$sql = sprintf("delete from %s %s %s;",$table,$where,$option);
		}
		//echo $sql;
		return $this->m_oMysql->real_query($sql);
	}
}
?>