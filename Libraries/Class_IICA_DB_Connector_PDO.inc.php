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
* @date 2014-06-24
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
		
		PDO::__construct( $DSN, $_User, $_Password );
		
		return true;
	}

	protected $transactionBegin = false;


	public function beginTransaction() {
	/**
	* Mise en place d'une Transaction (permettant l'exécution de plusieurs requêtes SQL et de valider cet ensemble)
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Antoine Radiguet
	* @date 2014-12-10
	*
	* @return Passe l'objet en mode Transaction
	*/
		$Query = $this->prepareSQL( 'BEGIN' );
		$this->executeSQL( $Query );

		$this->transactionBegin = true;
	}


	public function commitTransaction(){
	/**
	* Valide l'ensemble des requêtes de mise à jour de la Transaction.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Antoine Radiguet
	* @date 2014-12-10
	*
	* @return Passe l'objet en mode "autocommit"
	*/
		if ( ! $this->transactionBegin ) return;

		$Query = $this->prepareSQL( 'COMMIT' );
		$this->executeSQL( $Query );

		$this->transactionBegin = false;
	}


	public function rollbackTransaction(){
	/**
	* Annule l'ensemble des requêtes de mise à jour de la Transaction.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Antoine Radiguet
	* @date 2014-12-10
	*
	* @return Conserve l'objet en mode Transaction
	*/
		if ( ! $this->transactionBegin ) return;

		$Query = $this->prepareSQL( 'ROLLBACK' );
		$this->executeSQL($Query);

		$this->transactionBegin = true;
	}


	protected function prepareSQL( $sql ) {
	/**
	* Automatise la préparation d'une requète en ajoutant la gestion des exceptions
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Antoine Radiguet
	* @date 2013-10-24
	*
	* @param[in] $sql La requète à préparer
	*
	* @return Renvoi la requète préparée
	*/
		// évite les espaces inséquables dans la chaîne de caractère :s
		$sql = str_replace(" ", " ", $sql);

		if ( ! $Query = $this->prepare( $sql ) ) {
			$Error = $Query->errorInfo();

			$this->rollbackTransaction();

			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		return $Query;
	}


	protected function bindSQL( $Query, $Reference, $Value, $Type, $Length = 10 ){
	/**
	* Automatise l'association des paramètres sur une requète SQL.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Antoine Radiguet
	* @date 2013-10-24
	*
	* @param[in] $Query La requète modifier, passé en référence
	* @param[in] $Reference la chaine de caractère référence à remplacer dans la requête
	* @param[in] $Value la valeur à mettre à la place de la référence
	* @param[in] $Type le type de variable à remplacer. Pour l'instant ne sont géré que les entiers et les chaines de caractères
	* @param[in] $Length la longueur maximal de la chaine de caractère à remplacer
	*
	*/
		// Si le type est un "Numérique".
		if( $Type === PDO::PARAM_INT || $Type === PDO::PARAM_BOOL || $Type === PDO::PARAM_LOB ) {
			if ( ! $Query->bindParam( $Reference, $Value, $Type ) ) {
				$Error = $Query->errorInfo();
				$this->rollbackTransaction();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
		// Si le type est une "chaine de caractères".
		elseif($Type === PDO::PARAM_STR){
			if ( ! $Query->bindParam( $Reference, $Value,$Type, $Length ) ) {
				$Error = $Query->errorInfo();
				$this->rollbackTransaction();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
		else {
			$this->rollbackTransaction();
			throw new Exception( "bindSQL - Format de donnée non géré");
		}

		// permet les appels en cascade 
		return $this;
	}


	protected function executeSQL($Query){
	/**
	* Automatise l'execution d'une requète
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Antoine Radiguet
	* @date 2013-10-24
	*
	* @param[in] $Query La requète à executer, passé en référence
	*
	*/	
		$Status = $Query->execute();
		if ( $Status === false ) {
			$Error = $Query->errorInfo();

			$this->rollbackTransaction();

			$message = $Error[ 2 ] . ' (' . $Error[ 1 ] . ')';

			throw new Exception($message, $Error[ 1 ] );
		}
		
		// permet les appels en cascade
		return $Query;
	}

}