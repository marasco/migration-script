<?php 
	
	$connections = (object)[
		"bevforce_jobs" => ['localhost','root','eNWM@[v5FC^y'],
		"bevforce_dest" => ['localhost','root','eNWM@[v5FC^y']
	];

	$truncates = (object)[
		"bevforce_dest" => ['job_applications']
	];

	include_once "functions.php";
	include "routine.php";

	// Users and roles
	$job_applications = $mysql["bevforce_jobs"]->query("
	SELECT bf_job_applications.*, node.title 
	FROM bf_job_applications
	LEFT JOIN node ON node.nid = bf_job_applications.nid 
	GROUP BY bf_job_applications.aid 
	LIMIT 1000
	") OR die($mysql["bevforce_jobs"]->error);

	$total = $job_applications->num_rows;

	while($row = $job_applications->fetch_object()) {

		$message = addslashes($row->additional_info);

		// jobs
		$sql = "INSERT INTO job_applications SET 
		user_id = '{$row->uid}',
		job_id = '{$row->nid}',
		resume_id = '{$row->resume_fid}',
		cover_letter_id = '{$row->cover_letter_fid}',
		message = '{$message}',
		status = '{$row->status}',
		created_at = NOW(),
		updated_at = NOW()";

		$insert_row_id = $mysql["bevforce_dest"]->query($sql) OR $errors[] = $sql . ' => ' . $mysql["bevforce_dest"]->error;

		if($insert_row_id){
			$inserted++;
		}

		show_progress($inserted, $total);
	}

	$job_applications->free();

	show_status($errors, $inserted, $total);

	endscript();