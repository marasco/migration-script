#!/bin/sh
cd .;

echo "Running process";
limit=100
# phpcommand=`whereis php`
phpcommand='/usr/bin/php'
offset=0
# limitarg=" -l $offset,$limit"
limitarg=""

$phpcommand ./users.php $limitarg
$phpcommand ./users_files.php

echo "Finished";
