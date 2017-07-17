<?php 

	$connections = (object)[
		"bevforce_dest" => ['localhost','root','eNWM@[v5FC^y','bevforce']
	];

	include_once "includes/functions.php";
	include "includes/routine.php";

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
		LEFT JOIN user_cover_letter ON user_cover_letter.user_id = users.id 
		LEFT JOIN user_resumes ON user_resumes.user_id = users.id 
		WHERE {$wheresql}
		GROUP BY users.id

		") OR die($mysql["bevforce_dest"]->error);

	$json = [];
	if($users->num_rows){

		while($row = $users->fetch_object()) {

			$applications = $mysql["bevforce_dest"]->query("
			SELECT job_applications.*, user_cover_letter.path_file AS cover_letter, user_resumes.path_file AS resume,
			post_jobs.title, post_jobs.brand  
			FROM job_applications
			LEFT JOIN post_jobs ON post_jobs.id = job_applications.job_id 
			LEFT JOIN user_cover_letter ON user_cover_letter.id = job_applications.cover_letter_id 
			LEFT JOIN user_resumes ON user_resumes.user_id = job_applications.resume_id  
			WHERE job_applications.user_id = '{$row->id}'") OR die($mysql["bevforce_dest"]->error);

			$apps = [];
			while($app = $applications->fetch_object()) {
				$apps[] = $app;
			}

			$row->applications = $apps;
			$applications->free();

			$json[]= $row;
		}

		echo json_encode($json, JSON_PRETTY_PRINT);
		echo "\n";
		echo colorize("Found " . $users->num_rows . " user(s)","SUCCESS");		

	} else {
		echo colorize("Nothing found","WARNING");
	}

	$users->free();

	endscript();