<?php 

include "functions.php";

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

$inserted = 0;
$errors = [];
$resume_id = 0;

if(option_value('r')){ // resume
	$resume_id = $mysqli2->query("SELECT MAX(id) AS id FROM users")->fetch_object()->id; 
	print "Note: resuming from " . $resume_id .  "\n";
	$inserted = $mysqli2->query("SELECT COUNT(*) as offset FROM users WHERE id < {$resume_id}")->fetch_object()->offset; 
} else {
	if(option_value('t')) { // truncate
		print "Note: users,user_cover_letter,user_resumes truncated" . "\n";
		$mysqli2->query("SET FOREIGN_KEY_CHECKS = 0;");
		$mysqli2->query("TRUNCATE users;");
		$mysqli2->query("TRUNCATE user_cover_letter;");
		$mysqli2->query("TRUNCATE user_resumes;");
		$mysqli2->query("SET FOREIGN_KEY_CHECKS = 1;");
	}	
}

// Users and roles
$users = $mysqli->query("SELECT users.*, users_roles.rid, role.name AS role 
	FROM users 
	LEFT JOIN users_roles ON users_roles.uid = users.uid 
	LEFT JOIN role ON role.rid = users_roles.rid 
	WHERE users.uid > {$resume_id} 
	GROUP BY users.uid 
	ORDER BY users.uid ASC") or die($mysqli->error);

$total = $mysqli->query("SELECT COUNT(*) AS total FROM users")->fetch_object()->total or die($mysqli->error);

while($row = $users->fetch_object()) {

	//echo $row->uid . ",";
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
	$data = (object) @unserialize($row->data);

	if(!empty($options->first_name)){
		$name.= $options->first_name;
	}

	if(!empty($options->last_name)){
		$name.= $options->last_name;
	}

	if(!empty($options->company_name)){
		$work.= $options->company_name;
	}

	if(!empty($options->zip)){
		$zip.= $options->zip;
	}

	if(!empty($options->about)){
		$bio.= $options->about;
	}

	if(!empty($options->last_job_title)){
		$title.= $options->last_job_title;
	}	

	if(!empty($options->linkedin)){
		$linkedin.= $options->linkedin;
	}	

	if(!empty($options->salesForceId)){
		$salesforce.= $options->salesForceId;
	}	

	if(trim($name)==""){
		if(!empty($data->first_name)){
			$name.= $data->first_name;
		} 

		if(!empty($data->uf_first_name)) {
			$name.= $data->uf_first_name;
		}

		if(!empty($data->uf_last_name)) {
			$name.= " " . $data->uf_last_name;
		}		
	}

	if(trim($work) == "" AND !empty($data->uf_company_name)){
		$work.= $data->uf_company_name;
	}

	if(trim($zip) == "" AND !empty($data->uf_zip)){
		$zip.= $data->uf_zip;
	}

	if(!empty($data->uf_city)){
		$address.= " " . $data->uf_city;
	}

	if(!empty($data->uf_state)){
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
	$email = strtolower($row->mail);
	
	$sql = "INSERT INTO users 
		(id,role, name, address, zip_code, work, title, email, biography, linkedin_id, salesforce_id, profile_picture, password, status, created_at, updated_at) VALUES
		($row->uid, '{$row->role}', '{$name}', '{$address}', '{$zip}', '{$work}', '{$title}', '{$email}','{$bio}','{$linkedin}','{$salesforce}','{$row->picture}', '{$row->pass}', 'migration', NOW(), NOW())
	";

	$insert_row = $mysqli2->query($sql) OR $errors[] = $sql . ' => ' . $mysqli2->error;

	if($insert_row){
		$inserted++;
	}

	show_status($inserted, $total);	
}

print "" . "\n";

if(count($errors)){
	foreach($errors as $e){
		print "Error: " . $e . "\n";
	}
}

print "inserted: " . $inserted . " of " . $total . "\n";
print "success: " . round($inserted/$total*100) . "%" . "\n";

$users->free();

// close connection 
$mysqli->close();
$mysqli2->close();
