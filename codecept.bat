@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0vendor/codeception/codeception/codecept
php "%BIN_TARGET%" %*
