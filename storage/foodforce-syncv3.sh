#!/bin/sh

aws s3 cp /code/sites/foodforce.com/sites/default/files/ s3://forcebrands-v2-staging/files_foodforce/ --acl public-read  --recursive >> outfile.log