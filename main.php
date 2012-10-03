<?php
require_once("coSimpleTemplate.php");
require_once("pwgen.php");
$users = array();
//apellidos,nombre,correo,curp,telefono,direccion
#get base DN
$dn= "ou=alumnos,dc=openintelligence,dc=mx";
#get route to csv
$file = "csv/sample.csv";
#parse CSV
$handle = fopen($file, "r");
while ($data = fgetcsv($handle, 1000, ",")){
	for($i = 0; $i < 6; $i++){
		if(!isset($data[$i])){
			$data[$i] = " ";
		}
	}
	$users[] = array(
		"lastName" => cleaninput($data[0]),
		"name" => cleaninput($data[1]),
		"email" => cleaninput($data[2]),
		"curp" => cleaninput($data[3]),
		"phone" => cleaninput($data[4]),
		"address" => cleaninput($data[5]),
	);
}
fclose($handle);

$pwgen = new PWGen();
$outpotFile = fopen("data.ldiff", "w");
$outpotRFile = fopen("data.report", "w");
$outpotCFile = fopen("data.csv", "w");
#pull ldiff template
$outpot = NULL;
$outpotR = NULL;
$outpotC = NULL;
$uids = array();
include("./ldap_connection.php");
$person="";
$filter="(|(sn=$person*)(givenname=$person*))";
$justthese = array("uid");
$sr=ldap_search($ds, $dn, $filter, $justthese);
$info = ldap_get_entries($ds, $sr);
foreach ($info as $user){
	$uids[] = $user["uid"][0];
}
foreach ($users as $user){
	$password = $pwgen->generate();
	$fullName = trim(capitalize($user["name"]. " " . $user["lastName"]));
	$gecos = strtolower(dees($fullName));
	$cleanName = dearticle(strtolower(dees($user["name"])));
	$cleanLastName = dearticle(strtolower(dees($user["lastName"])));
	$lastNames = explode(" ", $cleanLastName);
	$names = explode(" ", $cleanName);
	$inittials = NULL;
	foreach ($names as $sname){
		$inittials .= substr($sname, 0, 1);
	}
	foreach ($lastNames as $slastName){
		$inittials .= substr($slastName, 0, 1);
	}
	$uid = strtolower(dees(substr($cleanName, 0, 1) . $lastNames[0]));
	$uidControll = TRUE;
	$tip = 1;
	$tip2 = 2;
	while($uidControll){
		if(in_array($uid,$uids)){
			if(isset($names[1])){
				$uid = strtolower(dees(substr($names[0], 0, $tip) . substr($names[1], 0, 1) . $lastNames[0]));
			}else{
				$uid = strtolower(dees(substr($cleanName, 0, $tip2) . $lastNames[0]));
			}
		}else{
			$uidControll = FALSE;
		}
		$tip++;
		$tip2++;
	}
	$inittials = strtolower(dees($inittials));
	$template = new coSimpleTemplate("template.ldiff");
	$report = new coSimpleTemplate("template.report");
	
	$template->set("password", $password);	
	$template->set("name", capitalize($user["name"]));
	$template->set("lastName", capitalize($user["lastName"]));
	$template->set("email", $user["email"]);
	$template->set("phone", $user["phone"]);
	$template->set("address", $user["address"]);
	$template->set("dommain", $dn);
	$template->set("fullName", $fullName);
	$template->set("uid", $uid);
	$template->set("inittials", $inittials);
	$template->set("gecos", $gecos);
	#[@organization]
	$report->set("fullName", $fullName);
	$report->set("uid", $uid);
	$report->set("password", $password);	
	$report->set("email", $user["email"]);
	
	$uids[]=$uid;
	$outpot .= $template->output();
	$outpot .= "\n";
	$outpotR .= $report->output();
	$outpotR .= "\n";
	$outpotC .= "$fullName,$uid," . $user["email"] . ",$password\n";
}
fwrite($outpotFile, $outpot);
fclose($outpotFile);
fwrite($outpotRFile, $outpotR);
fclose($outpotRFile);
fwrite($outpotCFile, $outpotC);
fclose($outpotCFile);
#pussh data to template.

#push dato to file


###functions
function dees($text){
	$search  = array('ñ','á','é','í', 'ó', 'ú', 'ü',"ú","Ñ","Á","É","Í","Ó","Ú" );
	$replace = array('x','a','e','i', 'o', 'u', 'u',"u","X","A","E","I","O","U" );
	$return = str_ireplace($search, $replace, $text);
	return $return;
}
function dearticle($text){
	$text = " " . $text . " ";
	$search  = array(' del ', ' las ', ' de ', ' la ', ' y ', ' a ');
	$return =trim(str_replace($search," ",$text));
	return $return;
}
function capitalize($text){
	$return = ucwords(strtolower(trim($text)));
	return $return;
}
function cleaninput($text){
	$return = strtolower(trim($text));
	return $return;
}


