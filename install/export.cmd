@echo off

REM :: Read version
for /f %%i in ('grep -Eo "([0-9\.]+)" ../config/version.php') do set VERSION=%%i

REM :: Initialize variables
set TDIR="E:\BUILD"
set SDIR="E:\Github\cBackup\Core"
set EXCL=--exclude=install --exclude=README.md --exclude=views/install --exclude=web/install --exclude=views/layouts/install.php --exclude=controllers/InstallController.php --exclude=config/db.php --exclude=config/install.php

REM :: Create MySQL dump
call dump.cmd

REM :: Cleanup codeception runtime
cd /D "%SDIR%"
call codecept.bat clean
cd /D "%SDIR%\install"

REM :: Prepare environment
rm -rf %TDIR%
mkdir %TDIR%

REM :: Copy files
xcopy /H /I /E /Q /Y %SDIR% %TDIR%

REM :: Clean up
rm -vf  %TDIR%/*.conf
rm -vf  %TDIR%/*.lock
rm -vf  %TDIR%/.gitignore
rm -vf  %TDIR%/cbackup.private.key
rm -vfr %TDIR%/.git | grep "removed directory"
rm -vfr %TDIR%/.idea  | grep "removed directory"
rm -vf  %TDIR%/bin/.gitignore
rm -vf  %TDIR%/bin/application.properties
rm -vf  %TDIR%/bin/hostKey.ser
rm -vf  %TDIR%/config/settings.ini
rm -vfr %TDIR%/data/backup/
rm -vf  %TDIR%/install/*.cmd
rm -vf  %TDIR%/install/test.sql
rm -vfr %TDIR%/runtime/cache | grep "removed directory"
rm -vfr %TDIR%/runtime/debug
rm -vfr %TDIR%/runtime/HTML
rm -vfr %TDIR%/runtime/logs
rm -vfr %TDIR%/runtime/URI
rm -vfr %TDIR%/runtime/gii* | grep "removed directory"
rm -vfr %TDIR%/web/install/assets/* | grep "removed directory"
rm -vfr %TDIR%/web/assets/* | grep "removed directory"
rm -vfr %TDIR%/modules/plugins/*/
rm -vfr %TDIR%/vendor/* | grep "removed directory"
mv -vf  %TDIR%/install/.gitignore %TDIR%
rm -vfr %TDIR%/tests | grep "removed directory"
rm -vf  %TDIR%/codecept*
rm -vf  %TDIR%/web/index-test.php
rm -vf  %TDIR%/config/test*.php
rm -vf  %TDIR%/modules/cds/content/*.md
rm -vfr %TDIR%/modules/cds/content/.git | grep "removed directory"
rm -vfr %TDIR%/modules/cds/content/.idea | grep "removed directory"
rm -vfr %TDIR%/modules/cds/content/authtemplates | grep "removed directory"
rm -vfr %TDIR%/modules/cds/content/devices | grep "removed directory"
rm -vfr %TDIR%/modules/cds/content/workers | grep "removed directory"

REM :: Prepare production env
cd /D "%TDIR%"
mv -vf %TDIR%/web/index-prod.php %TDIR%/web/index.php
mv -vf %TDIR%/web/install/index-prod.php %TDIR%/web/install/index.php
mv -vf %TDIR%/yii-prod %TDIR%/yii
call composer install --no-dev
call composer dumpautoload -o
rm -f %TDIR%/composer.*
tar --exclude=migrations --exclude=README.md -cf ../cbackup-%VERSION%.tar * .??*
tar %EXCL% -cf ../cbackup-%VERSION%-update.tar * .??*

REM :: Clean up production env and proceed with debug env
rm -vfr %TDIR%/vendor/* | grep "removed directory"
cp -fv %SDIR%/web/index.php %TDIR%/web/index.php
cp -fv %SDIR%/web/install/index.php %TDIR%/web/install/index.php
cp -fv %SDIR%/yii %TDIR%/yii
cp -fv %SDIR%/composer.json %TDIR%/composer.json
cp -fv %SDIR%/*.conf %TDIR%
cp -fv %SDIR%/codecept* %TDIR%
mkdir %TDIR%\tests
xcopy /H /I /E /Q /Y %SDIR%\tests %TDIR%\tests
cd /D "%TDIR%"
cp -fv %SDIR%/web/index-test.php %TDIR%/web/index-test.php
cp -fv %SDIR%/config/test.php %TDIR%/config/test.php
cp -fv %SDIR%/config/test_db.php %TDIR%/config/test_db.php
cp -fv %SDIR%/install/test.sql %TDIR%/install/test.sql
call composer install
tar --exclude=migrations --exclude=README.md -cf ../cbackup-%VERSION%-debug-release.tar * .??*
tar %EXCL% -cf ../cbackup-%VERSION%-debug-update.tar * .??*

REM :: Compress tars
echo Compressing files
gzip -9 ../cbackup-%VERSION%.tar
gzip -9 ../cbackup-%VERSION%-debug-release.tar
gzip -9 ../cbackup-%VERSION%-update.tar
gzip -9 ../cbackup-%VERSION%-debug-update.tar

REM :: Correct program termination
echo Cleaning up
cd /D "%SDIR%\install"
rm -rf %TDIR%

REM :: Exit
echo Export finished, %date% %time%
exit /B 0
