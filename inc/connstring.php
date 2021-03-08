<?php
// SQL connection String
$MYconnectionInfo = array(
	"UID" => "radsms", 
	"PWD" => "K9L3VPI4:5K", 
	"Database"=>"smsportal"
);

$MYserverName = "webstaging2sql2"; // Server name
$baseDSN = "DC=northumbria-healthcare,DC=nhs,DC=uk"; // AD Base search path
$ldapserver = "northumbria-healthcare.nhs.uk"; // AD server
$ldaplogin = array("UID" =>"WebAD@northumbria-healthcare.nhs.uk", "PWD" =>"webLDAP92"); // AD login

$GroupsLdap = array("CN=Maternity - WAN,OU=Global,OU=Group Objects,DC=northumbria-healthcare,DC=nhs,DC=uk","CN=Domain IT Staff,OU=Global,OU=Group Objects,DC=northumbria-healthcare,DC=nhs,DC=uk"); // AD Group list
?>