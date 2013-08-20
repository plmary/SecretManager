<?php

include_once( 'Constants.inc.php' );

class IICA_DB_Connector extends PDO {
/**
* Cette classe gère les connexions à la base de données tout en offrant une couche d'abastraction.
* Effectivement, seule cette classe connait la localisation du fichier de paramètre externe.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
*/

	public function __construct() {
	/**
	* Connexion à la base de données via le contructeur de PDO.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2013-07-19
	*
	* @param[in] $_Host Noeud sur lequel s'exécute la base de données
	* @param[in] $_Port Port IP sur lequel répond la base de données
	* @param[in] $_Driver Type de la base de données
	* @param[in] $_Base Nom de la base de données
	* @param[in] $_User Nom de l'utilisateur dans la base de données
	* @param[in] $_Password Mot de passe de l'utilisateur dans la base de données
	*
	* @return Renvoi un booléen sur le succès de la connexion à la base de données
	*/
		// Charge les différentes variables utiles à la connexion à la base de données.
		include( IICA_DB_CONFIG );

		$DSN = $_Driver . ':host=' . $_Host . ';port=' . $_Port . ';dbname=' . $_Base ;
		
		parent::__construct( $DSN, $_User, $_Password );
		
		return true;
	}
}