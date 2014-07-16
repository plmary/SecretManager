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

	$L_ERR_SERVER_NOT_LOADED = 'SecretServer non chargé';
	$L_ERR_SERVER_NOT_STARTED = 'SecretServer non démarré';
	$L_ERR_MOTHER_KEY_CORRUPTED = 'Clé mère corrompue ou invalide';
	$L_ERR_NO_CMD_SEND = 'Pas de commande envoyée';
	$L_ERR_NO_SESSION = 'Pas d\'ID de Session';
	$L_ERR_NO_VALUE = 'Pas de valeur';
	$L_ERR_INVALID_SESSION = 'Session Invalide ou Session non trouvée';
	$L_ERR_SESSION_EXPIRED = 'Session expirée';
	$L_ERR_USER_NOT_CONNECTED = 'Utilisateur non connecté';
	$L_ERR_USER_NOT_ADMIN = 'Cette utilisateur n\'est pas administrateur';
	$L_ERR_OPERATOR_KEY_EMPTY = 'Erreur, la Clé Opérateur est vide';
	$L_ERR_MOTHER_KEY_EMPTY = 'Erreur, la Clé Mère est vide';
	$L_ERR_INVALID_OPERATOR_KEY = 'Fichier contenant la clé mère corrompu ou Clé Opérateur invalide';
	$L_ERR_MOTHER_KEY_NOT_LOADED = 'Clé mère non chargée';
	$L_ERR_TRANSPORT_FILE_OPEN = 'Erreur à l\'ouverture du fichier contenant la Clé de Transport';
	$L_ERR_TRANSPORT_FILE_READ = 'Erreur à la lecture du fichier contenant la Clé de Transport';
	$L_ERR_TRANSPORT_FILE_CREATION = 'Erreur à la création du fichier pour la Clé de Transport';
	$L_ERR_MISSING_KEY = 'Une clé omise';
	$L_ERR_SECRET_FILE_OPEN = 'Erreur à l\'ouverture du fichier contenant la Clé Mère';
	$L_ERR_SECRET_FILE_READ = 'Erreur à la lecture du fichier contenant la Clé Mère';
	$L_ERR_SECRET_FILE_CREATION = 'Erreur à la création du fichier pour la Clé Mère';
	$L_ERR_TRANSCRYPT = 'Erreur durant le transchiffrement de la base';
	$L_ERR_INVALID_OPERATOR_KEY_BACKUP = 'La Clé Opérateur n\'ouvre pas la Clé Mère du fichier à Restaurer';
	$L_ERR_MASTER_INTEGRITY_ALERT = 'Alerte sur l\'intégrité du fichier principal (SecretManager)';
	$L_ERR_SECRETSERVER_INTEGRITY_ALERT = 'Alerte sur l\'intégrité du fichier secondaire (SecretServer)';

	$L_MOTHER_KEY_LOADED = 'Clé Mère chargée';
	$L_MOTHER_KEY_AUTOMATICALLY_CREATED = 'Clé Mère crée automatiquement';
	$L_MOTHER_KEY_MANUALLY_CREATED = 'Clé Mère crée manuellement';
	$L_MOTHER_KEY_TRANSCRYPTED = 'Clé Mère transchiffrée';

	$L_Operator = 'Opérateur';
	$L_Load_Mother_Key = 'Charger la clé mère';
	$L_Creation_Mother_Key = 'Création de la clé mère';
	$L_Operator_Key = 'Clé Opérateur';
	$L_Mother_Key = 'Clé Mère';
	$L_Insert_Operator_Key = 'Insérer la valeur de la clé Opérateur';
	$L_Insert_Mother_Key = 'Insérer la valeur de la clé Mère';
	$L_Use_SecretServer = 'Utiliser le SecretServer';
	$L_Create_New_Keys = 'Créer de nouvelles clés';
	
	$L_New_Keys_Created = 'Nouvelles clés de chiffrement créées';
	$L_Confidentials = 'Informations Confidentielles';
	
	$L_Success_Page = "<h1>" . $L_Confidentials . "</h1>\n" .
	 "<p>Important 1 : <span class=\"normal\">cette page ne sera pas regénérée, veillez à la conserver dans un lieu sur.</span></p>" .
	 "<p>Important 2 : <span class=\"normal\">le précédent fichier 'secret.dat' a été renommé.</span></p>";

	$L_Shutdown_SecretServer = 'Eteindre le SecretServer';

?>