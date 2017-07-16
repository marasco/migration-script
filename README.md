# Migration Script

## Copy datasets

### Run with truncate optional
Copiers are plural
You can previously truncate 
```
php users.php -t true
```

### Aux data chain
This will run jobs_applications at the end
```
php jobs.php -w applications
```
This will truncate and run users_files.php at the end
```
php users.php -t true -w files
```

## Display results
### Details are singular
### By ID

```
php job.php -i 21650
```

### By Name

```
php user.php -n John
```

##Modifiers glossary
```
-i id
-n name
-t truncate
-w with
-r resume (not implemented yet)
```