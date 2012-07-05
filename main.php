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
while ($data = fgetcsv($handle, 1000, ",")) {
	$users[] = array("lastName" => $data[0], "name" => $data[1], "email" => $data[2], "curp" => $data[3], "phone" => $data[4], "addres" => $data[5]);
}
fclose($handle);

$pwgen = new PWGen();
$outpotFile = fopen("data.ldiff", "w");
$outpotRFile = fopen("data.report", "w");
#pull ldiff template
$outpot = NULL;
$outpotR = NULL;
foreach ($users as $user){
	$template = new coSimpleTemplate("template.ldiff");
	$report = new coSimpleTemplate("template.report");
	$password = $pwgen->generate();
	$template->set("password", $password);	
	$template->set("name", capitalize($user["name"]));
	$template->set("lastName", capitalize($user["lastName"]));
	$template->set("email", $user["email"]);
	$template->set("phone", $user["phone"]);
	$template->set("address", $user["address"]);
	$template->set("dommain", $dn);
	$fullName = capitalize($user["name"]. " " . $user["lastName"]);
	$template->set("fullName", $fullName);
	$gecos = dees(strtolower(trim($fullName)));
	$template->set("gecos", $gecos);
	$cleanName = dearticle(strtolower(trim($user["name"])));
	$cleanLastName = dearticle(strtolower(trim($user["lastName"])));
	$lastNames = explode(" ", $cleanLastName);
	$uid = substr($cleanName, 0, 1) . $lastNames[0];
	$template->set("uid", $uid);
	$names = explode(" ", $cleanName);
	$inittials = NULL;
	foreach ($names as $sname){
		$inittials .= substr($sname, 0, 1);
	}
	foreach ($lastNames as $slastName){
		$inittials .= substr($slastName, 0, 1);
	}
	$template->set("inittials", $inittials);
	#[@organization]
	$report->set("fullName", $fullName);
	$report->set("uid", $uid);
	$report->set("password", $password);	
	$report->set("email", $user["email"]);
	$outpot .= $template->output();
	$outpot .= "\n";
	$outpotR .= $report->output();
	$outpotR .= "\n";
}
fwrite($outpotFile, $outpot);
fclose($outpotFile);
fwrite($outpotRFile, $outpotR);
fclose($outpotRFile);
#pussh data to template.

#push dato to file


###functions
function dees($text){
	$search  = array('ñ', 'á', 'é', 'í', 'ó', 'ú', 'ü');
	$replace = array('x', 'a', 'e', 'i', 'o', 'u', 'u' );
	$return = str_replace($search, $replace, $text);
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

