<?php
/**
* Libellé spécifique à la gestion des Préférences.
*
* @warning Ce fichier doit impérativement être au format UTF-8 pour une gestion cohérente des caractères accentués.
*
* @copyright LGPL License 3.0 http://www.gnu.org/copyleft/lesser.html
* @author Pierre-Luc MARY
* @date 2013-03-25
* @version 1.2
*/
	$L_Title = 'Preferences management';
	
	$L_Welcome = 'Welcome';
	$L_Alerts = 'Alerts';
	$L_Connection = 'Connection';
	$L_Welcome_Text = 'Warning: These settings determine the overall safety of SecretManager and may cause serious malfunction.' ;
	
	$L_Alert_Management = 'Alerts management';
	$L_Verbosity_Alert = 'Alert Verbosity level';
	$L_Alert_Syslog = 'Alert via Syslog';
	$L_Alert_Mail = 'Alert via Mail';
	$L_Detailed_Verbosity = 'Detailed'; // Verbosité détaillée remontée
	$L_Technical_Verbosity = 'Technical'; // Verbosité technique remontée
	$L_Normal_Verbosity = 'Normal'; // Verbosité normale remontée
	$L_Language_Alerts = 'Language alerts';	

	$L_Save = 'Save';
	
	$L_Parameter_Updated = 'Parameter updated';
	$L_Parameters_Updated = 'Parameters updated';

	$L_Mail_From = "Issuer Name";
	$L_Mail_To = "Recipient names must be separated by commas";
	$L_Title_1 = 'Title';
	$L_Mail_Title = 'Mail title';
	$L_Mail_Body = 'Mail body';
	$L_Body = 'Body';
	$L_Mail_Body_Type = 'Mail body type';
	$L_Body_Type = 'Body type';

	$L_Connection_Management = 'Connection process Management';
	$L_Use_Password = 'Password Authentication';
	$L_Use_Radius = 'RADIUS Authentication';
	$L_Use_LDAP = 'LDAP Authentification';

	$L_Min_Size_Password = 'Minimum password length';
	$L_Password_Complexity = 'Passwords complexity';
	$L_Default_User_Lifetime = 'User lifetime (months)';
	$L_Max_Attempt = 'Maximum number of attempts';
	$L_Default_Password = 'Default password';
	$L_Expiration_Time = 'Time before session expiration (minutes)';
	$L_Radius_Server = 'RADIUS server IP address';
	$L_Radius_Authentication_Port = 'RADIUS Server IP Authentication Port';
	$L_Radius_Accounting_Port = 'RADIUS Server IP Accounting Port';
	$L_Radius_Secret_Common = 'RADIUS shared secret';
	$L_LDAP_Server = 'LDAP server IP address';
	$L_LDAP_Port = 'LDAP port';
	$L_LDAP_Protocol_Version = 'LDAP protocol version';
	$L_LDAP_Organization = 'LDAP Organization';
	$L_LDAP_RDN_Prefix = 'LDAP prefix RDN';
	$L_Testing_Connection = 'Testing connection';
	
	$L_ERR_MAJ_Alert = 'An error occurred while updating the "Alerts" parameters';
	$L_ERR_MAJ_Connection = 'An error occurred while updating the "Connection" parameters';
	
	$L_Specify_Purge_Date_History = 'Specify a purge date in history';
	$L_Oldest_Date_History = 'oldest date in history';
	$L_Purge_Historical = 'Purge historical';
	$L_No_Purge_Date = 'No date specified purge, purge not done';
	$L_Success_Purge = 'The events of history, before the date of "%s", have been purged';
	
	$L_SecretServer_Management = 'SecretServer management';

	$L_SecretServer_Keys = 'Security keys used with SecretServer';
	$L_Min_Key_Size = 'Key minimum size';
	$L_Key_Complexity = 'Key complexity';
	$L_Mother_Key = 'Mother key';
	$L_Operator_Key = 'Operator key';
?>