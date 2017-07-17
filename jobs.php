<?php 
	
	$connections = (object)[
		"bevforce_jobs" => ['localhost','root','eNWM@[v5FC^y'],
		"bevforce_dest" => ['localhost','root','eNWM@[v5FC^y']
	];

	$truncates = (object)[
		"bevforce_dest" => ['companies','post_jobs','job_employment_types']
	];	

	include_once "functions.php";
	include "routine.php";

	// Users and roles
	$jobs = $mysql["bevforce_jobs"]->query("SELECT content_type_job.*, node.title, node.uid 
		FROM content_type_job
		LEFT JOIN node ON node.nid = content_type_job.nid 
		GROUP BY content_type_job.nid 
		LIMIT 1000
		") OR die($mysql["bevforce_jobs"]->error);

	// WHERE users.uid = 110718
	$total = $jobs->num_rows;

	// Aux 1
	$states = [];
	$job_states = $mysql["bevforce_dest"]->query("SELECT * FROM job_states") OR die($mysql["bevforce_dest"]->error);

	while($row = $job_states->fetch_object()) {
		$states[$row->name] = $row->id;
	}

	// Aux 2
	$types = [];
	$job_types = $mysql["bevforce_dest"]->query("SELECT * FROM employment_types") OR die($mysql["bevforce_dest"]->error);

	while($row = $job_types->fetch_object()) {
		$types[trim(str_replace(' ','-',$row->name))] = $row->id;
	}

	while($row = $jobs->fetch_object()) {

		$company_id = 0;
		$company = "";
		$title = addslashes($row->title);
		$city = addslashes($row->field_city_value);
		$brand = addslashes($row->field_brand_name_value);
		$description = addslashes($row->field_job_requirements_value);

		if(strlen($row->field_brand_name_value)){
			$company = $row->field_brand_name_value;
		} else if(strlen($row->field_name_value)){
			$company = $row->field_name_value;
		}

		if(strlen($company)){
			$company = trim(addslashes($company));
			$company_result = $mysql["bevforce_dest"]->query("SELECT id FROM companies WHERE name = '{$company}' LIMIT 1") OR die($mysql["bevforce_dest"]->error);

			if($company_result->num_rows){
				$company_id = $company_result->fetch_object()->id;
			} else {
				
				$logo = "";
				$logo_result = $mysql["bevforce_jobs"]->query("SELECT filepath FROM bf_files WHERE uid = '{$row->uid}' AND type = 'other' LIMIT 1") OR die($mysql["bevforce_jobs"]->error);

				if($logo_result->num_rows){
					$logo = $logo_result->fetch_object()->filepath;
				}

				$sql = "INSERT INTO companies SET name = '{$company}', logo = '{$logo}'";
				$company_id = $mysql["bevforce_dest"]->query($sql) OR die($mysql["bevforce_dest"]->error);
			}
		}

		// jobs
		$sql = "INSERT INTO post_jobs SET 
			user_id = '{$row->uid}',
			company_id = '{$company_id}',
			title = '{$title}',
			city = '{$city}',
			brand = '{$brand}',
			state_id = '{$row->field_state_value}',
			zip_code = '{$row->field_zip_value}',
			description = '{$description}',
			status = '{$row->field_job_status_value}',
			created_at = NOW(),
			updated_at = NOW()";

		$insert_row_id = $mysql["bevforce_dest"]->query($sql) OR $errors[] = $sql . ' => ' . $mysql["bevforce_dest"]->error;

		if($insert_row_id){

			// employment type
			$employment_type = $types[$row->field_type_value];

			if($employment_type){
				$sql = "INSERT INTO job_employment_types SET job_id = '{$insert_row_id}',employment_type_id = '{$employment_type}'";
				$mysql["bevforce_dest"]->query($sql) OR die($mysql["bevforce_dest"]->error);
			} else {
				print colorize("Note: Employment type not found: " . $row->field_type_value,"WARNING");
			}

			$inserted++;
		}

		show_progress($inserted, $total);
	}

	$jobs->free();

	show_status($errors, $inserted, $total);

	endscript();