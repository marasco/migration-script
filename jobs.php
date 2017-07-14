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
$jobs = $mysqli->query("SELECT content_type_job.*, node.title, node.uid 
	FROM content_type_job
	LEFT JOIN node ON node.nid = content_type_job.nid 
	GROUP BY content_type_job.nid 
	") or die($mysqli->error);

// WHERE users.uid = 110718
echo "<pre>";

$inserted = 0;
$schema = [];
$errors = [];
$mysqli2->query("SET FOREIGN_KEY_CHECKS = 0;");
$mysqli2->query("TRUNCATE post_jobs;");
$mysqli2->query("SET FOREIGN_KEY_CHECKS = 1;");

$states = [];
// Users and roles
$job_states = $mysqli2->query("SELECT *
	FROM jobs_states
	") or die($mysqli->error);

while($row = $job_states->fetch_object()) {
	$states[$row->name] = $row->id;
}

while($row = $jobs->fetch_object()) {

	$sql = "INSERT INTO post_jobs SET 
		user_id = ,
		company_id = ,
		user_id = ,
		user_id = ,
		user_id = ,
		user_id = ,
		user_id = ,
		user_id = ,
		user_id = ,
		user_id = ,
		
		(user_id,company_id, path_file, created_at, updated_at) VALUES
		($row->uid, '{$row->filename}', '{$row->fileurl}', NOW(), NOW())
	";

	$insert_row = $mysqli2->query($sql) OR $errors[] = $sql . ' => ' . $mysqli2->error;

	if($insert_row){
		$inserted++;
	}
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
