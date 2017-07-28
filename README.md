# Migration scripts

(Optional)

1) analyze and user replace-user-ids.php; (CHANGE IDs if needed)

2) 
$ php users.php; #can user runjobs.sh to stepping
$ php users_credits.php;
$ php users_companies.php;
$ php users_files.php;
$ php jobs.php;
$ php jobs_applications.php; 

## Comprobation queries

SELECT COUNT(id), brand FROM post_jobs GROUP BY brand;
SELECT COUNT(id), brand FROM users GROUP BY brand;
SELECT COUNT(id), brand FROM beverage_types GROUP BY brand;
