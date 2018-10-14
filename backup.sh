#!/bin/bash
today=`date +%Y-%m-%d`
filename="/backup/twhl-sql-backup-$today.sql.gz"

echo "Dumping to $filename..."
mysqldump twhl -u root -p<password> | gzip > $filename

echo "Uploading to storage..."
s3cmd put $filename s3://twhl/backups/sql/
