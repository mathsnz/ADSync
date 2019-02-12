@echo off
for /F "usebackq tokens=1,2 delims==" %%i in (`wmic os get LocalDateTime /VALUE 2^>NUL`) do if '.%%i.'=='.LocalDateTime.' set ldt=%%j
set ldt=%ldt:~0,4%-%ldt:~4,2%-%ldt:~6,2%-%ldt:~8,2%%ldt:~10,2%-%ldt:~12,6%
"C:\Program Files (x86)\PHP\v5.6\php.exe" -f C:\inetpub\kamar-web\wwwroot\assay\ldap\process.php >C:\ldaplogs\%ldt%.txt 2>&1