<?php
include_once('DB.class.php');
class SBDB{
	static private function getConfig($jsonfile){
		return json_decode(file_get_contents($jsonfile));
	}
	
	private $db = null;
	public function __construct(){
		$this->db = DB::getDB();
		$config = self::getConfig('conf/db.conf.json');
		$this->db->connect($config->host,$config->user,$config->pass,$config->db,$config->port);
	}
	public function __destruct(){
		DB::delDB();
		$this->db = null;
	}
	/**
	 * 检查数据库
	 * @return bool [TRUE]正常 or [FALSE]错误
	 */
	public function checkUp(){
		//$this->db;
		return TRUE;
	}
	/**
	 * 登录
	 * @param [$data]
	 */
	public function login($data){
		if(!isset($data->type)){
			return ['errno'=>-2,'error'=>'type for id is Unknowable'];
		}
		switch($data->type){
			case 'sinaweibo':
				$fieId = 'wb_id';
				break;
			case 'qq':
				$fieId = 'qq_id';
				break;
			case 'weixin':
				$fieId = 'wx_id';
				break;
			default:
				return ['errno'=>-1,'error'=>'type for id should be \'sinaweibo\', \'qq\' or \'weixin\''];
				break;
		}
		$res = $this->db->select([
			'table'=>'sb_user_login',
			'field'=>'id',
			'where'=>"$fieId = '$data->id'"
		]);

		if(empty($res)){
			return ['errno'=>1,'error'=>'not regist'];
		}
		
		$uid = $res[0]['id'];
		$userInfo = $this->db->select([
			'table'=>'sb_user_info',
			'field'=>'name,nickname,birthday,email,schoolgrade,schoolmajor,schoolclass',
			'where'=>"u_id = $uid"
		])[0];
		$userInfo['school'] = $this->getSchool($uid);
		$dpList   = $this->getDepartments($uid);
		$notices  = $this->getNotice($uid);
		
		$res = [
			'userInfo'=>$userInfo,
			'dpList'  =>$dpList,
			'notices' =>$notices
		];
//		var_dump($res);

		return $res;
	}
	/**
	 * 注册
	 * @param [$data]
	 */
	public function regist($data){}
	/**
	 * 获取用户加入的社团部门信息
	 * @param [$uid] 用户id
	 */
	public function getDepartments($uid){
		return $this->db->select([
			'table'=>'sb_school_community,sb_community_department,sb_user_department',
			'field'=>'sb_school_community.NAME AS cName,sb_school_community.nickname AS cNick,sb_school_community.summary AS cSum,sb_school_community.COUNT AS cCount,sb_community_department.NAME AS dName,sb_community_department.nickname AS dNick,sb_community_department.summary AS dSum,sb_community_department.COUNT AS dCount,sb_user_department.post AS pos',
			'where'=>"sb_user_department.u_id = $uid AND sb_community_department.com_id = schoolbook.sb_school_community.ID AND sb_user_department.dep_id = schoolbook.sb_community_department.ID"
		]);
	}
	/**
	 * 获取公告信息
	 * @param [$uid] 用户id
	 * @param [$did] dep(部门)id
	 * @param [$pag] 页数(10条一页)(从0开始Re0)
	 */
	public function getNotice($uid,$did=-1,$pag=0){
//		var_dump($uid,$did,$pag);
		if($did>0){
			return $this->db->select([
				'table'  =>'sb_notice sn',
				'field'  =>'sn.title AS title,sn.notice AS notice,sn.targetType AS type,sn.time AS time',
				'where'  =>"(sn.targetType = 0) OR (sn.targetType = 3 AND sn.u_idTarget = $uid) OR (sn.targetType = 2 AND sn.dep_idTarget = $did) OR (sn.targetType = 1 AND sn.dep_idTarget IN (SELECT scd.id FROM sb_community_department scd WHERE scd.com_id IN (SELECT scd.com_id FROM sb_community_department scd WHERE scd.id = $did)))",
				'$option'=>'OUP BY sn.time,sn.targetType,sn.dep_idTarget ORDER BY sn.time DESC LIMIT 10 OFFSET '.($pag*10)
			]);
		}else{
			return $this->db->select([
				'table'  =>'sb_notice sn',
				'field'  =>'sn.title AS title,sn.notice AS notice,sn.targetType AS type,sn.time AS time',
				'where'  =>"(sn.targetType = 0) OR (sn.targetType = 3 AND sn.u_idTarget = $uid) OR (sn.targetType = 2 AND sn.dep_idTarget IN (SELECT sud.dep_id FROM sb_user_department sud WHERE sud.u_id = $uid)) OR (sn.targetType = 1 AND sn.dep_idTarget IN (SELECT scd.id FROM sb_community_department scd WHERE scd.com_id IN (SELECT scd.com_id FROM sb_community_department scd WHERE scd.id IN (SELECT sud.dep_id FROM sb_user_department sud WHERE sud.u_id = $uid))))",
				'$option'=>'OUP BY sn.time,sn.targetType,sn.dep_idTarget ORDER BY sn.time DESC LIMIT 10 OFFSET '.($pag*10)
			]);
		}
	}
	/**
	 * 获取用户就读学校
	 * @param  [$uid] 用户id
	 * @return string 学校名
	 */
	public function getSchool($uid){
		return $this->db->select([
			'table'=>'sb_school ss',
			'field'=>'ss.name',
			'where'=>"ss.id IN (SELECT ssm.school_id FROM sb_user_info sui,sb_school_major ssm WHERE sui.u_id=$uid AND ssm.id=sui.schoolmajor)"
		])[0]['name'];
	}
	/**
	 * 获取通讯录
	 * @param [$data]
	 */
	public function getBook($data){}
}
?>