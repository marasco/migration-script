<?php 
	
	$connections = (object)[
		"bevforce" => ['localhost','root','eNWM@[v5FC^y'],
		"bevforce_dest" => ['localhost','root','eNWM@[v5FC^y']
	];

	$truncates = (object)[
		"bevforce_dest" => ['companies','post_jobs','job_employment_types','manufacturing_types','beverage_types','areas']
	];	

	include_once "includes/functions.php";
	include "includes/routine.php";

	// Main list
	$jobs = $mysql["bevforce"]->query("SELECT node.*, content_type_job.*, 
		node_revisions.title as job_title, node_revisions.body as job_description
		FROM node
		LEFT JOIN node_revisions ON node_revisions.nid = node.nid  
		LEFT JOIN content_type_job ON node.nid = content_type_job.nid 
		WHERE node.type = 'job' 
		GROUP BY content_type_job.nid 
		") OR die($mysql["bevforce"]->error);

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

		// bev type, industry and 

		$company_id = 0;
		$company = "";

		$area = "";
		$area_id = 0;

		$manufacturing_type = "";
		$manufacturing_type_id = 0;

		$beverage_type = "";
		$beverage_id = 0;

		$terms = $mysql["bevforce"]->query("SELECT term_node.*, term_data.name as value,term_data.vid as node_type
		FROM term_node 
		LEFT JOIN term_data ON term_data.tid = term_node.tid 
		WHERE term_node.nid = '{$row->nid}'
		") OR die($mysql["bevforce"]->error);

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
			}
		}

		$terms->free();

		if(!empty($row->job_title)){
			$title = $row->job_title;
		} else {
			$title = addslashes($row->title);
		}

		$city = addslashes($row->field_city_value);
		$brand = addslashes($row->field_brand_name_value);
		$description = addslashes($row->job_description);
		$requirements = addslashes($row->field_job_requirements_value);

		// manufacturing_type

		if(strlen($area)){
			$manufacturing_type = trim(addslashes($area));
			$area_result = $mysql["bevforce_dest"]->query("SELECT id FROM areas WHERE name = '{$area}' LIMIT 1") OR die($mysql["bevforce_dest"]->error);

			if($area_result->num_rows){
				$area_id = $area_result->fetch_object()->id;
			} else {
				$sql = "INSERT INTO areas SET name = '{$area}'";
				$area_id = $mysql["bevforce_dest"]->query($sql) OR die($mysql["bevforce_dest"]->error);				
			}
		}

		if(strlen($beverage_type)){
			$beverage_type = trim(addslashes($beverage_type));
			$beverage_result = $mysql["bevforce_dest"]->query("SELECT id FROM beverage_types WHERE name = '{$beverage_type}' LIMIT 1") OR die($mysql["bevforce_dest"]->error);

			if($beverage_result->num_rows){
				$beverage_id = $beverage_result->fetch_object()->id;
			} else {
				$sql = "INSERT INTO beverage_types SET name = '{$beverage_type}'";
				$beverage_id = $mysql["bevforce_dest"]->query($sql) OR die($mysql["bevforce_dest"]->error);				
			}
		}

		if(strlen($manufacturing_type)){
			$manufacturing_type = trim(addslashes($manufacturing_type));
			$industry_type_result = $mysql["bevforce_dest"]->query("SELECT id FROM manufacturing_types WHERE name = '{$manufacturing_type}' LIMIT 1") OR die($mysql["bevforce_dest"]->error);

			if($industry_type_result->num_rows){
				$manufacturing_type_id = $industry_type_result->fetch_object()->id;
			} else {
				$sql = "INSERT INTO manufacturing_types SET name = '{$manufacturing_type}'";
				$manufacturing_type_id = $mysql["bevforce_dest"]->query($sql) OR die($mysql["bevforce_dest"]->error);				
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
			$company_result = $mysql["bevforce_dest"]->query("SELECT id FROM companies WHERE name = '{$company}' LIMIT 1") OR die($mysql["bevforce_dest"]->error);

			if($company_result->num_rows){
				$company_id = $company_result->fetch_object()->id;
			} else {
				
				$logo = "";
				$logo_result = $mysql["bevforce"]->query("SELECT filepath FROM bf_files WHERE uid = '{$row->uid}' AND type = 'other' LIMIT 1") OR die($mysql["bevforce"]->error);

				if($logo_result->num_rows){
					$logo = $logo_result->fetch_object()->filepath;
				}

				$sql = "INSERT INTO companies SET name = '{$company}', logo = '{$logo}'";
				$company_id = $mysql["bevforce_dest"]->query($sql) OR die($mysql["bevforce_dest"]->error);
			}
		}

		$created = date('Y-m-d H:i:s', $row->created);
		$changed = date('Y-m-d H:i:s', $row->changed);
		$expired = date('Y-m-d H:i:s', strtotime($row->field_job_expiration_value));

		// jobs
		$sql = "INSERT INTO post_jobs SET 
		user_id = '{$row->uid}',
		company_id = '{$company_id}',
		manufacturing_type_id = '{$manufacturing_type_id}',
		title = '{$title}',
		city = '{$city}',
		brand = '{$brand}',
		state_id = '{$row->field_state_value}',
		zip_code = '{$row->field_zip_value}',
		reports_to = '{$row->field_job_reports_to_value}',
		of_reports = '{$row->field_job_direct_reports_value}',
		description = '{$description} {$requirements}',
		status = '{$row->field_job_status_value}',
		redirect_to_company_job_board_post = '{$row->field_external_job_board_value}',
		expired_date = '{$expired}',
		created_at = '{$created}',
		updated_at = '{$changed}'
		";

		$insert_row_id = $mysql["bevforce_dest"]->query($sql) OR $errors[] = $sql . ' => ' . $mysql["bevforce_dest"]->error;

		if($insert_row_id){

			// employment type
			$employment_type = !empty($types[$row->field_type_value])?$types[$row->field_type_value]:0;

			if($employment_type){
				$sql = "INSERT INTO job_employment_types SET job_id = '{$insert_row_id}',employment_type_id = '{$employment_type}'";
				$mysql["bevforce_dest"]->query($sql) OR die($mysql["bevforce_dest"]->error);
			} else {
				print colorize("Note: Employment type not found: " . $row->field_type_value,"WARNING");
				echo "\n";
			}

			$inserted++;
		}

		show_progress($inserted, $total);
	}

	$jobs->free();

	show_status($errors, $inserted, $total);

	endscript();