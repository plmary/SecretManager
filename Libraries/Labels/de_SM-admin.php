<?php
	$L_Title = 'Verwalten';

	$L_Total_Users_Base = 'Gesamtzahl Anwender in der Datenbank';
	$L_Total_Users_Disabled = 'Deaktivierte Benutzeraccounts';
	$L_Total_Users_Expired = 'Abgelaufene Benutzeraccounts';
	$L_Total_Users_Attempted = 'Benutzeraccounts mit fehlgeschlagenen Anmeldeversuchen';
	$L_Total_Users_Super_Admin = 'root Benutzeraccounts';

	$L_Total_Groups_Base = 'Gesamtzahl Passwortgruppen in der Datenbank';
	$L_Total_Profiles_Base = 'Gesamtzahl Profile in der Datenbank';
	$L_Total_Entities_Base = 'Gesamtzahl Instanzen in der Datenbank';
	$L_Total_Civilities_Base = 'Gesamtzahl Civilities in der Datenbank';

	$L_Historical_Records_Management = 'Records Management historical';
	$L_Total_Historical_Records = 'Gesamtzahl der Datensätze in der historical';

	$L_Manage_Users = 'Benutzer verwalten';
	$L_Manage_Groups = 'Passwortgruppen verwalten';
	$L_Manage_Profiles = 'Profile verwalten';
	$L_Manage_Entities = 'Instanzen verwalten';
	$L_Manage_Civilities = 'Civilities verwalten';
	$L_Manage_Historical = 'Historical verwalten';
	
	$L_Specify_Purge_Date_History = 'Geben Sie ein Datum für die Freigabe von zeit';
	$L_Oldest_Date_History = 'Älteste Datum in der Geschichte';
	$L_Purge_Historical = 'Spülen Histories';
	$L_No_Purge_Date = 'Kein Datum angegeben Spülung, so dass keine Spülung durchgeführt';
	$L_Success_Purge = 'Die Ereignisse der Geschichte, vor dem Tag der "%s" wurden gespült';
	
	$L_SecretServer_Management = 'SecretServer management';
	$L_Manage_SecretServer = 'SecretServer verwalten';

	$L_New_Operator_Key = 'Erstellen eines neuen Operatorkey';
	$L_New_Mother_Key = 'Erstellen einer neuen Mutterkey';
	$L_Insert_New_Operator_Key = 'Setzen Sie den Wert der neuen Operatorkey';
	$L_Insert_New_Mother_Key = 'Setzen Sie den Wert der neuen Mutterkey';
	$L_Transcrypt = 'Transcrypt';
	$L_Transcrypt_Mother_Key = 'Transcrypt Mutterkey';

    $L_Confirm_Operation = 'Sie bestätigen, diese Operation?';
	$L_Warning_Transcrypt_mother_key = 'Diese Operation wird der Mutterkey mit dem angegebenen Operatorkey transcrypt. ' .
	    $L_Confirm_Operation;
	$L_Operation_Cancel_Not_Given_Keys = 'Dieser Vorgang kann nicht durchgeführt, weil die Mutterkey oder die Operatorkey nicht gegeben werden';
	$L_Operation_Cancel_Not_Given_O_Key = 'Dieser Vorgang kann nicht durchgeführt, weil die Operatorkey nicht gegeben werden';
	$L_Warning_Change_Mother_Key = 'This operation will chnage Mother Key and transcrypt all secrets in database. ' .
	    $L_Confirm_Operation;
	$L_Warning_Create_Mother_Key = 'This operation will create a new mother key without transcrypt secrets in the database. ' . $L_Confirm_Operation;

    $L_Backup_Secrets_Successful = 'Sicherung erfolgreich Geheimnisse';
?>