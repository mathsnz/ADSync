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

Full documentation will be coming soon... but feel free to take it and fiddle with it.
I won't be held responsible if I break your AD though.
