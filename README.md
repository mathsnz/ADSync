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

#Installation

To install extract the files onto a server running PHP and some web publishing thing (eg: Apache, IIS etc.) and note down where this is going to be running.
Change the appropriate things in the config.php file (full comments in there to guide you through)... note that it may not display nicely in notepad on windows... ATOM is a great editor if you're looking for one.

Once you have set up the files on the web server on KAMAR go to: Setup - KAMAR - Server - Directory Services
Make a new service of type 'Other'.

The fields you need to set are:
- Name: ADSync (doesn't really matter what you call it)
- Make sure you check it works and tick the enabled box
- Address... whatever the address is of where you were hosting it
- It doesn't matter if you are using SSL or not
- The Data Format must be JSON
- Username should be left blank
- Authentication should be set to whatever you set it to in the config file

It should then look like this:
![Preview](https://raw.githubusercontent.com/mathsnz/ADSync/master/img/KAMARSS.png)


You can then either run using the batch script (you'll need to edit this first) if on windows, or just going to

I suggest the first run you do while '$live' in the config is set to 'no', and this will give you a preview of what it is going to do.

You can also set it up to run manually

Full documentation will be coming soon... but feel free to take it and fiddle with it.
I won't be held responsible if I break your AD though.
