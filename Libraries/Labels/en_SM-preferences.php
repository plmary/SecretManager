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

	$L_Save = 'Save';
	$L_Parameters_Updated = 'Parameters updated';

	$L_Mail_From = "Issuer Name";
	$L_Mail_To = "Recipient names must be separated by commas";

	$L_Connection_Management = 'Connection process Management';
	$L_Use_Password = 'Password Authentication mode';
	$L_Use_Radius = 'RADIUS Authentication';

	$L_Min_Size_Password = 'Minimum password length';
	$L_Password_Complexity = 'Passwords complexity';
	$L_Default_User_Lifetime = 'Password lifetime (months)';
	$L_Max_Attempt = 'Maximum number of attempts';
	$L_Default_Password = 'Default password';
	$L_Expiration_Time = 'Time before session expiration (minutes)';
	$L_Radius_Server_IP = 'RADIUS server IP address';
	$L_Radius_Secret_Common = 'RADIUS shared secret';
	
	$L_ERR_MAJ_Alert = 'An error occurred while updating the "Alerts" parameters';
	$L_ERR_MAJ_Connection = 'An error occurred while updating the "Connection" parameters';
	
	$L_Specify_Purge_Date_History = 'Specify a purge date in history';
	$L_Oldest_Date_History = 'oldest date in history';
	$L_Purge_Historical = 'Purge historical';
	$L_No_Purge_Date = 'No date specified purge, purge not done';
	$L_Success_Purge = 'The events of history, before the date of "%s", have been purged';
	
	$L_SecretServer_Management = 'SecretServer management';
?>