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
$users = $mysqli->query("SELECT users.*, users_roles.rid, role.name AS role 
	FROM users 
	LEFT JOIN users_roles ON users_roles.uid = users.uid 
	LEFT JOIN role ON role.rid = users_roles.rid 
	GROUP BY users.uid 
	ORDER BY users.uid DESC 
	") or die($mysqli->error);

// WHERE users.uid = 110718
echo "<pre>";

$inserted = 0;
$schema = [];
$errors = [];
$mysqli2->query("SET FOREIGN_KEY_CHECKS = 0;");
$mysqli2->query("TRUNCATE users;");
$mysqli2->query("SET FOREIGN_KEY_CHECKS = 1;");

while($row = $users->fetch_object()) {

	$title = "";
	$bio = "";
	$name = "";
	$zip = "";
	$work = "";
	$address = "";
	$linkedin = "";
	$salesforce = "";

	// Users and roles
	$option = $mysqli->query("SELECT `key`, `value`  
		FROM bf_users_options 
		WHERE `key` IN('about','address','company_name','first_name','last_name','linkedin','salesForceId','zip') 
		AND uid = {$row->uid}
		") or die($mysqli->error);

	$options = [];
	while($row2 = $option->fetch_object()) {
		$options[$row2->key] = $row2->value;
	}

	// embeded users object
	$data = (object) unserialize($row->data);

	if($options->first_name){
		$name.= $options->first_name;
	}

	if($options->last_name){
		$name.= $options->last_name;
	}

	if($options->company_name){
		$work.= $options->company_name;
	}

	if($options->zip){
		$zip.= $options->zip;
	}

	if($options->about){
		$bio.= $options->about;
	}

	if($options->last_job_title){
		$title.= $options->last_job_title;
	}	

	if($options->linkedin){
		$linkedin.= $options->linkedin;
	}	

	if($options->salesForceId){
		$salesforce.= $options->salesForceId;
	}	

	if(trim($name)==""){
		if($data->first_name){
			$name.= $data->uf_first_name;
		} 

		if($data->uf_first_name) {
			$name.= $data->uf_first_name;
		}
	}

	if(trim($work) == ""){
		if($data->uf_company_name){
			$work.= " " . $data->uf_company_name;
		}
	}

	if(trim($zip) == ""){
		if($data->uf_zip){
			$zip.= " " . $data->uf_zip;
		}
	}

	if($data->uf_city){
		$address.= " " . $data->uf_city;
	}

	if($data->uf_state){
		$address.= ", " . $data->uf_state;
	}

	if(trim($name)==""){
		$name = trim(addslashes($row->name));
	}

	$title = trim(addslashes($title));
	$zip = trim(addslashes($zip));
	$work = trim(addslashes($work));
	$address = trim(addslashes($address));
	$bio = trim(addslashes($bio));
	$linkedin = trim(addslashes($linkedin));
	$salesforce = trim(addslashes($salesforce));

	$sql = "INSERT INTO users 
		(id,role, name, address, zip_code, work, title, email, biography, linkedin_id, salesforce_id, profile_picture, password, status, created_at, updated_at) VALUES
		($row->uid, '{$row->role}', '{$name}', '{$address}', '{$zip}', '{$work}', '{$title}', '{$row->mail}','{$bio}','{$linkedin}','{$salesforce}','{$row->picture}', '{$row->pass}', 'active', NOW(), NOW())
	";

	$insert_row = $mysqli2->query($sql) OR $errors[] = $sql . ' => ' . $mysqli2->error;

	if($insert_row){
		$inserted++;
	}
}

var_dump($inserted);
var_dump($errors);

$users->free();

// close connection 
$mysqli->close();
$mysqli2->close();
