<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
	include_once('lib/SBDB.class.php');
	include_once('lib/crypt.class.php');
//	$db = DB::DB();
//	$db = DB::DB();
//	$db = DB::DB();
//	DB::getDB()->insert('a',['aa'=>'asd','bb'=>5]);
//	DB::getDB()->connect('localhost','root','216212lvx','schoolbook',3306);
//	DB::getDB()->insert(['table'=>'test','data'=>['a'=>11,'b'=>'253']]);
//	DB::getDB()->select(['table'=>'test','field'=>['a as A','b'],'where'=>'a=1']);
//	DB::getDB()->update(['table'=>'test','set'=>['a'=>4,'b'=>'da'],'where'=>'isnull(b)']);
//	DB::getDB()->delete(['table'=>'test','where'=>'a=100']);
//	DB::getDB()->select(['table'=>'test','field'=>['a as A','b']]);
//	DB::delDB();
//	$sb = new SBDB();
	//var_dump(new SBDB());
//header("content-type:text/html;charset=utf-8");
	if(isset($_REQUEST['data'])&&$_REQUEST['data']){
		$reqData = explode('@',base64_decode($_REQUEST['data']));
		$id = $reqData[0];
		$cry = new Crypt("dww".$id);

		$res = [];											//响应数据
		$req = json_decode($cry->decrypt($reqData[1]));		//请求数据
		$msgType = $req->msgType;

		switch ($msgType) {
			case 'login':
				//校验id
				if($req->logMsg->id != $id){
					$res['error'] = "Id is error (different encrypt id)";
					$res['errno'] = -3;
					break;
				}
				$sb = new SBDB();
				$res = $sb->login($req->logMsg);
				break;
			default:
				
				break;
		}
		
//		var_dump($res);
		$res = json_encode($res);
		$data = "'".$cry->encrypt($res)."'";
	}else{
		$data = "{'errno':-10,'error':'error request'}";
	}
	if(isset($_REQUEST['callback'])){
		echo $_REQUEST['callback'].'('.$data.')';
	}else{
		echo $data;
	}
?>