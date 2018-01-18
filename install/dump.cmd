@echo off

REM :: Create MySQL dump
mysqldump -h localhost -u root --no-data cbackup | sed 's/ AUTO_INCREMENT=[0-9]*//g' > schema.sql
head -n -2 schema.sql > temp.sql
tail -n +6 temp.sql > schema.sql
rm -f temp.sql
