<?php 

	$connections = (object)[
		"bevforce_dest" => ['localhost','root','eNWM@[v5FC^y']
	];

	include_once "functions.php";
	include "routine.php";

	$id = !empty($options['i'])?$options['i']:0;
	$name = !empty($options['n'])?$options['n']:0;

	if(!$id AND !$name){
		print colorize("No id or name provided. Please use -id 123 || -name John","FAILURE");
		exit;
	}


	$where = [];
	if($id) $where[]= "post_jobs.id = " . $id;	
	if($name) $where[]= "(post_jobs.title LIKE '%" . $name . "' OR companies.name LIKE '%" . $name . "' )";
	$wheresql = implode(" AND ", $where);

	// Users and roles
	$jobs = $mysql["bevforce_dest"]->query("

		SELECT post_jobs.*, companies.name AS company_name, employment_types.name AS employment_type  
		FROM post_jobs
		LEFT JOIN companies ON companies.id = post_jobs.company_id 
		LEFT JOIN job_employment_types ON job_employment_types.job_id = post_jobs.id 
		LEFT JOIN employment_types ON employment_types.id = job_employment_types.employment_type_id
		WHERE {$wheresql}
		GROUP BY post_jobs.id

		") OR die($mysql["bevforce_dest"]->error);

	$json = [];
	if($jobs->num_rows){

		while($row = $jobs->fetch_object()) {
			$company = $mysql["bevforce_dest"]->query("
			SELECT * FROM companies
			WHERE id = '{$row->company_id}'") OR die($mysql["bevforce_dest"]->error);

			$applications = $mysql["bevforce_dest"]->query("
			SELECT users.*, user_cover_letter.path_file AS cover_letter, user_resumes.path_file AS resume 
			FROM users
			LEFT JOIN job_applications ON job_applications.user_id = users.id 
			LEFT JOIN user_cover_letter ON user_cover_letter.id = job_applications.cover_letter_id 
			LEFT JOIN user_resumes ON user_resumes.user_id = job_applications.resume_id  
			WHERE job_applications.job_id = '{$row->id}'") OR die($mysql["bevforce_dest"]->error);

			$applicants = [];
			while($applicant = $applications->fetch_object()) {
				$applicants[] = $applicant;
			}

			$row->company = $company->fetch_object();
			$row->applicants = $applicants;

			$company->free();
			$applications->free();

			$json[]= $row;
		}

		echo json_encode($json, JSON_PRETTY_PRINT);
		echo "\n";
		echo colorize("Found " . $jobs->num_rows . " job(s)","SUCCESS");

	} else {
		echo colorize("Nothing found","WARNING");
	}
	
	$jobs->free();

	endscript();