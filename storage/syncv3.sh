#!/bin/sh


aws s3 cp /var/www/vhosts/bevforce.com/sites/default/files/convert/ s3://forcebrands-v2-staging/files/convert/ --acl public-read  --recursive >> outfile.log

aws s3 cp /var/www/vhosts/bevforce.com/sites/default/files/convert2/ s3://forcebrands-v2-staging/files/convert2/ --acl public-read  --recursive >> outfile.log


aws s3 cp /var/www/vhosts/bevforce.com/sites/default/files/pdf_resume_2017_1/ s3://forcebrands-v2-staging/files/pdf_resume_2017_1/ --acl public-read  --recursive >> outfile.log

aws s3 cp /var/www/vhosts/bevforce.com/sites/default/files/pdf_resume_2017_2/ s3://forcebrands-v2-staging/files/pdf_resume_2017_2/ --acl public-read  --recursive >> outfile.log

aws s3 cp /var/www/vhosts/bevforce.com/sites/default/files/pictures/ s3://forcebrands-v2-staging/files/pictures/ --acl public-read  --recursive >> outfile.log


aws s3 cp /var/www/vhosts/bevforce.com/sites/default/files/custom/ s3://forcebrands-v2-staging/files/custom/ --acl public-read  --recursive >> outfile.log



aws s3 cp /var/www/vhosts/bevforce.com/sites/default/files/pdf_resume_2016_1/ s3://forcebrands-v2-staging/files/pdf_resume_2016_1/ --acl public-read  --recursive >> outfile.log


aws s3 cp /var/www/vhosts/bevforce.com/sites/default/files/pdf_resume_2016_2/ s3://forcebrands-v2-staging/files/pdf_resume_2016_2/ --acl public-read  --recursive >> outfile.log


