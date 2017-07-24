<?php 
	
 	require_once 'config.db.php';

	$truncates = (object)[
		$db_destination => ['job_applications']
	];

	include_once "includes/functions.php";
	include "includes/routine.php";
	global $errors;
	if (empty($stringLimit)){
		$stringLimit = "";
		$stringLimit = startscript($stringLimit);
	}
	// Users and roles
	$job_applications = $mysql[$db_source]->query("SET sql_mode = ''"); 
	$job_applications = $mysql[$db_source]->query("
	SELECT bf_job_applications.*, node.title 
	FROM bf_job_applications
	LEFT JOIN node ON node.nid = bf_job_applications.nid 
	GROUP BY bf_job_applications.aid $stringLimit
	") OR die($mysql[$db_source]->error);

	$total = $job_applications->num_rows;

	while($row = $job_applications->fetch_object()) {

		$message = addslashes($row->additional_info);
		$status = 'reject';
		if ($row->status=='published'){
			$status = 'saved';
		}
		// jobs
		$sql = "INSERT INTO job_applications SET 
		user_id = '{$row->uid}',
		job_id = '{$row->nid}',
		resume_id = '{$row->resume_fid}',
		cover_letter_id = '{$row->cover_letter_fid}',
		message = '{$message}',
		status = '{$status}',
		created_at = NOW(),
		updated_at = NOW()";

		try { 
			$insert_row_id = $mysql[$db_destination]->query($sql) OR $errors[] = $sql . ' => ' . $mysql[$db_destination]->error;
		} catch(Exception $e){
			$errors[] = "\r\n".$e->getMessage();
		}


		//if($insert_row_id){
			$inserted++;
		//}

		show_progress($inserted, $total);
	}

	$job_applications->free();

	show_status($errors, $inserted, $total);

	endscript();