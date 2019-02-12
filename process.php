<?php
/*
This is the file that does all the processing to AD
Change things in here at your own risk...
I will not be held responsible if you break your AD.
This is provided as is where is... make sure you understand
what is going on before doing a live sync... as I say...
I will not be held responsible if you break your AD.
*/

define(LDAP_OPT_DIAGNOSTIC_MESSAGE, 0x0032);

set_time_limit(0);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

// config
include ('config.php');

if($live!='yes'){echo "THIS IS A TRIAL SYNC! \r\n";}

// connect
$ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");

// make sure the proticol is set correctly.
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

// if the connection worked...
if($ldapconn) {
    // binding to ldap server
    $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));

    // verify binding
    if ($ldapbind) {
      // if successful let tell us.
        echo "LDAP bind successful...\r\n
Started: ".date('Y-m-d H:i:s')." \r\n
";

    // get list of files from the storage folder
    $files = scandir($storage);
    sort($files);

    //for each of these files do this
    foreach ($files as $n => $file) {
      // check if a real file
      if($file[0]=='.'){continue;}
      //get the contents of the file
	    $contents=file_get_contents($storage.$file);
      //convert into a php array
	    $data = json_decode($contents,true);
      //if it isn't an array then skip it.
	    if(!is_array($data)) {continue;}
      //if live then delete the file
      if($live=='yes'){unlink($storage.$file);}
      // if we don't have data then skip this file
      if(!array_key_exists('SMSDirectoryData', $data)) {continue;}
	    if(!array_key_exists('instanceID', $data['SMSDirectoryData'])) {continue;}
      // check if the authentication is correct.
      if($data['SMSDirectoryData']['instanceID']!=$authentication){continue;}
      // check if the staff data exists and that it is okay to sync staff.
    	if(array_key_exists('staff', $data['SMSDirectoryData']) && $syncstaff=='yes') {
        // for each staff
    		foreach ($data['SMSDirectoryData']['staff']['data'] as $i => $teacher) {
          //get the details needed
      		$id=$teacher['id'];
    			$username=strtolower($teacher['username']);
    			$firstname=$teacher['firstname'];
    			$lastname=$teacher['lastname'];
    			$email=$teacher['email'];
    			$groups=array();
    			foreach($teacher['groups'] as $key => $value){
            // make sure the type is class
    				if($value['type']=='class'){
              // if it is add it to the list
    					array_push($groups,'CN=Subject-'.$value['subject']."-".$value['coreoption'].",OU=Groups,".$ldaptree);
    				}
    			}
          $groups = array_unique($groups);

          //search for specific user in LDAP
          $result = ldap_search($ldapconn,"OU=Staff,".$ldaptree, "employeeid=".$id) or die ("Error in search query: ".ldap_error($ldapconn));
  	      $d = ldap_get_entries($ldapconn, $result);
          // check if user exists
          $newuser='no';
          if($d['count']<1){
            if($createnewstaff!='yes'){continue;}
            // create them if they don't
            $info=array();
            $firstnamecn=str_replace("'", '', $firstname);
            $firstnamecn=str_replace("-", '', $firstnamecn);
            $firstnamecn=str_replace(" ", '', $firstnamecn);
            $lastnamesn=str_replace("'", '', $lastname);
            $lastnamesn=str_replace("-", '', $lastnamesn);
            $lastnamesn=str_replace(" ", '', $lastnamesn);
            $cn = $firstnamecn." ".$lastnamesn;
          	$info["cn"] = $cn;
          	$info["sn"] = $lastname;
          	$info["givenname"] = $firstname;
            $info['objectclass'][0] = "top";
            $info['objectclass'][1] = "person";
            $info['objectclass'][1] = "organizationalPerson";
            $info['objectclass'][2] = "user";
            $info["homeDrive"] = "H:";
            $info["homeDirectory"] = $staffhomes.$username;
            $info["scriptpath"] = "staff.bat";
          	$info["employeeid"] = $id;
          	$info["useraccountcontrol"] = 544;
          	$info["mail"] = $email;
          	$info["samaccountname"] = $username;
          	$info["userprincipalname"] = $username.$suffix;

          	// add data to directory
            $dn = "CN=".$info["cn"].",OU=Staff,".$ldaptree;
            echo "Adding user ".$info["cn"]." ($dn) \r\n
";          $newuser="yes";
            if($live=='yes') {ldap_add($ldapconn, $dn, $info);}

            // add to default staff groups
            foreach ($defaultstaffgroups as $key => $group) {
              echo "Adding to $group \r\n
";            $group_info['member']=$dn;
              if($live=='yes'){ldap_mod_add($ldapconn,$group,$group_info);}
            }

          } else {
            $dn = $d[0]['dn'];
            if($updatestaffdetails=='yes'){
              // corfirm attributes are correct
              $firstnamecn=str_replace("'", '', $firstname);
              $firstnamecn=str_replace("-", '', $firstnamecn);
              $firstnamecn=str_replace(" ", '', $firstnamecn);
              $lastnamesn=str_replace("'", '', $lastname);
              $lastnamesn=str_replace("-", '', $lastnamesn);
              $lastnamesn=str_replace(" ", '', $lastnamesn);
              $cn = $firstnamecn." ".$lastnamesn;
              $info=array();
            	$info["sn"] = $lastname;
            	$info["givenname"] = $firstname;
            	$info["mail"] = $email;
            	$info["samaccountname"] = $username;
            	$info["userprincipalname"] = $username.$suffix;
              if(
                strtolower($d[0]['sn'][0])!=strtolower($info["sn"]) ||
                strtolower($d[0]['givenname'][0])!=strtolower($info["givenname"]) ||
                strtolower($d[0]['mail'][0])!=strtolower($info["mail"]) ||
                strtolower($d[0]['samaccountname'][0])!=strtolower($info["samaccountname"]) ||
                strtolower($d[0]['userprincipalname'][0])!=strtolower($info["userprincipalname"])
              ) {
            	   // update user if they aren't
                echo "Updating user ".$cn." ($dn) \r\n
                ".strtolower($d[0]['sn'][0])." => ".strtolower($info["sn"])."\r\n
                ".strtolower($d[0]['givenname'][0])." => ".strtolower($info["givenname"])."\r\n
                ".strtolower($d[0]['mail'][0])." => ".strtolower($info["mail"])."\r\n
                ".strtolower($d[0]['samaccountname'][0])." => ".strtolower($info["samaccountname"])."\r\n
                ".strtolower($d[0]['userprincipalname'][0])." => ".strtolower($info["userprincipalname"])."\r\n
  ";            if($live=='yes') {ldap_mod_replace($ldapconn, $dn, $info);}
              }
              $calcdn = "CN=".$cn.",OU=Staff,".$ldaptree;
              if($dn!=$calcdn){
                $oldDn = $dn;
                $newParent = "OU=Staff,".$ldaptree;
                $newRdn = "CN=".$cn;
                echo "Moving user ".$cn." ($dn to $calcdn) \r\n
  ";            if($live=='yes') {ldap_rename($ldapconn, $oldDn, $newRdn, $newParent, true);}
                $dn=$calcdn;
              }
            }
          }
          if($newuser=='no'){
            //get that user's groups from ldaptree
    	      $usergroups=array();
            if(array_key_exists('memberof', $d[0])){
              foreach($d[0]['memberof'] as $group){
                if(strpos($group,$ldaptree)>0){
                  array_push($usergroups,$group);
                }
              }
            }
          } else {
            $usergroups=array();
          }

         //work out which groups to add person to
          $needtoadd = array_diff($groups,$usergroups);
          //loop through these groups
          foreach ($needtoadd as $key => $group) {
            //check if group exists
            $groupcn=strpos($group,',OU=Groups');
            $groupcn=substr($group,0,$groupcn);
            $groupcn=substr($groupcn,3);
            $result = ldap_search($ldapconn,"OU=Groups,".$ldaptree, 'CN='.$groupcn) or die ("Error in search query: ".ldap_error($ldapconn));
            $g = ldap_get_entries($ldapconn, $result);
            if($g['count']<1){
              //details
              $info=array();
              $info["cn"] = $groupcn;
              $info['objectclass'][0] = "top";
              $info['objectclass'][1] = "group";
              $info["description"] = "Timetable Group, Managed by KAMAR.";
              // add data to directory
              echo "Creating Group CN=".$groupcn.",OU=Groups,".$ldaptree." \r\n
";
              if($live=='yes') {ldap_add($ldapconn, 'CN='.$groupcn.",OU=Groups,".$ldaptree, $info);}
            }
            echo "Adding $dn to $group \r\n
";          $group_info['member']=$dn;
            if($live=='yes') {ldap_mod_add($ldapconn,$group,$group_info);}
          }
          //work out which groups to remove person from
          $needtoremove = array_diff($usergroups,$groups);
          foreach ($needtoremove as $key => $group) {
            echo "Removing $dn from $group \r\n
";          $group_info['member']=$dn;
            if($live=='yes') {ldap_mod_del($ldapconn,$group,$group_info);}
          }
    		}
    	}
	if(array_key_exists('students', $data['SMSDirectoryData']) && $syncstudents=='yes') {
      // for each student
      foreach ($data['SMSDirectoryData']['students']['data'] as $i => $student) {
        //get the details needed
        $id=$student['id'];
        $username=strtolower($student['username']);
        $firstname=$student['firstname'];
        $lastname=$student['lastname'];
        $yearlevel=$student['yearlevel'];
        $email=$student['email'];
        if($changestudentpasswords=='yes'){
          $password=$student['password'];
          $resetpassword=$student['resetpassword'];
        }
        $networkaccess=$student['networkaccess'];
        $accountdisabled=$student['accountdisabled'];
        if(array_key_exists('leavingdate', $student)){
          $leavingdate=$student['leavingdate'];
        } else {
          $leavingdate="";
        }

        $groups=array();
        foreach($student['groups'] as $key => $value){
          // make sure the type is class
          if($value['type']=='class'){
            // if it is add it to the list
            array_push($groups,'CN=Subject-'.$value['subject']."-".$value['coreoption'].",OU=Groups,".$ldaptree);
          }
        }
        array_push($groups,'CN=Year-'.$yearlevel.",OU=Groups,".$ldaptree);
        $groups = array_unique($groups);
        //search for specific user in LDAP
        $result = ldap_search($ldapconn,"OU=Students,".$ldaptree, "employeeid=".$id) or die ("Error in search query: ".ldap_error($ldapconn));
        $d = ldap_get_entries($ldapconn, $result);
        // check if user exists
        $newuser='no';

        if($d['count']<1){
          // create them if they don't
          $info=array();
          $firstnamecn=str_replace("'", '', $firstname);
          $firstnamecn=str_replace("-", '', $firstnamecn);
          $firstnamecn=str_replace(" ", '', $firstnamecn);
          $lastnamesn=str_replace("'", '', $lastname);
          $lastnamesn=str_replace("-", '', $lastnamesn);
          $lastnamesn=str_replace(" ", '', $lastnamesn);
          $cn = $firstnamecn." ".$lastnamesn;
          $info["cn"] = $cn;
          $info["sn"] = $lastname;
          $info["givenname"] = $firstname;
          $info['objectclass'][0] = "top";
          $info['objectclass'][1] = "person";
          $info['objectclass'][1] = "organizationalPerson";
          $info['objectclass'][2] = "user";
          $info["homeDrive"] = "H:";
          $info["homeDirectory"] = $studenthomes.$username;
          //$info["scriptpath"] = "";
          $info["employeeid"] = $id;
          $info["useraccountcontrol"] = 544;
          $info["mail"] = $email;
          $info["samaccountname"] = $username;
          $info["userprincipalname"] = $username.$suffix;

          // add data to directory
          $dn = "CN=".$info["cn"].",OU=Year ".$yearlevel.",OU=Students,".$ldaptree;
          echo "Adding user ".$info["cn"]." ($dn) \r\n
";          $newuser="yes";
          if($live=='yes') {ldap_add($ldapconn, $dn, $info);}

          // add to default student groups
          foreach ($defaultstudentgroups as $key => $group) {
            echo "Adding to $group \r\n
";            $group_info['member']=$dn;
              if($live=='yes'){ldap_mod_add($ldapconn,$group,$group_info);}
          }

        } else {
          $dn = $d[0]['dn'];
          // corfirm attributes are correct
          $info=array();
          $firstnamecn=str_replace("'", '', $firstname);
          $firstnamecn=str_replace("-", '', $firstnamecn);
          $firstnamecn=str_replace(" ", '', $firstnamecn);
          $lastnamesn=str_replace("'", '', $lastname);
          $lastnamesn=str_replace("-", '', $lastnamesn);
          $lastnamesn=str_replace(" ", '', $lastnamesn);
          $cn = $firstnamecn." ".$lastnamesn;
          $info["sn"] = $lastname;
          $info["givenname"] = $firstname;
          $info["mail"] = $email;
          $info["samaccountname"] = $username;
          $info["userprincipalname"] = $username.$suffix;
          if(
            strtolower($d[0]['sn'][0])!=strtolower($info["sn"]) ||
            strtolower($d[0]['givenname'][0])!=strtolower($info["givenname"]) ||
            strtolower($d[0]['mail'][0])!=strtolower($info["mail"]) ||
            strtolower($d[0]['samaccountname'][0])!=strtolower($info["samaccountname"]) ||
            strtolower($d[0]['userprincipalname'][0])!=strtolower($info["userprincipalname"])
          ) {
             // update user if they aren't
            echo "Updating user ".$cn." ($dn) \r\n
            ".strtolower($d[0]['sn'][0])." => ".strtolower($info["sn"])."\r\n
            ".strtolower($d[0]['givenname'][0])." => ".strtolower($info["givenname"])."\r\n
            ".strtolower($d[0]['mail'][0])." => ".strtolower($info["mail"])."\r\n
            ".strtolower($d[0]['samaccountname'][0])." => ".strtolower($info["samaccountname"])."\r\n
            ".strtolower($d[0]['userprincipalname'][0])." => ".strtolower($info["userprincipalname"])."\r\n
";            if($live=='yes') {ldap_mod_replace($ldapconn, $dn, $info);}
          }
          $calcdn = "CN=".$cn.",OU=Year ".$yearlevel.",OU=Students,".$ldaptree;
          if($dn!=$calcdn){
            $oldDn = $dn;
            $newParent = "OU=Year ".$yearlevel.",OU=Students,".$ldaptree;
            $newRdn = "CN=".$cn;
            echo "Moving user ".$cn." ($dn to $calcdn) \r\n
";          if($live=='yes') {ldap_rename($ldapconn, $oldDn, $newRdn, $newParent, true);}
            $dn=$calcdn;
          }
        }

        if ($accountdisabled==1) {
    			//account is disabled - update company field and disable account
    			echo "Disable user ".$cn." \r\n";
    			$info=array();
    			$info["useraccountcontrol"] = 514;
    			$info["company"] = "Disabled ".date("Y-m-d H:i");
    			if($live=='yes') {ldap_mod_replace($ldapconn, $dn, $info);}
    		}
        if($ignoreuseragreementfield=='yes'){
          //overwrite the networkaccess if you are ignoring this field
          $networkaccess=1;
        }
    		if ($networkaccess==0) {
    			//contract not signed - update company field and disable account
    			echo "Disable user ".$cn." \r\n";
    			$info=array();
    			$info["useraccountcontrol"] = 514;
    			$info["company"] = "Agreement ".date("Y-m-d H:i");
    			if($live=='yes') {ldap_mod_replace($ldapconn, $dn, $info);}
    		}

    		if (($accountdisabled==0) && ($networkaccess==1)) {
    			//all good, enable account
    			echo "Ensure user ".$cn." is enabled\r\n";
    			$info=array();
    			$info["useraccountcontrol"] = 512;
    			$info["company"] = "OK ".date("Y-m-d H:i");
    			if($live=='yes') {ldap_mod_replace($ldapconn, $dn, $info);}
    		}

        if($changestudentpasswords=='yes'){
          if($resetpassword==1) {
      			//reset password (as this is using shell the account running process.php will need permissions)
      			echo 'EXECUTE: dsmod user "'.$dn.'" -pwd '.base64_decode($password).' -mustchpwd no -pwdneverexpires yes';
      			echo " \r\n";
      			if($live=='yes') {
      				$output = shell_exec('dsmod user "'.$dn.'" -pwd '.base64_decode($password).' -mustchpwd no -pwdneverexpires yes');
      				echo $output."\r\n";
      			}
      		}
        }

    		if (strlen($leavingdate)==8) {
    			//kamar only sends this info out once when a student is first changed to a leaver so need to act on it (i.e not in the over night file)
    			echo "Has left ".$cn." \r\n";
    			$info=array();
    			$info["company"] = "Leaver ".date("Y-m-d H:i");
    			$info["useraccountcontrol"] = 514;
    			//alternative to disabling account is to set the expiration date to expire in X days (not tested)
    			// $disableddate = date('Ymd', strtotime($leavingdate. ' + 5 days'));
    			// $info["accountexpirationdate"] = $disableddate;
    			if($live=='yes') {ldap_mod_replace($ldapconn, $dn, $info);}
    		}

        if($newuser=='no'){
          //get that user's groups from ldaptree
          $usergroups=array();
          if(array_key_exists('memberof', $d[0])){
            foreach($d[0]['memberof'] as $group){
              if(strpos($group,$ldaptree)>0){
                array_push($usergroups,$group);
              }
            }
          }
        } else {
          $usergroups=array();
        }

       //work out which groups to add person to
        $needtoadd = array_diff($groups,$usergroups);
        //loop through these groups
        foreach ($needtoadd as $key => $group) {
          //check if group exists
          $groupcn=strpos($group,',OU=Groups');
          $groupcn=substr($group,0,$groupcn);
          $groupcn=substr($groupcn,3);
          $result = ldap_search($ldapconn,"OU=Groups,".$ldaptree, "CN=".$groupcn) or die ("Error in search query: ".ldap_error($ldapconn));
          $g = ldap_get_entries($ldapconn, $result);
          if($g['count']<1){
            //details
            $info=array();
            $info["cn"] = $groupcn;
            $info['objectclass'][0] = "top";
            $info['objectclass'][1] = "group";
            $info["description"] = "Timetable Group, Managed by KAMAR.";
            // add data to directory
            echo "Creating Group CN=".$groupcn.",OU=Groups,".$ldaptree." \r\n
";
            if($live=='yes') {ldap_add($ldapconn, 'CN='.$groupcn.",OU=Groups,".$ldaptree, $info);}
          }
          echo "Adding $dn to $group \r\n
";          $group_info['member']=$dn;
          if($live=='yes') {ldap_mod_add($ldapconn,$group,$group_info);}
        }
        //work out which groups to remove person from
        $needtoremove = array_diff($usergroups,$groups);
        foreach ($needtoremove as $key => $group) {
          echo "Removing $dn from $group \r\n
";          $group_info['member']=$dn;
          if($live=='yes') {ldap_mod_del($ldapconn,$group,$group_info);}
        }
      }
		}
	}
}

	//search for all groups and remove any that have no members.
	$result = ldap_search($ldapconn,"OU=Groups,".$ldaptree, "cn=*") or die ("Error in search query: ".ldap_error($ldapconn));
	$data = ldap_get_entries($ldapconn, $result);
	foreach($data as $thisgroup){
		if(is_array($thisgroup)){
			$dn=$thisgroup['dn'];
			echo "CHECK: $dn - ";
			if(array_key_exists('member',$thisgroup)){
				if($thisgroup['member']['count']<2){
					if($live=='yes') {ldap_delete($ldapconn, $dn);}
					echo "DELETED \r\n
";
				} else {
					echo "FINE \r\n
";
				}
			} else {
        if($live=='yes') {ldap_delete($ldapconn, $dn);}
				echo "DELETED \r\n
";
			}
		}
	}
}
// all done... clean up
ldap_close($ldapconn);
// tell us we are finished.
echo "All Done... \r\n
Finished: ".date('Y-m-d H:i:s')."
";
?>
