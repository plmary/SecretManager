<?php
/**
* Libellé spécifique à la gestion des utilisateurs.
*
* @warning Ce fichier doit impérativement être au format UTF-8 pour une gestion cohérente des caractères accentués.
*
* @copyright LGPL License 3.0 http://www.gnu.org/copyleft/lesser.html
* @author Pierre-Luc MARY
* @date 2013-03-25
* @version 1.2
*/

	$L_Title = 'Gestion des utilisateurs';
	$L_List_Users = 'Liste des Utilisateurs';
	$L_List_Entities = 'Liste des Entités';
	$L_List_Civilities = 'Liste des Civilités';
	$L_List_Profiles = 'Liste des Profils';
	$L_First_Name = 'Prénom' ;
	$L_Last_Name = 'Nom' ;
	$L_Sex = 'Sexe';
	$L_Man = 'Homme';
	$L_Woman = 'Femme';
	
	$L_User_Create = 'Création d\'un utilisateur';
	$L_User_Delete = 'Suppression d\'un utilisateur';
	$L_User_Modify = 'Modification d\'un utilisateur';
	$L_User_View = 'Visualisation d\'un utilisateur';
	
	$L_Entity_Create = 'Création d\'une entité';
	$L_Entity_Modify = 'Modification d\'une entité';
	$L_Entity_Delete = 'Suppression d\'une entité';
	
	$L_Civility_Create = 'Création d\'une civilité';
	$L_Civility_Modify = 'Modification d\'une civilité';
	$L_Civility_Delete = 'Suppression d\'une civilité';
	
	$L_Profile_Create = 'Création d\'un profil';
	$L_Profile_Modify = 'Modification d\'un profil';
	$L_Profile_Delete = 'Suppression d\'un profil';
	
	$L_Entity = 'Entité';
	$L_Civility = 'Civilité';
	$L_Auditor = 'Auditeur';
	$L_Administrator = 'Administrateur';

	$L_User_Created = 'Utilisateur créé' ;
	$L_User_Modified = 'Utilisateur modifié' ;
	$L_User_Deleted = 'Utilisateur supprimé' ;

	$L_Entity_Created = 'Entité créée' ;
	$L_Entity_Modified = 'Entité modifiée' ;
	$L_Entity_Deleted = 'Entité supprimée' ;

	$L_Civility_Created = 'Civilité créée' ;
	$L_Civility_Modified = 'Civilité modifiée' ;
	$L_Civility_Deleted = 'Civilité supprimée' ;
	
	$L_Profile_Created = 'Profil créé' ;
	$L_Profile_Modified = 'Profil modifié' ;
	$L_Profile_Deleted = 'Profil supprimé' ;
	
	$L_Change_Authenticator_Flag = 'Changer l\'authentifiant';
	$L_Attempt = 'Tentative';
	$L_Disabled = 'Désactiver';
	$L_Enabled = 'Activer';
	$L_Last_Connection = 'Dernière connexion';
	$L_Expiration_Date = 'Date d\'expiration';
	$L_Updated_Authentication = 'Date de changement authentifiant';
	$L_Users_List_Return = 'Retour à la liste des utilisateurs';
	$L_Never_Connected = 'Jamais connecté';
	$L_To_Activate_User = 'Activer l\'utilisateur';
	$L_To_Deactivate_User = 'Désactiver l\'utilisateur';
	
	$L_Authenticator_Reset = 'Réinitialiser le mot de passe';
	$L_Password_Reseted = 'Mot de passe réinitialisé';
	$L_Attempt_Reset = 'Réinitialiser le nombre de tentative';
	$L_Attempt_Reseted = 'Nombre de tentative réinitialisé';
	$L_Expiration_Date_Reset = 'Réinitialiser la date d\'expiration';
	$L_Expiration_Date_Reseted = 'Date d\'expiration réinitialisée';
	
	$L_ERR_CREA_Entity = 'Erreur durant la création de l\'entité';
	$L_ERR_MODI_Entity = 'Erreur durant la modification de l\'entité';
	$L_ERR_DELE_Entity = 'Erreur durant la suppression de l\'entité';
	$L_ERR_DUPL_Entity = '"Code" ou "Libellé" déjà utilisé';
	
	$L_ERR_CREA_Civility = 'Erreur durant la création de la civilité';
	$L_ERR_MODI_Civility = 'Erreur durant la modification de la civilité';
	$L_ERR_DELE_Civility = 'Erreur durant la suppression de la civilité';
	$L_ERR_DUPL_Civility = '"Prénom" et "Nom" déjà utilisé';
	
	$L_ERR_CREA_Identity = 'Erreur durant la création de l\'identité';
	$L_ERR_MODI_Identity = 'Erreur durant la modification de l\'identité';
	$L_ERR_DELE_Identity = 'Erreur durant la suppression de l\'identité';
	$L_ERR_DUPL_Identity = '"Nom d\'utilisateur" déjà utilisé';
	
	$L_ERR_RST_Password = 'Erreur durant la réinitialisation du mot de passe';
	$L_ERR_RST_Attempt = 'Erreur durant la réinitialisation du nombre de tentative';
	$L_ERR_RST_Expiration = 'Erreur durant la réinitialisation de la date d\'expiration';
	$L_ERR_RST_Disable = 'Erreur durant l\'activation ou la désactivation de l\'utilisateur';

	$L_ERR_CREA_Profile = 'Erreur durant la création du profil';
	$L_ERR_MODI_Profile = 'Erreur durant la modification du profil';
	$L_ERR_DELE_Profile = 'Erreur durant la suppression du profil';
	$L_ERR_DUPL_Profile = '"Libellé" déjà utilisé';
	
	$L_Attempt_Exceeded = 'Nombre de tentatives de connexion excédé';
	$L_User_Disabled = 'Utilisateur désactivé';
	$L_User_Enabled = 'Utilisateur activé';
	$L_User_Expired = 'Utilisateur expiré';
	$L_Expiration_Date_Exceeded = 'Date d\'expiration atteinte';
	$L_Last_Connection_Old = 'Date de dernière connexion trop ancienne';
	$L_Association_Terminated = 'Association terminée';

	$L_Users_Profiles = 'Association des Profils à une Identité';
	$L_Profiles_Management = "Gestion des Profils";
	$L_Associated_Profiles = "Profils à associer";
	$L_Users_Associate = 'Associer des utilisateurs';
	
	$L_Reactivated_Civility = 'La civilité a été réactivée';
	
	$L_Confirm_Delete_Entity = 'Confirmez vous la suppression de cette Entité : ';
    $L_Confirm_Delete_Civility = 'Confirmez vous la suppression de cette Civilité : ';
?>