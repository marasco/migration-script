<?php
$originFolder = '/var/www/vhosts/bevforce.com/sites/default/files/custom/';
$fileList = 'custom_list.txt';
$destFolder = 's3://forcebrands-v2-staging/files/custom/';
$options = '--acl public-read';

$i = 0;
$uploads = 0;
$startLine = 9019;
$limit = 0;
$handle = fopen($fileList, "r");

if ($handle) {
    while (($file = fgets($handle)) !== false) {

	if (empty($startLine) || !empty($startLine) && $startLine<$i){
	
		$source = "{$originFolder}{$file}";
		$source = preg_replace( "/\r|\n/", "", $source );

		$command = "aws s3 cp \"{$source}\" {$destFolder} {$options}";

		print("\r\n\r\nLine {$i}: {$command}\r\n");

		$r = exec($command, $result, $arr);

		print_r("\r\nResponse: ".$r);
		
		if (++$uploads >= $limit && $limit > 0){
			print("\r\nLimit files reached"); die;
		}
	}else{

		print("\r\nSkipping line {$i}");

	}

	$i++;

    }

    fclose($handle);
} else {
	print("\r\nCant open file");
} 
