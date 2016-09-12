# ADSync
Sync to AD from KAMAR using a PHP listening / LDAP service.

This incorporates a listening service for KAMAR and then a file that processes that Data and updates AD using LDAP

We now have this up and running on our servers and it's working great.

# What it does and doesn't do
It Does:
- Create New Staff (optional)
- Create New Students
- Update Student Groups based on Timetabled Classes
- Update Staff Groups based on Timetabled Classes
- Update Student Details
- Update Staff Details (optional)

It Doesn't:
- Do anything with passwords

# Installation

To install extract the files onto a server running PHP and some web publishing thing (eg: Apache, IIS etc.) and note down where this is going to be running.
Change the appropriate things in the config.php file (full comments in there to guide you through)... note that it may not display nicely in notepad on windows... ATOM is a great editor if you're looking for one.

Once you have set up the files on the web server on KAMAR go to: Setup - KAMAR - Server - Directory Services
Make a new service of type 'Other'.

The fields you need to set are:
- Name: ADSync (doesn't really matter what you call it)
- Make sure you check it works and tick the enabled box
- Address... whatever the address is of where you were hosting it with a /json.php on the end (see screenshot below for an example)
- It doesn't matter if you are using SSL or not
- The Data Format must be JSON
- Username should be left blank
- Authentication should be set to whatever you set it to in the config file

It should then look like this:
![Image of Yaktocat](https://assay.co.nz/KAMARSS.png)

# Running
Once installed you want to press the send full update button.

You can then either
- run using the batch script (you'll need to edit this first) if on windows
- just going to wherever you have installed it and going to http(s)://wherever/you/put/it/process.php
- or running on the command line in linux (`$ php /path/to/process.php`)

The run.bat file looks like this
```
@echo off
for /F "usebackq tokens=1,2 delims==" %%i in (`wmic os get LocalDateTime /VALUE 2^>NUL`) do if '.%%i.'=='.LocalDateTime.' set ldt=%%j
set ldt=%ldt:~0,4%-%ldt:~4,2%-%ldt:~6,2%-%ldt:~8,2%%ldt:~10,2%-%ldt:~12,6%
"C:\Program Files (x86)\PHP\v5.6\php.exe" -f C:\inetpub\kamar-web\wwwroot\assay\ldap\process.php >C:\ldaplogs\%ldt%.txt 2>&1
```
The parts you will need to change are in the bottom line:
`C:\Program Files (x86)\PHP\v5.6\php.exe` is the path to your PHP installation
`C:\inetpub\kamar-web\wwwroot\assay\ldap\process.php` is the location of the process.php file
`C:\ldaplogs\` is where you want the log files to be saved (they are automatically timestamped... that is what the `%ldt%` does)

I suggest the first run you do while '$live' in the config is set to 'no', and this will give you a preview of what it is going to do without making any live changes.

You can also set it up to run automatically by setting up a scheduled task that runs the run.bat file if on Windows (we have ours running every hour) or setting up a cron job that runs `php /path/to/process.php` if on linux

#Disclaimer

This is provided as is where is for you to use as you see fit.
It is not provided with any warranty, explicit or implicit.
We now have it up and running on our server at KC, but I won't be held responsible if you break your AD... backups are your friend.

Please note: KAMAR cannot provide any support for this as it's not their product :) 
