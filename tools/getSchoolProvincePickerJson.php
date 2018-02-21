<?php
$result = [];
$conf = json_decode(file_get_contents("../conf/db.conf.json"));
$sql = new mysqli;
$sql->connect($conf->host,$conf->user,$conf->pass,$conf->db,$conf->port);
$sql->real_query("SET NAMES UTF8;");
$res = $sql->query("select sbp.id as pid,sbp.name as pname from sb_province sbp ORDER BY pid;") or die("select sbp.id as pid,sbp.name as pname from sb_province sbp ORDER BY pid;");
while($row=$res->fetch_assoc()){
	$tmp = [];
	$tmp["value"] = $row["pid"];
	$tmp["text"]  = $row["pname"];
	$tmp["children"] = [];
	
	array_push($result,$tmp);
}
foreach ($result as $key => $value) {
	$res = $sql->query("select sbs.id as sid,sbs.name as sname from sb_school sbs where sbs.province=".$value["value"]." ORDER BY convert(sname using gbk) asc;") or die("select sbs.id as sid,sbs.name as sname from sb_school sbs where sbs.province=".$value["value"]." ORDER BY convert(sname using gbk) asc;");
	while($row=$res->fetch_assoc()){
		$tmp = [];
		$tmp["value"] = $row["sid"];
		$tmp["text"]  = $row["sname"];
		//var_dump($tmp);
		array_push($result[$key]["children"],$tmp);
	}
}
//var_dump($result);
echo @$_GET["callback"]."(".json_encode($result).")";
?>