<?php 
	
	require_once 'config.db.php';

	$truncates = (object)[
		$db_destination => ['companies','post_jobs','employment_types','job_employment_types','areas','job_areas','manufacturing_types','beverage_types']
	];	

	include_once "includes/functions.php";
	include "includes/routine.php";
	if (empty($stringLimit)){
		$stringLimit = "";
		$stringLimit = startscript($stringLimit);
	}
	// Main list
	$jobs = $mysql[$db_source]->query("SELECT node.*, content_type_job.*, 
		node_revisions.title as job_title, node_revisions.body as job_description
		FROM node
		LEFT JOIN node_revisions ON node_revisions.nid = node.nid  
		LEFT JOIN content_type_job ON node.nid = content_type_job.nid 
		WHERE node.type = 'job' 
		GROUP BY content_type_job.nid 
		 $stringLimit") OR die($mysql[$db_source]->error);

	// WHERE users.uid = 110718
	$total = $jobs->num_rows;

	while($row = $jobs->fetch_object()) {

		// bev type, industry and 

		$company_id = 0;
		$company = "";
		$area = "";
		$area_id = 0;
		$manufacturing_type = "";
		$manufacturing_type_id = 0;
		$beverage_type = "";
		$beverage_type_id = 0;
		$state_id = 0;
		$salary_range = "";
		$status = $row->field_job_status_value;

		switch ($row->field_job_status_value){
			case 'published':
				$status = 'active';
				if ($row->field_job_expiration_value == 'closed'){
					$status = 'closed';
				}elseif (strtotime($row->field_job_expiration_value) < time()){
					$status = 'expired';
				}
			break;
			default:
				$status = 'closed';
				break;
		}
		$city = trim(addslashes($row->field_city_value));
		$brand = trim(addslashes($row->field_brand_name_value));
		$description = trim(addslashes($row->job_description));
		$requirements = trim(addslashes($row->field_job_requirements_value));
		$reports_to = trim(addslashes($row->field_job_reports_to_value));
		$of_reports = trim(addslashes($row->field_job_direct_reports_value));
		$created = date('Y-m-d H:i:s', $row->created);
		$changed = date('Y-m-d H:i:s', $row->changed);
		$expired = date('Y-m-d H:i:s', strtotime($row->field_job_expiration_value));

		$terms = $mysql[$db_source]->query("SELECT term_node.*, term_data.name as value,term_data.vid as node_type
		FROM term_node 
		LEFT JOIN term_data ON term_data.tid = term_node.tid 
		WHERE term_node.nid = '{$row->nid}'
		") OR die($mysql[$db_source]->error);

		while($term = $terms->fetch_object()) {	
			switch($term->node_type){
				case 1: // area 
				$area = $term->value;
				break;
				case 4: // manufacture
				$manufacturing_type = $term->value;				
				break;	
				case 5: // beverage
				$beverage_type = $term->value;				
				break;
				case 6: // employment-type
				$area = $term->value;				
				break;
				default:
				break;
			}
		}

		$terms->free();

		if(!empty($row->job_title)){
			$title = $row->job_title;
		} else {
			$title = $row->title;
		}

		$title = addslashes($title);

		if(!empty($row->field_state_value)){
			$ucfirst = ucfirst($row->field_state_value);
			$upper = strtoupper($row->field_state_value);
			$job_state = $mysql[$db_destination]->query("SELECT id FROM job_states WHERE code = '$upper' OR name = '$ucfirst'") OR die($mysql[$db_destination]->error);

			if($job_state->num_rows){
				$state_id = $job_state->fetch_object()->id;
			} 
		}

		// manufacturing_type
/*
		if(strlen($area)){
			$area = trim(addslashes($area));
			$area_result = $mysql[$db_destination]->query("SELECT id FROM areas WHERE name = '{$area}' LIMIT 1") OR die($mysql[$db_destination]->error);

			if($area_result->num_rows){
				$area_id = $area_result->fetch_object()->id;
			} else {
				$sql = "INSERT INTO areas SET name = '{$area}'";
				$area_id = $mysql[$db_destination]->query($sql) OR die($mysql[$db_destination]->error);				
			}

			// save relation
			$sql = "INSERT INTO job_areas SET job_id = '{$row->nid}', area_id = '{$area_id}'";
			$area_id = $mysql[$db_destination]->query($sql) OR die($mysql[$db_destination]->error);
		}
*/
		// employment type
		if(strlen($row->field_type_value)){
			// employment type
			$employment_type = str_replace('-',' ',trim(addslashes($row->field_type_value)));
			$employment_type_result = $mysql[$db_destination]->query("SELECT id FROM employment_types WHERE name = '{$employment_type}' LIMIT 1") OR die($mysql[$db_destination]->error);

			if($employment_type_result->num_rows){
				$employment_type_id = $employment_type_result->fetch_object()->id;
			} else {
				$sql = "INSERT INTO employment_types SET name = '{$employment_type}'";
				$employment_type_id = $mysql[$db_destination]->query($sql) OR die($mysql[$db_destination]->error);	
			}

			// save relation
			$sql = "INSERT INTO job_employment_types SET job_id = '{$row->nid}', employment_type_id = '{$employment_type_id}'";
			$job_employment_type_id = $mysql[$db_destination]->query($sql) OR die($mysql[$db_destination]->error);			
		}

		// beverage type
		if(strlen($beverage_type)){
			$beverage_type = trim(addslashes($beverage_type));
			$beverage_result = $mysql[$db_destination]->query("SELECT id FROM beverage_types WHERE name = '{$beverage_type}' LIMIT 1") OR die($mysql[$db_destination]->error);

			if($beverage_result->num_rows){
				$beverage_type_id = $beverage_result->fetch_object()->id;
			} else {
				$sql = "INSERT INTO beverage_types SET name = '{$beverage_type}'";
				$beverage_type_id = $mysql[$db_destination]->query($sql) OR die($mysql[$db_destination]->error);				
			}
		}

		// industry type
		if(strlen($manufacturing_type)){
			$manufacturing_type = trim(addslashes($manufacturing_type));
			$manufacturing_type_result = $mysql[$db_destination]->query("SELECT id FROM manufacturing_types WHERE name = '{$manufacturing_type}' LIMIT 1") OR die($mysql[$db_destination]->error);

			if($manufacturing_type_result->num_rows){
				$manufacturing_type_id = $manufacturing_type_result->fetch_object()->id;
			} else {
				$sql = "INSERT INTO manufacturing_types SET name = '{$manufacturing_type}'";
				$manufacturing_type_id = $mysql[$db_destination]->query($sql) OR die($mysql[$db_destination]->error);				
			}
		}

		//companies

		if(strlen($row->field_brand_name_value)){
			$company = $row->field_brand_name_value;
		} else if(strlen($row->field_name_value)){
			$company = $row->field_name_value;
		}

		if(strlen($company)){
			
			$company = trim(addslashes($company));
			$company_result = $mysql[$db_destination]->query("SELECT id FROM companies WHERE name = '{$company}' LIMIT 1") OR die($mysql[$db_destination]->error);

			if($company_result->num_rows){
				$company_id = $company_result->fetch_object()->id;
			} else {
				
				$logo = "";
				$logo_result = $mysql[$db_source]->query("SELECT filepath FROM bf_files WHERE uid = '{$row->uid}' AND type = 'other' LIMIT 1") OR die($mysql[$db_source]->error);

				if($logo_result->num_rows){
					$logo = $logo_result->fetch_object()->filepath;
				}
				$path_parts = pathinfo($logo);
					$logo = $path_parts['basename'];

				$sql = "INSERT INTO companies SET name = '{$company}', logo = '{$logo}'";
				$company_id = $mysql[$db_destination]->query($sql) OR die($mysql[$db_destination]->error);
			}
		}

		if(!empty($row->field_job_base_pay_value)){
			$salary_range = trim(addslashes($row->field_job_base_pay_value));
		}
		$anonymous = (int)$row->field_confidential_value;
		// jobs
		$sql = "INSERT INTO post_jobs SET 
		id = '{$row->nid}',
		user_id = '{$row->uid}',
		company_id = '{$company_id}',
		manufacturing_type_id = '{$manufacturing_type_id}',
		beverage_type_id = '{$beverage_type_id}',
		title = '{$title}',
		city = '{$city}',
		brand = '{$brand}',
		state_id = '{$state_id}',
		zip_code = '{$row->field_zip_value}',
		reports_to = '{$reports_to}',
		of_reports = '{$of_reports}',
		salary_range = '{$salary_range}',
		description = "{$description}\n{$requirements}",
		status = '{$status}',
		redirect_to_company_job_board_post = '{$row->field_external_job_board_value}',
		expired_date = '{$expired}',
		created_at = '{$created}',
		updated_at = '{$changed}',
		post_anonymously = {$anonymous}
		";
		try { 
		$insert_row_id = $mysql[$db_destination]->query($sql);
		} catch(Exception $e){
			$errors[] = $e->getMessage().' / '.$sql . ' => ' . $mysql[$db_destination]->error;

		}
		//if($insert_row_id){
			$inserted++;
		//}

		show_progress($inserted, $total);
	}

	$jobs->free();

	show_status($errors, $inserted, $total);

	endscript();