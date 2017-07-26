#!/bin/sh

python2.7 ./s4cmd dsync -s /var/www/vhosts/bevforce.com/sites/default/files/ s3://forcebrands-v2-staging/files/ --access-key=AKIAIZCMCPCFUKXVD63A --secret-key=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 
