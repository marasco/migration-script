<?php 
	
	$connections = (object)[
		"bevforce_users" => ['localhost','root','eNWM@[v5FC^y'],
		"bevforce_dest" => ['localhost','root','eNWM@[v5FC^y']
	];

	$truncates = (object)[
		"bevforce_dest" => ['users']
	];

	include_once "includes/functions.php";
	include "includes/routine.php";

	// Users and roles
	$users = $mysql["bevforce_users"]->query("SELECT users.*, users_roles.rid, role.name AS role 
		FROM users 
		LEFT JOIN users_roles ON users_roles.uid = users.uid 
		LEFT JOIN role ON role.rid = users_roles.rid 
		GROUP BY users.uid 
		ORDER BY users.uid DESC 
		") or die($mysql["bevforce_users"]->error);

	$total = $users->num_rows;

	while($row = $users->fetch_object()) {

		//echo $row->uid . ",";
		$title = "";
		$bio = "";
		$name = "";
		$zip = "";
		$work = "";
		$address = "";
		$linkedin = "";
		$salesforce = "";

		// Users and roles
		$extra = $mysql["bevforce_users"]->query("SELECT `key`, `value`  
			FROM bf_users_options 
			WHERE `key` IN('about','address','company_name','first_name','last_name','linkedin','salesForceId','zip') 
			AND uid = {$row->uid}
			") or die($mysql["bevforce_users"]->error);

		$extras = [];
		while($row2 = $extra->fetch_object()) {
			$extras[$row2->key] = $row2->value;
		}

		// embeded users object
		$data = (object) @unserialize($row->data);

		if(!empty($extras->first_name)){
			$name.= $extras->first_name;
		}

		if(!empty($extras->last_name)){
			$name.= $extras->last_name;
		}

		if(!empty($extras->company_name)){
			$work.= $extras->company_name;
		}

		if(!empty($extras->zip)){
			$zip.= $extras->zip;
		}

		if(!empty($extras->about)){
			$bio.= $extras->about;
		}

		if(!empty($extras->last_job_title)){
			$title.= $extras->last_job_title;
		}	

		if(!empty($extras->linkedin)){
			$linkedin.= $extras->linkedin;
		}	

		if(!empty($extras->salesForceId)){
			$salesforce.= $extras->salesForceId;
		}	

		if(trim($name)==""){
			if(!empty($data->first_name)){
				$name.= $data->first_name;
			} 

			if(!empty($data->uf_first_name)) {
				$name.= $data->uf_first_name;
			}

			if(!empty($data->uf_last_name)) {
				$name.= " " . $data->uf_last_name;
			}		
		}

		if(trim($work) == "" AND !empty($data->uf_company_name) AND trim($data->uf_company_name) != ""){
			$work.= $data->uf_company_name;
		}

		if(trim($zip) == "" AND !empty($data->uf_zip)){
			$zip.= $data->uf_zip;
		}

		if(!empty($data->uf_city)){
			$address.= " " . $data->uf_city;
		}

		if(!empty($data->uf_state)){
			$address.= ", " . $data->uf_state;
		}

		$name = trim(addslashes($name));
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
		
		$sql = "INSERT INTO users 
			(id,role, name, address, zip_code, work, title, email, biography, linkedin_id, salesforce_id, profile_picture, password, status, created_at, updated_at) VALUES
			($row->uid, '{$row->role}', '{$name}', '{$address}', '{$zip}', '{$work}', '{$title}', '{$email}','{$bio}','{$linkedin}','{$salesforce}','{$row->picture}', '{$row->pass}', 'migration', NOW(), NOW())
		";

		$insert_row = $mysql["bevforce_dest"]->query($sql) OR $errors[] = $sql . ' => ' . $mysql["bevforce_dest"]->error;

		if($insert_row){
			$inserted++;
		}

		show_progress($inserted, $total);
	}

	$users->free();
	
	show_status($errors, $inserted, $total);

	endscript();