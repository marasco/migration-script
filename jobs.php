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
	") OR die($mysqli->error);

// WHERE users.uid = 110718
$inserted = 0;
$total = $jobs->num_rows;
$errors = [];
var_dump("1");
if(option_value('t')) { // truncate
	print "Note: companies,post_jobs : truncated" . "\n";
	$mysqli2->query("SET FOREIGN_KEY_CHECKS = 0;");
	$mysqli2->query("TRUNCATE companies;");
	$mysqli2->query("TRUNCATE post_jobs;");
	$mysqli2->query("SET FOREIGN_KEY_CHECKS = 1;");
}

$states = [];
// Users and roles
$job_states = $mysqli2->query("SELECT * FROM job_states") OR die($mysqli2->error);

while($row = $job_states->fetch_object()) {
	$states[$row->name] = $row->id;
}

$types = [];
$job_types = $mysqli2->query("SELECT * FROM employment_types") OR die($mysqli2->error);

while($row = $job_types->fetch_object()) {
	$types[trim(str_replace(' ','-',$row->name))] = $row->id;
}

while($row = $jobs->fetch_object()) {

	$company_id = 0;
	$company = "";
	$title = addslashes($row->title);
	$city = addslashes($row->field_city_value);
	$brand = addslashes($row->field_brand_name_value);
	$description = addslashes($row->field_job_requirements_value);

	if(strlen($row->field_brand_name_value)){
		$company = $row->field_brand_name_value;
	} else if(strlen($row->field_name_value)){
		$company = $row->field_name_value;
	}

	if(strlen($company)){
		$company = trim(addslashes($company));
		$company_result = $mysqli2->query("SELECT id FROM companies WHERE name = '{$company}' LIMIT 1") OR die($mysqli2->error);
		if($company_result->num_rows){
			$company_id = $company_result->fetch_object()->id;
		} else {
			$logo = "";
			$logo_result = $mysqli->query("SELECT filepath FROM bf_files WHERE uid = '{$row->uid}' AND type = 'other' LIMIT 1") OR die($mysqli->error);

			if($logo_result->num_rows){
				$logo = $logo_result->fetch_object()->filepath;
			}

			$sql = "INSERT INTO companies SET name = '{$company}', logo = '{$logo}'";
			$company_id = $mysqli2->query($sql) OR die($mysqli2->error);
		}
	}

	// jobs
	$sql = "INSERT INTO post_jobs SET 
		user_id = '{$row->uid}',
		company_id = '{$company_id}',
		title = '{$title}',
		city = '{$city}',
		brand = '{$brand}',
		state_id = '{$row->field_state_value}',
		zip_code = '{$row->field_zip_value}',
		description = '{$description}',
		status = '{$row->field_job_status_value}',
		created_at = NOW(),
		updated_at = NOW()";

	$insert_row_id = $mysqli2->query($sql) OR $errors[] = $sql . ' => ' . $mysqli2->error;

	if($insert_row_id){

		// employment type

		$employment_type = $types[$row->field_type_value];

		if($employment_type){
			$sql = "INSERT INTO job_employment_types SET job_id = '{$insert_row_id}',employment_type_id = '{$employment_type}'";
			$mysqli2->query($sql) OR die($mysqli2->error);
		} else {
			print "Note: Employment type not found: " . $row->field_type_value . "\n";
		}

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
