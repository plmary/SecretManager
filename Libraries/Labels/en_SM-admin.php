<?php
	$L_Title = 'Management';

	$L_Total_Users_Base = 'Overall users in database';
	$L_Total_Users_Disabled = 'Disabled user accounts';
	$L_Total_Users_Expired = 'Expired user accounts';
	$L_Total_Users_Attempted = 'User accounts that reached max auth. attempts';
	$L_Total_Users_Super_Admin = 'Root accounts';
	$L_Total_Users_API = 'API users';
	$L_Total_Users_Operator = 'Operator user accounts';
	
	$L_Total_Groups_Base = 'Overall Secrets Groups in database';
	$L_Total_Profiles_Base = 'Overall Profiles in database';
	$L_Total_Entities_Base = 'Overall Entities in database';
	$L_Total_Civilities_Base = 'Overall Civilities in database';

	$L_Historical_Records_Management = 'Historical records management';
	$L_Total_Historical_Records = 'Overall records in historical';

	$L_Manage_Users = 'Manage Users';
	$L_Manage_Groups = 'Manage Groups of Secrets';
	$L_Manage_Profiles = 'Manage Profiles';
	$L_Manage_Entities = 'Manage Entities';
	$L_Manage_Civilities = 'Manage Civilities';
	
	$L_Specify_Purge_Date_History = 'Specify a date for purging historical';
	$L_Oldest_Date_History = 'Oldest date in history';
	$L_Purge_Historical = 'Purging Histories';
	$L_No_Purge_Date = 'No date specified purge, so no purge carried';
	$L_Success_Purge = 'The events of history, before the date of "%s", were purged';
	$L_Manage_Historical = 'Manage Historical';
	
	$L_SecretServer_Management = 'SecretServer management';
	$L_Manage_SecretServer = 'Manage SecretServer';

	$L_New_Operator_Key = 'Creating a new Operator key';
	$L_New_Mother_Key = 'Creating a new Mother key';
	$L_Insert_New_Operator_Key = 'Insert the value of the new Operator key';
	$L_Insert_New_Mother_Key = 'Insert the value of the new Mother key';
	$L_Transcrypt = 'Transcrypt';
	$L_Transcrypt_Mother_Key = 'Transcrypt mother key';

    $L_Confirm_Operation = 'Do you confirm this operation?';
	$L_Warning_Transcrypt_mother_key = 'This operation will transcrypt mother key with specified operator key. ' . $L_Confirm_Operation;
	$L_Operation_Cancel_Not_Given_Keys = 'This operation can not be performed because the mother key or the operator key is not given';
	$L_Operation_Cancel_Not_Given_O_Key = 'This operation can not be performed because the operator key is not given';
	$L_Warning_Change_Mother_Key = 'This operation will chnage Mother Key and transcrypt all secrets in the database. ' . $L_Confirm_Operation;
	$L_Warning_Create_Mother_Key = 'This operation will create a new mother key without transcrypt secrets in the database. ' . $L_Confirm_Operation;

    $L_SecretManager_Control = 'SecretManager installation control';
    $L_Run_Control = 'Run control';

    $L_List_Applications = 'Software list';
    $L_Total_Applications_Base = 'Overall Software in database';
    $L_Manage_Applications = 'Manage Software';
    $L_Application_Create = 'Create Software';
    $L_Application_Created = 'Software created';
    $L_ERR_CREA_Application = 'Error while creating the Software';
    $L_ERR_DUPL_Application = 'Software already exists';
    $L_Application_Delete = 'Delete Software';
    $L_Confirm_Delete_Application = 'Do you confirm the deletion of this Software';
    $L_Application_Deleted = 'Software deleted';
    $L_ERR_DELE_Application = 'Error while deleting the Software';
    $L_Application_Modified = 'Sofware modified';
	$L_ERR_MODI_Application = 'Erreur while modifying the Software';
?>