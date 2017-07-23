#!/bin/sh
cd .;
echo "Running process";
limit=100
#phpcommand=`whereis php`
phpcommand='/usr/bin/php'
offset=0
#limitarg=" -l $offset,$limit"
limitarg=""
$phpcommand ./users.php -w files $limitarg

echo "Finished";
