#!/bin/sh
cd .;
echo "Running process";
limit=100
phpcommand=`whereis php`
offset=0

$phpcommand ./users.php -w files -l $offset,$limit

echo "Finished";
