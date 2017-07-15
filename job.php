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

	//Open a new connection to the MySQL server
	$mysql["bevforce_jobs"] = new mysqli('localhost','root','eNWM@[v5FC^y','bevforce_dest');

	//Output any connection error
	if ($mysql["bevforce_jobs"]->connect_error) {
	    die('Error : ('. $mysql["bevforce_jobs"]->connect_errno .') '. $mysql["bevforce_jobs"]->connect_error);
	}

	$where = [];
	if($id) $where[]= "post_jobs.id = " . $id;	
	if($name) $where[]= "(post_jobs.title LIKE '%" . $name . "' OR companies.name LIKE '%" . $name . "' )";
	$wheresql = implode(" AND ", $where);

	// Users and roles
	$jobs = $mysql["bevforce_jobs"]->query("

		SELECT post_jobs.*, companies.name as company, employment_types.name as employment_type  
		FROM post_jobs
		LEFT JOIN companies ON companies.id = post_jobs.company_id 
		LEFT JOIN job_employment_types ON job_employment_types.job_id = post_jobs.id 
		LEFT JOIN employment_types ON employment_types.id = job_employment_types.employment_type_id
		WHERE {$wheresql}
		GROUP BY post_jobs.id

		") OR die($mysql["bevforce_jobs"]->error);

	if($jobs->num_rows){
		print colorize("Found " . $jobs->num_rows . " job(s)","SUCCESS");

		while($row = $jobs->fetch_object()) {
			print json_encode($row, JSON_PRETTY_PRINT);
		}
	} else {
		print colorize("Nothing found","WARNING");
	}

	print "\n";

	$jobs->free();

	endscript();