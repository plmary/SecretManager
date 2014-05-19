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

	$L_Title = 'Gestion des préférences';
	
	$L_Welcome = 'Accueil';
	$L_Alerts = 'Alertes';
	$L_Connection = 'Connexion';
	$L_Welcome_Text = 'Attention : les paramètres présents dans ces différents onglets conditionnent la sécurité globale de SecretManager et peuvent provoquer de graves dysfonctionnements.' ;
	
	$L_Alert_Management = 'Gestion des alertes';
	$L_Verbosity_Alert = 'Verbosité des alertes';
	$L_Alert_Syslog = 'Alerte remontée via Syslog';
	$L_Alert_Mail = 'Alerte remontée via Courriel';
	$L_Detailed_Verbosity = 'Détaillée'; // Verbosité détaillée remontée
	$L_Technical_Verbosity = 'Technique'; // Verbosité technique remontée
	$L_Normal_Verbosity = 'Normale'; // Verbosité intelligible remontée
	$L_Language_Alerts = 'Langue des alertes';	

	$L_Parameter_Updated = 'Paramètre mis à jour';
	$L_Parameters_Updated = 'Paramètres mis à jour';

	$L_SecretServer_Keys = 'Sécurisation des clés utilisées par le SecretServer';
	$L_Min_Key_Size = 'Taille minimum de la clé';
	$L_Key_Complexity = 'Complexité de la clé';
	$L_Mother_Key = 'Clé Mère';
	$L_Operator_Key = 'Clé Opérateur';

	$L_Mail_From = "Nom de l'émetteur";
	$L_Mail_To = "Les noms des destinataires doivent être séparés par des virgules";
	$L_Title_1 = 'Titre';
	$L_Mail_Title = 'Titre du message';
	$L_Mail_Body = "Corps du message";
	$L_Body = 'Corps';
	$L_Mail_Body_Type = 'Type du corps du message';
	$L_Body_Type = 'Type du corps';

	$L_Connection_Management = 'Gestion du processus de connexion';
	$L_Use_Password = 'Utilisation de l\'authentification par mots de passe';
	$L_Use_Radius = 'Utilisation de l\'authentification par Radius';
	$L_Use_LDAP = 'Utilisation de l\'authentification par LDAP';

	$L_Min_Size_Password = 'Taille minimum des mots de passe';
	$L_Password_Complexity = 'Complexité des mots de passe';
	$L_Default_User_Lifetime = 'Durée de vie d\'un utilisateur (en mois)';
	$L_Max_Attempt = 'Nombre de tentatives maximum';
	$L_Default_Password = 'Mot de passe par défaut';
	$L_Expiration_Time = 'Temps avant expiration de la session (en minutes)';
	$L_Radius_Server = 'Adresse IP du serveur Radius';
	$L_Radius_Authentication_Port = 'Port d\'authentification du serveur Radius';
	$L_Radius_Accounting_Port = 'Port d\'accounting du serveur Radius';
	$L_Radius_Secret_Common = 'Secret partagé de Radius';
	$L_LDAP_Server = 'Adresse IP du serveur LDAP';
	$L_LDAP_Port = 'Port du serveur LDAP';
	$L_LDAP_Protocol_Version = 'Version du protocole LDAP';
	$L_LDAP_Organization = 'Organisation du LDAP';
	$L_LDAP_RDN_Prefix = 'Préfixe RDN LDAP';
	$L_Testing_Connection = 'Tester connexion';
	
	$L_ERR_MAJ_Alert = 'Erreur durant la mise à jour des paramètres d\'Alertes';
	$L_ERR_MAJ_Connection = 'Erreur durant la mise à jour des paramètres de Connexion';
?>