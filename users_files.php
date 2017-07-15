<?php 

	$connections = (object)[
		"bevforce_users" => ['localhost','root','eNWM@[v5FC^y'],
		"bevforce_dest" => ['localhost','root','eNWM@[v5FC^y']
	];

	$truncates = (object)[
		"bevforce_dest" => ['user_resumes','user_cover_letter']
	];

	include_once "functions.php";
	include "routine.php";

	// Users and roles
	$users = $mysql["bevforce_users"]->query("SELECT *
		FROM bf_files
		WHERE type IN('cover-letter','resume') 
		GROUP BY fid 
		") or die($mysql["bevforce_users"]->error);

	// WHERE users.uid = 110718

	$total = $users->num_rows;

	while($row = $users->fetch_object()) {

		$table = "";

		if($row->type == 'resume'){
			$table = "user_resumes";
		} else if($row->type == 'cover-letter'){
			$table = "user_cover_letter";
		}

		if(strlen($table)){
			$sql = "INSERT INTO {$table} 
				(id, user_id,name_file, path_file, created_at, updated_at) VALUES
				($row->fid, $row->uid, '{$row->filename}', '{$row->fileurl}', NOW(), NOW())
			";
			$insert_row = $mysql["bevforce_dest"]->query($sql) OR $errors[] = $sql . ' => ' . $mysql["bevforce_dest"]->error;
		}

		if($insert_row){
			$inserted++;
		}

		print show_progress($inserted, $total);
	}

	$users->free();
	
	print show_status($errors, $inserted, $total);

	endscript();