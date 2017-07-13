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
$users = $mysqli->query("SELECT *
	FROM bf_files
	WHERE type IN('cover-letter','resume') 
	GROUP BY fid 
	") or die($mysqli->error);

// WHERE users.uid = 110718
echo "<pre>";

$inserted = 0;
$schema = [];
$errors = [];
$mysqli2->query("SET FOREIGN_KEY_CHECKS = 0;");
$mysqli2->query("TRUNCATE user_resumes;");
$mysqli2->query("TRUNCATE user_cover_letter;");
$mysqli2->query("SET FOREIGN_KEY_CHECKS = 1;");

while($row = $users->fetch_object()) {

	$table = "";

	if($row->type == 'resume'){
		$table = "user_resumes";
	}
	else if($row->type == 'cover-letter'){
		$table = "user_cover_letter";
	}

	if(strlen($table)){
		$sql = "INSERT INTO {$table} 
			(user_id,name_file, path_file, created_at, updated_at) VALUES
			($row->uid, '{$row->filename}', '{$row->fileurl}', NOW(), NOW())
		";
		$insert_row = $mysqli2->query($sql) OR $errors[] = $sql . ' => ' . $mysqli2->error;
	}

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
