<?php 
//Open a new connection to the MySQL server
$mysqli = new mysqli('localhost','root','eNWM@[v5FC^y','bevforce_users');
$mysqli2 = new mysqli('localhost','root','eNWM@[v5FC^y','bevforce_dest');

//Output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

//Output any connection error
if ($mysqli2->connect_error) {
    die('Error : ('. $mysqli2->connect_errno .') '. $mysqli2->connect_error);
}


// Users and roles
$results = $mysqli->query("SELECT users.*, users_roles.rid, role.name AS role 
	FROM users 
	LEFT JOIN users_roles ON users_roles.uid = users.uid 
	LEFT JOIN role ON role.rid = users_roles.rid 
	") or die($mysqli->error);

// WHERE users.uid = 110718
echo "<pre>";

$inserted = 0;
$schema = [];
$errors = [];
$mysqli2->query("SET FOREIGN_KEY_CHECKS = 0;");
$mysqli2->query("TRUNCATE users;");
$mysqli2->query("SET FOREIGN_KEY_CHECKS = 1;");

while($row = $results->fetch_object()) {
	$data = (object) unserialize($row->data);

	$title = "";
	$zip = "";
	$work = "";
	$address = "";

	if($data->first_name){
		$title.= $data->uf_first_name;
	} else if($data->uf_first_name) {
		$title.= $data->uf_first_name;
	}

	if($data->uf_last_name){
		$title.= " " . $data->uf_last_name;
	}

	if($data->uf_company_name){
		$work.= " " . $data->uf_company_name;
	}

	if($data->uf_zip){
		$zip.= " " . $data->uf_zip;
	}

	if($data->uf_city){
		$address.= " " . $data->uf_city;
	}

	if($data->uf_state){
		$address.= ", " . $data->uf_state;
	}

	//$title = addslashes($title);
	//$title = trim(str_replace("'","\'",$title));
	$title = str_replace("'", "\'", htmlspecialchars_decode($title, ENT_QUOTES));
	$title = str_replace('"', "'+String.fromCharCode(34)+'", $title);
	$title = trim($title);

	$zip = trim(addslashes($zip));
	$work = trim(addslashes($work));
	$address = trim(addslashes($address));

	$sql = "INSERT INTO users 
		(role, name, address, zip_code, work, title, email, profile_picture, password, status, created_at, updated_at) VALUES
		('{$row->role}', '{$row->name}', '{$address}', '{$zip}', '{$work}', '{$title}', '{$row->mail}','{$row->picture}', '{$row->pass}', 'active', NOW(), NOW())
	";
	$insert_row = $mysqli2->query($sql) OR $errors[] = $sql;

	if($insert_row){
		$inserted++;
	}
}

var_dump($inserted);
var_dump($errors);

$results->free();

// close connection 
$mysqli->close();
$mysqli2->close();
