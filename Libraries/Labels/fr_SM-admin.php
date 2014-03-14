<?php
	$L_Title = 'Administration';

	$L_Total_Users_Base = 'Nombre total d\'utilisateurs en base';
	$L_Total_Users_Disabled = 'Utilisateurs désativés';
	$L_Total_Users_Expired = 'Utilisateurs expirés';
	$L_Total_Users_Attempted = 'Utilisateurs ayant dépassé le nombre d\'essais';
	$L_Total_Users_Super_Admin = 'Utilisateurs super admin';

	$L_Total_Groups_Base = 'Nombre total de groupes en base';
	$L_Total_Profiles_Base = 'Nombre total de profils en base';
	$L_Total_Entities_Base = 'Nombre total d\'entités en base';
	$L_Total_Civilities_Base = 'Nombre total de civilités en base';
	
	$L_Historical_Records_Management = 'Gestion de l\'historique en base';
	$L_Total_Historical_Records = 'Nombre total d\'enregistrements dans l\'historique';

	$L_Manage_Users = 'Gérer les utilisateurs';
	$L_Manage_Groups = 'Gérer les groupes de secrets';
	$L_Manage_Profiles = 'Gérer les profils';
	$L_Manage_Entities = 'Gérer les entités';
	$L_Manage_Civilities = 'Gérer les civilités';
	
	$L_Specify_Purge_Date_History = 'Préciser une date de purge pour l\'historique';
	$L_Oldest_Date_History = 'Plus vieille date dans historique';
	$L_Purge_Historical = 'Purge de l\'historique';
	$L_No_Purge_Date = 'Pas de date purge précisée, donc pas de purge réalisée';
	$L_Success_Purge = 'Les événements de l\'historique, avant la date du "%s", ont été purgés';
	$L_Manage_Historical = 'Gérer l\'historique';
	
	$L_SecretServer_Management = 'Gestion du SecretServer';
	$L_Manage_SecretServer = 'Gérer le SecretServer';

	$L_New_Operator_Key = 'Création d\'une nouvelle clé Opérateur';
	$L_New_Mother_Key = 'Création d\'une nouvelle clé Mère';
	$L_Insert_New_Operator_Key = 'Insérer la valeur de la nouvelle clé Opérateur';
	$L_Insert_New_Mother_Key = 'Insérer la valeur de la nouvelle clé Mère';
	$L_Transcrypt_Mother_Key = 'Transchiffrer la clé Mère';
	$L_Transcrypt = 'Transchiffrer';
	
	$L_Confirm_Operation = 'Confirmez vous cette opération ?';
	$L_Warning_Transcrypt_mother_key = 'Cette opération va transchiffrer la clé Mère avec la clé Operateur précisée. ' . $L_Confirm_Operation;
	$L_Operation_Cancel_Not_Given_Keys = 'Cette opération ne peut être réalisée car la clé mère ou la clé opérateur n\'est pas renseignée';
	$L_Operation_Cancel_Not_Given_O_Key = 'Cette opération ne peut être réalisée car la clé opérateur n\'est pas renseignée';
	$L_Warning_Change_Mother_Key = 'Cette opération va changer la clé Mère et transchiffrer tous les secrets dans la base de données. ' . $L_Confirm_Operation;
	$L_Warning_Create_Mother_Key = 'Cette opération va créer une nouvelle cle Mère sans lancer le transchiffrement des secrets dans la base de données. ' . $L_Confirm_Operation;

    $L_SecretManager_Control = 'Contrôler l\'installation du SecretManager';
    $L_Run_Control = 'Exécuter le contrôle';
?>