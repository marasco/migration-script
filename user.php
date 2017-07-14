<?php 

include "functions.php";

$id = option_value('i');
$name = option_value('n');

if(!$id AND !$name){
	print "No id or name provided. Please use -id 123 || -name John" . "\n";
	exit;
}

//Open a new connection to the MySQL server
$mysqli = new mysqli('localhost','root','eNWM@[v5FC^y','bevforce_dest');

//Output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

$where = [];
if($id) $where[]= "users.id = " . $id;	
if($name) $where[]= "users.name LIKE '%" . $name . "'";
$wheresql = implode(" AND ", $where);

// Users and roles
$users = $mysqli->query("

	SELECT users.*, user_cover_letter.path_file as cover_letter, user_resumes.path_file as resume 
	FROM users
	LEFT JOIN user_cover_letter ON user_cover_letter.user_id = users.id 
	LEFT JOIN user_resumes ON user_resumes.user_id = users.id 
	WHERE {$wheresql}
	GROUP BY users.id

	") OR die($mysqli->error);

if($users->num_rows){
	print "Found " . $users->num_rows . " users" . "\n";

	while($row = $users->fetch_object()) {
		print json_encode($row, JSON_PRETTY_PRINT);
	}
}

print "\n";

$users->free();

// close connection 
$mysqli->close();