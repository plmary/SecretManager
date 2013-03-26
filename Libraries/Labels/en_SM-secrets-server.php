<?php
/**
* Libellé spécifique à la gestion du SecretServer.
*
* @warning Ce fichier doit impérativement être au format UTF-8 pour une gestion cohérente des caractères accentués.
*
* @copyright LGPL License 3.0 http://www.gnu.org/copyleft/lesser.html
* @author Pierre-Luc MARY
* @date 2013-03-25
* @version 1.2
*/

	$L_ERR_SERVER_NOT_LOADED = 'SecretServer not loaded';
	$L_ERR_SERVER_NOT_STARTED = 'SecretServer non started';
	$L_ERR_MOTHER_KEY_CORRUPTED = "Mother key corrupted";
	$L_ERR_NO_CMD_SEND = 'No command send';
	$L_ERR_NO_SESSION = 'No ID Session';
	$L_ERR_NO_VALUE = 'No value';
	$L_ERR_INVALID_SESSION = 'Invalid Session or Session not found';
	$L_ERR_SESSION_EXPIRED = 'Session expired';
	$L_ERR_USER_NOT_CONNECTED = 'User not connected';
	$L_ERR_USER_NOT_ADMIN = 'The user is not an administrator';
	$L_ERR_MOTHER_KEY_EMPTY = 'Error Mother key is empty';
	$L_ERR_INVALID_OPERATOR_KEY = 'Secret\'s file corrupted or invalid Operator key';
	$L_ERR_MOTHER_KEY_NOT_LOADED = 'Mother key not loaded';
	$L_ERR_TRANSPORT_FILE_OPEN = 'Transport file open error';
	$L_ERR_TRANSPORT_FILE_READ = 'Transport file read error';
	$L_ERR_TRANSPORT_FILE_CREATION = 'Error on creation Transport file';
	$L_ERR_MISSING_KEY = 'One missing key';
	$L_ERR_SECRET_FILE_OPEN = 'Secret file open error';
	$L_ERR_SECRET_FILE_READ = 'Secret file read error';
	$L_ERR_SECRET_FILE_CREATION = 'Error on creation Secret file';
	
	$L_MOTHER_KEY_LOADED = 'Mother key loaded';
	$L_MOTHER_KEY_AUTOMATICALLY_CREATED = 'Mother key automatically created';
	$L_MOTHER_KEY_MANUALLY_CREATED = 'Mother key manually created';

	$L_Operator = 'Operator';
	$L_Load_Mother_Key = 'Load mother key';
	$L_Operator_Key = 'Operator\'s key';
	$L_Mother_Key = 'Mother key';
	$L_Insert_Operator_Key = 'Insert operator key';
	$L_Insert_Mother_Key = 'Insert mother key';
	$L_Use_SecretServer = 'Use SecretServer';
	$L_Create_New_Keys = 'Create new keys';
	
	$L_New_Keys_Created = 'New keys created';
	$L_Confidentials = 'Confidential Information';
	
	$L_Success_Page = "<h1>" . $L_Confidentials . "</h1>\n" .
	 "<p>Important 1 : <span class=\"normal\">this page will not be regenerate, preserve it in a safe place.</span></p>" .
	 "<p>Important 2 : <span class=\"normal\">the previous file was renamed.</span></p>" .
	 "<p>Important 3 : <span class=\"normal\">reencrypted think your database if it previously contained data.</span></p>";

	$L_Shutdown_SecretServer = 'Shutdown SecretServer';
	
?>