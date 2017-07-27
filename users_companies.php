<?php 
	
	require_once 'config.db.php';

	include_once "includes/functions.php";
	include_once "includes/routine.php";

	// Users and roles
	$columns_result = $mysql[$db_destination]->query("SHOW COLUMNS from companies") or die($mysql[$db_destination]->error);
	$columns = [];

	while($row = $columns_result->fetch_object()) {
		$columns[] = $row->Field;
	}

	if(!in_array('beverage_type_id',$columns)){
		$columns_result = $mysql[$db_destination]->query("ALTER TABLE `companies` ADD `beverage_type_id` int(10) unsigned NULL AFTER `manufactured_type_id`,ADD INDEX (beverage_type_id)") or die($mysql[$db_destination]->error);
	}

	$companies = $mysql[$db_destination]->query("SELECT id, user_id FROM companies") or die($mysql[$db_destination]->error);

	// WHERE users.uid = 110718

	$total = $companies->num_rows;

	while($row = $companies->fetch_object()) {

		$fields = ['about','address','company_name','birthday','beverage','linkedin','facebook', 'zip', 'city', 'state', 'phone', 'country', 'employees', 'industry', 'image','twitter','website'];
		$values = [];
		$extra = $mysql[$db_source]->query("SELECT `key`, `value`  
			FROM bf_users_options 
			WHERE `key` IN('" . implode("','",$fields) ."') AND uid = {$row->user_id}") or die($mysql[$db_source]->error);

		while($row2 = $extra->fetch_object()) {
			$extras[$row2->key] = $row2->value;
		}

		foreach($fields as $field){
			$values[$field] = "";
			if(!empty($extras[$field]) AND trim($extras[$field]) != ""){	
				$values[$field] = $extras[$field];
			}
		}

		extract($values);


		if(!empty($industry)){
			$industry_name = "";
			$industry_name_result = $mysql[$db_source]->query("SELECT name FROM term_data WHERE tid = $industry LIMIT 1") or die($mysql[$db_source]->error);
			if($industry_name_result->num_rows){
				$industry_name = addslashes($industry_name_result->fetch_object()->name);
			}

			if(strlen($industry_name)){
				$industry_result = $mysql[$db_destination]->query("SELECT id FROM manufacturing_types WHERE LOWER(name) = '" . strtolower($industry_name) . "' LIMIT 1") OR die($mysql[$db_destination]->error);
				if($industry_result->num_rows){
					$industry_id = $industry_result->fetch_object()->id;
				} else {
					$industry_id = $mysql[$db_destination]->query("INSERT INTO manufacturing_types SET name = '{$industry_name}'") OR die($mysql[$db_destination]->error);
				}
			}
		}

		if(!empty($beverage)){
			$beverage_name = "";
			$beverage_name_result = $mysql[$db_source]->query("SELECT name FROM term_data WHERE tid = $beverage LIMIT 1") or die($mysql[$db_source]->error);
			if($beverage_name_result->num_rows){
				$beverage_name = addslashes($beverage_name_result->fetch_object()->name);
			}

			if(strlen($beverage_name)){
				$beverage_result = $mysql[$db_destination]->query("SELECT id FROM beverage_types WHERE LOWER(name) = '" . strtolower($beverage_name) . "' LIMIT 1") OR die($mysql[$db_destination]->error);
				if($beverage_result->num_rows){
					$beverage_id = $beverage_result->fetch_object()->id;
				} else {
					$beverage_id = $mysql[$db_destination]->query("INSERT INTO beverage_types SET name = '{$beverage_name}'") OR die($mysql[$db_destination]->error);
				}
			}
		}

		$about = addslashes($about);
		$company_name = addslashes($company_name);
		$address = addslashes($address);
		$city = addslashes($city);
		$country = addslashes($country);

		if(strlen($birthday)){
			$birthday = date('Y') . '/' . $birthday;
			$birthday = str_replace("/", "-", $birthday);
		}

		if(strlen($facebook)){
			$facebook = str_replace(["http://","https://"],"",$facebook);
			$facebook = "https://" . $facebook;
		}

		if(strlen($twitter)){
			$twitter = str_replace(["http://","https://"],"",$twitter);
			$twitter = "https://" . $twitter;
		}

		if(strlen($linkedin)){
			$linkedin = str_replace(["http://","https://"],"",$linkedin);
			$linkedin = "https://" . $linkedin;
		}

		if(strlen($image)){
			$logo_url = $mysql[$db_source]->query("SELECT fileurl FROM bf_files WHERE fid = $image LIMIT 1") or die($mysql[$db_source]->error);
			if($logo_url->num_rows){
				$image = $logo_url->fetch_object()->fileurl;
			}
		}

		$inserted_result = $mysql[$db_destination]->query("UPDATE companies SET 
			name = '{$company_name}',
			beverage_type_id = '{$beverage_id}',
			manufactured_type_id = '{$industry_id}',
			location = '{$address} {$city} {$zip} {$state} {$country}',
			web = '{$website}',
			phone = '{$phone}',
			logo = '{$image}',
			facebook_url = '{$facebook}',
			twitter_url = '{$twitter}',
			linkedin_url = '{$linkedin}',
			birthdate = '{$birthday}',
			number_of_employees = '{$employees}',
			description = '{$about}'
			WHERE user_id = {$row->user_id}
			") OR die($mysql[$db_destination]->error);
		
		if($inserted_result){
			$inserted++;
		}

		show_progress($inserted, $total);
	}

	$companies->free();
	
	show_status($errors, $inserted, $total);

	endscript();
