<?php 

	$connections = (object)[
		"bevforce_dest" => ['localhost','root','eNWM@[v5FC^y','bevforce_jobs']
	];

	include_once "functions.php";
	include "routine.php";

	$id = !empty($options['i'])?$options['i']:0;
	$name = !empty($options['n'])?$options['n']:0;
	$email = !empty($options['e'])?$options['e']:0;

	if(!$id AND !$name AND !$email){
		print colorize("No id or name provided. Please use -id 123 || -name John","FAILURE");
		exit;
	}

	$where = [];
	if($id) $where[]= "users.id = " . $id;	
	if($name) $where[]= "users.name LIKE '%" . $name . "%'";
	if($email) $where[]= "users.email LIKE '%" . $email . "%'";	
	$wheresql = implode(" AND ", $where);

	// Users and roles
	$users = $mysql["bevforce_dest"]->query("

		SELECT users.*, user_cover_letter.path_file as cover_letter, user_resumes.path_file as resume 
		FROM users
		JOIN user_cover_letter ON user_cover_letter.user_id = users.id 
		JOIN user_resumes ON user_resumes.user_id = users.id 
		WHERE {$wheresql}
		GROUP BY users.id

		") OR die($mysql["bevforce_dest"]->error);

	if($users->num_rows){

		while($row = $users->fetch_object()) {
			echo json_encode($row, JSON_PRETTY_PRINT);
		}
	} else {
		echo colorize("Nothing found","WARNING");
	}

	echo "\n";
	echo colorize("Found " . $users->num_rows . " user(s)","SUCCESS");		

	$users->free();

	endscript();