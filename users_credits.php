<?php 

	$startIn = 0;
	$startId = 0;
	$minIdQuery = '';
	require_once 'config.db.php';
	include_once "includes/functions.php";
	include_once "includes/routine.php";
	$inserted=0; 
	if (empty($brand))
		$brand = 'bevforce';

	$users = $mysql[$db_source]->query("SET sql_mode = ''"); 
	$users = $mysql[$db_destination]->query("SELECT user_id 
		FROM companies
		") or die($mysql[$db_source]->error);
	$total = $users->num_rows;

	while($row = $users->fetch_object()) {

		// Users and roles
		$row_data = $mysql[$db_source]->query("SELECT data 
		FROM users  
		WHERE uid = {$row->user_id}
		")->fetch_object()->data or die($mysql[$db_source]->error);

		$data = (object) @unserialize($row_data);
		$credits = 0;

		if( ! empty($data->bf_user_credits)){
			$credits = (int) $data->bf_user_credits;
		}

		$sql = "UPDATE companies SET credits = {$credits} WHERE user_id = {$row->user_id}";

		try { 
			$insert_row = $mysql[$db_destination]->query($sql);
			$inserted++;
		} catch (\Exception $e){
			$failed++;
			$errors[] = $sql . ' => ' . $e->getMessage();
		}

		show_progress($inserted, $total);
	}

	$users->free();
	
	show_status($errors, $inserted, $total);

	endscript();