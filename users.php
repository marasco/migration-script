<?php 

	$startIn = 0;
	$startId = 0;
	$minIdQuery = '';
	require_once 'config.db.php';
 
	$truncates = (object)[
		$db_destination => ['users']
	];

	include_once "includes/functions.php";
	include_once "includes/routine.php";
	$inserted=0; 
	if (empty($brand))
		$brand = 'bevforce';

	if (empty($stringLimit)){
		$stringLimit = "";
		$stringLimit = startscript($stringLimit);
	}

	if (!empty($startId)){
		$minIdQuery = ' AND uid > '.$startId.' ';
	}
	// Users and roles
	$users = $mysql[$db_source]->query("SET sql_mode = ''"); 
	$users = $mysql[$db_source]->query("SELECT users.*, users_roles.rid, role.name AS role 
		FROM users 
		LEFT JOIN users_roles ON users_roles.uid = users.uid 
		LEFT JOIN role ON role.rid = users_roles.rid 
		{$minIdQuery} 
		GROUP BY users.uid 
		ORDER BY users.uid DESC
		 {$stringLimit}
		") or die($mysql[$db_source]->error);
	$total = $users->num_rows;

	while($row = $users->fetch_object()) {
		if (isset($startIn) && $inserted<$startIn){
			$inserted++;
			continue;
		} 
		if ($row->role != 'master employer' && $row->role != 'job_seeker'){
			// get off
			continue;
		}

		//echo $row->uid . ",";
		$title = "";
		$bio = "";
		$name = "";
		$phone = "";
		$last_name = "";
		$zip = "";
		$work = "";
		$picture = $row->picture;
		$address = "";
		$linkedin = "";
		$salesforce = "";
		$role = 'candidate';

		// Users and roles
		$extra = $mysql[$db_source]->query("SELECT `key`, `value`  
			FROM bf_users_options 
			WHERE `key` IN('about','address','company_name','first_name','last_name', 'salesForceId', 'zip', 'city', 'state', 'phone', 'user_title', 'employees', 'last_job_employer', 'last_job_title') 
			AND uid = {$row->uid}
			") or die($mysql[$db_source]->error);

		$extras = [];
		while($row2 = $extra->fetch_object()) {
			$extras[$row2->key] = $row2->value;
		}

		// embeded users object
		$data = (object) @unserialize($row->data);
		
		if ($row->role == 'master employer'){
			$role = 'client';
		}

		// bf_users_options
		if(!empty($extras['first_name'])){
			$name = $extras['first_name'];
		}

		if(!empty($extras['last_name'])){
			$last_name = $extras['last_name'];
		}

		if(!empty($extras['phone'])){
			$phone = $extras['phone'];
		}

		if(!empty($extras['address'])){
			$address = $extras['address'];
		}

		if(!empty($extras['company_name'])){
			$work = $extras['company_name'];
		}

		if(!empty($extras['zip'])){
			$zip.= $extras['zip'];
		}

		if(!empty($extras['about'])){
			$bio.= $extras['about'];
		}

		if(!empty($extras['user_title'])){
			$title.= $extras['user_title'];
		}	

		if(!empty($extras['salesForceId'])){
			$salesforce.= $extras['salesForceId'];
		}	

		if(empty(trim($work)) && !empty($extras['last_job_employer'])){
			$work = $extras['last_job_employer'];
		}
		if(empty(trim($title)) && !empty($extras['last_job_title'])){
			$title = $extras['last_job_title'];
		}


		// users.data
		if(trim($name)=="" && !empty($data->first_name)){
			$name.= $data->first_name;
		}
		if(trim($name) == '' && !empty($data->uf_first_name)) {
			$name.= $data->uf_first_name;
		}
		if(trim($last_name) && !empty($data->uf_last_name)) {
			$last_name = $data->uf_last_name;
		} 

		if(trim($work) == "" AND !empty($data->uf_company_name) AND trim($data->uf_company_name) != ""){
			$work = $data->uf_company_name;
		}

		if(trim($zip) == "" AND !empty($data->uf_zip)){
			$zip.= $data->uf_zip;
		}

		 


		$name = trim(addslashes($name));
		$last_name = trim(addslashes($last_name));
		$phone = trim(addslashes($phone));
		$title = trim(addslashes($title));
		$zip = trim(addslashes($zip));
		$work = trim(addslashes($work));
		$address = trim(addslashes($address));
		$bio = trim(addslashes($bio));
		$linkedin = trim(addslashes($linkedin));
		$salesforce = trim(addslashes($salesforce));
		$email = strtolower($row->mail);

		if(trim($name)=="" AND !empty($row->name)){
			$name = trim(addslashes($row->name));
		}
		if($row->role=="master employer"){
			$company_id = 0;
			$logo = "";

			// 
			//$company_result = $mysql[$db_destination]->query("SELECT id FROM companies WHERE LOWER(name) = '" . strtolower($work) . "' LIMIT 1") OR die($mysql[$db_destination]->error);

			// force to create
			//if(1 || ! $company_result->num_rows){
				$logo_result = $mysql[$db_source]->query("SELECT filepath FROM bf_files WHERE uid = '{$row->uid}' AND type = 'other' LIMIT 1") OR die($mysql[$db_source]->error);

				if($logo_result->num_rows){
					$logo = $logo_result->fetch_object()->filepath;
					$path_parts = pathinfo($logo);
					$logo = $path_parts['basename'];
				}

				$sql = "INSERT INTO companies SET name = '{$work}', user_id = '{$row->uid}', logo = '{$logo}', linkedin_url = '{$linkedin}'";
				$company_id = $mysql[$db_destination]->query($sql) OR die($mysql[$db_destination]->error);
			//}
		}

		$sql = "INSERT INTO users 
			(id,role, name, last_name, address, zip_code, work, title, email, biography, salesforce_id, profile_picture, password, status, created_at, updated_at,verified, brand) VALUES
			($row->uid, '{$role}', '{$name}', '{$last_name}', '{$address}', '{$zip}', '{$work}', '{$title}', '{$email}','{$bio}','{$salesforce}','{$picture}', '{$row->pass}', 'active', NOW(), NOW(),1, '{$brand}')
		";
		try { 
			$insert_row = $mysql[$db_destination]->query($sql); // OR $errors[] = $sql . ' => ' . $mysql[$db_destination]->error;
		} catch (\Exception $e){
			$failed++;
			$errors[] = $sql . ' => ' . $e->getMessage();
		}

		// pass, not inserted really
		$inserted++;

		show_progress($inserted, $total);
	}

	$users->free();
	
	show_status($errors, $inserted, $total);

	endscript();
