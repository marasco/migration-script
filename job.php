<?php 

	$connections = (object)[
		"bevforce_jobs" => ['localhost','root','eNWM@[v5FC^y']
	];

	include_once "includes/functions.php";
	include "includes/routine.php";

	$mysql["bevforce_dest"]->set_charset("utf8");

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

		SELECT post_jobs.*, companies.name AS company_name, 
		manufacturing_types.name AS manufacturing_type, 
		beverage_types.name AS beverage_type 
		FROM post_jobs
		LEFT JOIN companies ON companies.id = post_jobs.company_id 
		LEFT JOIN manufacturing_types ON manufacturing_types.id = post_jobs.manufacturing_type_id 
		LEFT JOIN beverage_types ON beverage_types.id = post_jobs.beverage_type_id 
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

			$areas_result = $mysql["bevforce_dest"]->query("
			SELECT job_areas.id, areas.name 
			FROM job_areas
			LEFT JOIN areas ON job_areas.area_id = areas.id 
			WHERE job_areas.job_id = '{$row->id}'") OR die($mysql["bevforce_dest"]->error);

			$areas = [];
			while($area = $areas_result->fetch_object()) {
				$areas[] = $area->name;
			}

			$employment_types_result = $mysql["bevforce_dest"]->query("
			SELECT job_employment_types.id, employment_types.name 
			FROM job_employment_types
			LEFT JOIN employment_types ON job_employment_types.employment_type_id = employment_types.id 
			WHERE job_employment_types.job_id = '{$row->id}'") OR die($mysql["bevforce_dest"]->error);

			$employment_types = [];
			while($employment_type = $employment_types_result->fetch_object()) {
				$employment_types[] = $employment_type->name;
			}

			$row->company = $company->fetch_object();
			$row->applicants = $applicants;
			$row->areas = $areas;
			$row->employment_types = $employment_types;

			$company->free();
			$applications->free();
			$employment_types_result->free();
			$areas_result->free();

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