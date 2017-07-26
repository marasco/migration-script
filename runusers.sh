#!/bin/sh
cd .;

echo "Running process";
limit=0
# phpcommand=`whereis php`
phpcommand='/usr/bin/php'
offset=0
limitarg=""

$phpcommand ./users.php $limitarg
$phpcommand ./users_files.php

echo "Finished";
