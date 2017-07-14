<?php 

include "functions.php";

$id = option_value('i');
$name = option_value('n');

if(!$id AND !$name){
	print "No id or name provided. Please use -id 123 || -name John" . "\n";
	exit;
}

//Open a new connection to the MySQL server
$mysqli = new mysqli('localhost','root','eNWM@[v5FC^y','bevforce_dest');

//Output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

$where = [];
if($id) $where[]= "post_jobs.id = " . $id;	
if($name) $where[]= "(post_jobs.title LIKE '%" . $name . "' OR companies.name LIKE '%" . $name . "' )";
$wheresql = implode(" AND ", $where);

// Users and roles
$jobs = $mysqli->query("

	SELECT post_jobs.*, companies.name as company, employment_types.name as employment_type  
	FROM post_jobs
	LEFT JOIN companies ON companies.id = post_jobs.company_id 
	LEFT JOIN job_employment_types ON job_employment_types.job_id = post_jobs.id 
	LEFT JOIN employment_types ON employment_types.id = job_employment_types.employment_type_id
	WHERE {$wheresql}
	GROUP BY post_jobs.id

	") OR die($mysqli->error);

if($jobs->num_rows){
	print "Found " . $jobs->num_rows . " jobs" . "\n";

	while($row = $jobs->fetch_object()) {
		print json_encode($row, JSON_PRETTY_PRINT);
	}
}

print "\n";

$jobs->free();

// close connection 
$mysqli->close();