<?php 
	
	require_once 'config.db.php';


	$truncates = (object)[
		$db_destination => ['user_resumes', 'user_cover_letter']
	];

	include_once "includes/functions.php";
	include_once "includes/routine.php";


	if (empty($stringLimit)){
		$stringLimit = " ";
		$stringLimit = startscript($stringLimit);
	}

	// Users and roles
	$users = $mysql[$db_source]->query("
	SELECT *
	FROM bf_files
	WHERE type IN('cover-letter','resume') 
	GROUP BY fid {$stringLimit} 
	") or die($mysql[$db_source]->error);

	// WHERE users.uid = 110718

	$total = $users->num_rows;

	while($row = $users->fetch_object()) {

		$table = "";

		if($row->type == 'resume'){
			$table = "user_resumes";
		} else if($row->type == 'cover-letter'){
			$table = "user_cover_letter";
		}else{
			continue;
		}

		if(strlen($table)){
			$sql = "INSERT INTO {$table} 
			(id, user_id,name_file, path_file, created_at, updated_at) VALUES
			($row->fid, $row->uid, '{$row->filename}', '{$row->fileurl}', NOW(), NOW())
			";
			
			try { 
				$insert_row = $mysql[$db_destination]->query($sql); // OR $errors[] = $sql . ' => ' . $mysql[$db_destination]->error;
			} catch (\Exception $e){
				$errors[] = $sql . ' => ' . $e->getMessage();
			}
		}

		if($insert_row){
			$inserted++;
		}

		show_progress($inserted, $total);
	}

	$users->free();
	
	show_status($errors, $inserted, $total);

	endscript();