<?php

include( DIR_LIBRARIES . '/Config_Access_Tables.inc.php' );

// ===================================
class IICA_Referentials extends IICA_DB_Connector {
/**
* Cette classe gère les référentiels internes.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-24
*/

	/* ===============================
	** Connexion à la base de données.
	*/
	public function __construct() {
		parent::__construct();
		
		return true;
	}


	public function listRights() {
	/**
	* Lister les Droits
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-19
	*
	* @return Renvoi un tableau contentant tous les Droits disponibles dans la Base de Données, sinon lève une exception
	*/
		$Data = false;
		
		$Request = 'SELECT ' .
		 'rgh_id, rgh_name ' .
		 'FROM rgh_rights ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function listSecretTypes() {
	/**
	* Lister les Types de Secret.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-19
	*
	* @return Renvoi un tableau contenant les Types de Secrets en Base de Données, sinon lève une exception
	*/
		$Data = false;
		
		$Request = 'SELECT ' .
		 'stp_id, stp_name ' .
		 'FROM stp_secret_types ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function listEnvironments() {
	/**
	* Lister les Environnements.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-19
	*
	* @return Renvoi un tableau contenant les ENvironnements en Base de Données, sinon lève une exception
	*/
		$Data = false;
		
		$Request = 'SELECT ' .
		 'env_id, env_name ' .
		 'FROM env_environments ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function listActions() {
	/**
	* Lister les Actions dans l'Historique.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-19
	*
	* @return Renvoi un tableau contenant les Actions en Base de Données, sinon lève une exception
	*/
		$Data = false;
		
		$Request = 'SELECT ' .
		 'hac_id, hac_name ' .
		 'FROM hac_history_actions_codes ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}

} // Fin class IICA_Referentials

?>