# Migration scripts

(Optional)
$ php replace-user-ids.php; (CHANGE IDs if needed)

$ php users.php;
$ php users_credits.php;
$ php users_files.php;
$ php jobs.php;
$ php jobs_applications.php; 

## Comprobation queries

SELECT COUNT(id), brand FROM post_jobs GROUP BY brand;
SELECT COUNT(id), brand FROM users GROUP BY brand;
SELECT COUNT(id), brand FROM beverage_types GROUP BY brand;