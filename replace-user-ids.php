<?php 

	$startIn = 0;
	$startId = 0;
	$minIdQuery = '';
	$testLimit = ' ';
	require_once 'config.db.php'; 
	include_once "includes/functions.php";
	include_once "includes/routine.php"; 
	$users = $mysql[$db_source]->query("SET sql_mode = ''"); 

/*
	$users = $mysql[$db_source]->query("UPDATE users SET uid = (uid + 999999) $testLimit") or die($mysql[$db_source]->error); 
	
	$users = $mysql[$db_source]->query("UPDATE bf_users_options SET uid = (uid + 999999) $testLimit") or die($mysql[$db_source]->error); 
	
*/
	$users = $mysql[$db_source]->query("UPDATE node SET nid = (nid + 999999) $testLimit") or die($mysql[$db_source]->error); 
	
	$users = $mysql[$db_source]->query("UPDATE content_type_job SET nid = (nid + 999999) $testLimit") or die($mysql[$db_source]->error); 
	
	$users = $mysql[$db_source]->query("UPDATE bf_job_applications SET nid = (nid + 999999) $testLimit") or die($mysql[$db_source]->error); 
/*
	$users = $mysql[$db_source]->query("UPDATE bf_files SET nid = (nid + 999999) $testLimit") or die($mysql[$db_source]->error); */

	//$users->free();
	 
