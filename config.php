<?php
/*
The folling are the things that you should use to configure the settings for the sync service.
Change them as you need to.
Make sure there is a semi-colon (;) at the end of each line otherwise it will break.
This is provided as is where is... make sure you understand
what is going on before doing a live sync... as I say...
I will not be held responsible if you break your AD.
*/

// Authentication as set in KAMAR Directory services
// Do not set a username - leave that blank.
// Good idea to change this to something random
$authentication = 'dlfkgo78sfdg';

// The IP or Name of the DC
// eg: '172.30.0.104'
$ldapserver = 'localhost';

// The full DN of the LDAP Admin you want to authenticate with
// eg: 'CN=Jake Wills,OU=Teacher,OU=Staff,OU=KAMAR,DC=kapiticollege,DC=local'
$ldapuser   = 'CN=Admin,OU=Teacher,OU=Staff,OU=KAMAR,DC=kapiticollege,DC=local';

// The password for that Admin
// eg: '12345'
$ldappass   = 'Password123';

// The LDAP Tree that you want the KAMAR things to be in
// This should have 3 OUs in it already: Groups, Staff and Students
// eg: 'OU=KAMAR,DC=kapiticollege,DC=local';
$ldaptree   = 'OU=KAMAR,DC=kapiticollege,DC=local';

// Suffix for userprincipalname
// eg: '@kapiticollege.local'
$suffix = '@kapiticollege.local';

// Live?
// If set to 'yes' it will actually make changes.
// If set to 'no' it will just display the changes it would make.
// Note, these are case sensitive... should be all lower case.
$live = 'no';

// Storage
// This should point to a folder to put the output from KAMAR's directory services
// It should not be accessible directly from the web, otherwise everyone can see all your stuff... not ideal!
// eg: './storage/'
// note the trailing slash on the end.
$storage ='../ldapstorage/';

// Sync Staff / students
// Will only sync if set to 'yes'
$syncstaff='yes';
$syncstudents='yes';
$createnewstaff='no';

// Default Staff Groups
// Put each group on a new line in quotes, with a comma at the end.
// These groups are added to any new staff that are created.
// They are not applied to existing staff.
$defaultstaffgroups = array(
    'CN=All Staff Mail Group,OU=Google Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=Wireless Enabled Staff,OU=Security Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=All Teachers Mail Group,OU=Google Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=Staff,OU=Staff Groups,OU=Security Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=LocalAdmins,OU=Security Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=TSUsers,OU=Security Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
);

// Default Student Groups
// Put each group on a new line in quotes, with a comma at the end.
// These groups are added to any new students that are created.
// They are not applied to existing students.
$defaultstudentgroups = array(
    'CN=GoogleCloudPrintUsers,OU=Google Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=Wireless Enabled Students,OU=Security Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=All Students Mail Group,OU=Google Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=Staff,OU=Staff Groups,OU=Security Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=CLASS,OU=Student Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
    'CN=Students,OU=Security Groups,OU=Kapiti College,DC=kapiticollege,DC=local',
);

// Staff and student home drives.
// Any backslash will need to be replaced with a double blackslash
// eg: '\\\\kapiticollege.local\\users\\staff\\'
$staffhomes = '\\\\kapiticollege.local\\users\\staff\\';
$studenthomes = '\\\\kc-student01\\Student_Home\\';
?>
