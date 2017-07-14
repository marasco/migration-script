<?php 

include "functions.php";

//Open a new connection to the MySQL server
$mysqli = new mysqli('localhost','root','eNWM@[v5FC^y','bevforce_jobs');
$mysqli2 = new mysqli('localhost','root','eNWM@[v5FC^y','bevforce_dest');

//Output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

//Output any connection error
if ($mysqli2->connect_error) {
    die('Error : ('. $mysqli2->connect_errno .') '. $mysqli2->connect_error);
}

// Users and roles
$job_applications = $mysqli->query("

	SELECT bf_job_applications.*, node.title, node.uid 
	FROM bf_job_applications
	LEFT JOIN node ON node.nid = bf_job_applications.nid 
	GROUP BY bf_job_applications.aid 

	") OR die($mysqli->error);

// WHERE users.uid = 110718
$total = $job_applications->num_rows;
$inserted = 0;
$errors = [];

if(option_value('t')) { // truncate
	print "Note: job_applications truncated" . "\n";
	$mysqli2->query("SET FOREIGN_KEY_CHECKS = 0;");
	$mysqli2->query("TRUNCATE job_applications;");
	$mysqli2->query("SET FOREIGN_KEY_CHECKS = 1;");
}

while($row = $job_applications->fetch_object()) {

	$message = addslashes($row->additional_info);

	// jobs
	$sql = "INSERT INTO job_applications SET 
		user_id = '{$row->uid}',
		resume_id = '{$row->resume_fid}',
		cover_letter_id = '{$row->cover_letter_fid}',
		message = '{$message}',
		status = '{$row->status}',
		created_at = NOW(),
		updated_at = NOW()";

	$insert_row_id = $mysqli2->query($sql) OR $errors[] = $sql . ' => ' . $mysqli2->error;

	if($insert_row_id){
		$inserted++;
	}

	show_status($inserted, $total);
}

print "" . "\n";

if(count($errors)){
	foreach($errors as $e){
		print "Error: " . $e . "\n";
	}
}

print "inserted: " . $inserted . " of " . $total . "\n";
print "success: " . round($inserted/$total*100) . "%" . "\n";

$jobs->free();

// close connection 
$mysqli->close();
$mysqli2->close();