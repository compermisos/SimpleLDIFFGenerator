<?php
require_once("coSimpleTemplate.php");
require_once("pwgen.php");
$users = array();
//apellidos,nombre,correo,curp,telefono,direccion
#get base DN
$dn= NULL;
#get route to csv
$file = "csv/sample.csv";
#parse CSV
if (($handle = fopen("test.csv", "r")) !== FALSE) {
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$users[] = array("lastName" => $data[0], "name" => $data[1], "email" => $data[2], "curp" => $data[3], "phone" => $data[4], "addres" => $data[5]);
	}
	fclose($handle);
}

$pwgen = new PWGen();
$outpot = fopen("test.csv", "w");
#pull ldiff template
$template = new coSimpleTemplate("template.ldiff");
$template->set("password", $pwgen->generate());
$template->set("name", capitalize($user["name"]));
$template->set("lastName", capitalize($user["lastName"]));
$template->set("email", $user["email"]);
$template->set("lastName", $user["email"]);
$template->set("phone", $user["phone"]);
$template->set("address", $user["address"]);
$template->set("dommain", $dn);
$fullname = capitalize($user["name"]. " " . $user["lastName"])
$template->set("fullname", $fullname);
$gecos = dees(strtolower(trim($fullname)));
$template->set("gecos", $gecos);
$cleanName = dearticle(strtolower(trim($user["name"])));
$cleanLastName = dearticle(strtolower(trim($user["lastName"])));
$lastNames = explode(" ", $cleanLastName);
$uid = substr($cleanName, 0, 1) . $lastaNames[0];
$names = explode(" ", $cleanName);
$inittials = NULL;
foreach ($names as $sname){
	$inittials .= substr($snamee, 0, 1);
}
foreach ($lastNames as $slastName){
	$inittials .= substr($slastName, 0, 1);
}
#[@organization]
print $template->output();

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
function capitalize(text){
	$return = ucwords(strtolower(trim($text)));
	return $return;
}

