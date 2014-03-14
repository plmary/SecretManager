<?php
	$L_Title = 'Management';

	$L_Total_Users_Base = 'Overall users in database';
	$L_Total_Users_Disabled = 'Disabled user accounts';
	$L_Total_Users_Expired = 'Expired user accounts';
	$L_Total_Users_Attempted = 'User accounts that reached max auth. attempts';
	$L_Total_Users_Super_Admin = 'root accounts';

	$L_Total_Groups_Base = 'Overall Secrets Groups in database';
	$L_Total_Profiles_Base = 'Overall Profiles in database';
	$L_Total_Entities_Base = 'Overall Entities in database';
	$L_Total_Civilities_Base = 'Overall Civilities in database';

	$L_Historical_Records_Management = 'Historical records management';
	$L_Total_Historical_Records = 'Total records in historical';

	$L_Manage_Users = 'Manage users';
	$L_Manage_Groups = 'Manage groups of secrets';
	$L_Manage_Profiles = 'Manage profiles';
	$L_Manage_Entities = 'Manage entities';
	$L_Manage_Civilities = 'Manage civilities';
	
	$L_Specify_Purge_Date_History = 'Specify a date for purging historical';
	$L_Oldest_Date_History = 'Oldest date in history';
	$L_Purge_Historical = 'Purging Histories';
	$L_No_Purge_Date = 'No date specified purge, so no purge carried';
	$L_Success_Purge = 'The events of history, before the date of "%s", were purged';
	$L_Manage_Historical = 'Manage historical';
	
	$L_SecretServer_Management = 'SecretServer management';
	$L_Manage_SecretServer = 'Manage SecretServer';

	$L_New_Operator_Key = 'Creating a new Operator key';
	$L_New_Mother_Key = 'Creating a new Mother key';
	$L_Insert_New_Operator_Key = 'Insert the value of the new Operator key';
	$L_Insert_New_Mother_Key = 'Insert the value of the new Mother key';
	$L_Transcrypt = 'Transcrypt';
	$L_Transcrypt_Mother_Key = 'Transcrypt mother key';

    $L_Confirm_Operation = 'Do you confirm this operation?';
	$L_Warning_Transcrypt_mother_key = 'This operation will transcrypt mother key with specified operator key. ' .
	    $L_Confirm_Operation;
	$L_Operation_Cancel_Not_Given_Keys = 'This operation can not be performed because the mother key or the operator key is not given';
	$L_Operation_Cancel_Not_Given_O_Key = 'This operation can not be performed because the operator key is not given';
	$L_Warning_Change_Mother_Key = 'This operation will chnage Mother Key and transcrypt all secrets in the database. ' . $L_Confirm_Operation;
	$L_Warning_Create_Mother_Key = 'This operation will create a new mother key without transcrypt secrets in the database. ' . $L_Confirm_Operation;

    $L_SecretManager_Control = 'SecretManager installation control';
    $L_Run_Control = 'Run control';

?>