#!/bin/bash
# Tested using bash version 4.1.5
for ((i=1;i<=1000;i++)); 
do 
   # your-unix-command-here
   let p=($i-1)*100
   echo "php jobs.php - $p,100";
   php jobs.php -l $p,100
done

