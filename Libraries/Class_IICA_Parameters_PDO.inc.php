<?php

/**
* Cette classe gère les paramètres internes de l'application.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-23
*
* Contrat d'interface :
*  boolean __construct( $_Host, $_Port, $_Driver, $_Base, $_User, $_Password )
*  string get( string $Name )
*  string set( string $Name, string $Value )
*
*/

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php' );

class IICA_Parameters extends IICA_DB_Connector {

	public function __construct() {
	/**
	* Connexion à la base de données.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @return Renvoi un booléen sur le succès de la connexion à la base de données
	*/
		parent::__construct();
		
		return true;
	}


	/* ===============================================================================
	** Gestion des Paramètres
	*/
	
	public function getParameter( $Name ) {
	/**
	* Récupère la valeur d'un paramètre.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $Name Nom du paramètre recherché
	*
	* @return Renvoi une chaîne contenant la valeur du parmètre
	*/
		// -----------------------------------
		// Récupère la valeur d'un paramètre.
		$Request = 'SELECT ' .
		 'spr_value ' .
		 'FROM spr_system_parameters ' .
 		 'WHERE spr_name = :Name ' ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		if ( ! $Result->bindParam( ':Name', $Name, PDO::PARAM_STR, 30 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		$Occurrence = $Result->fetchObject();
		
		if ( isset( $Occurrence->spr_value ) ) $Value = $Occurrence->spr_value;
		else $Value = '';

 		return $Value;
	}


	public function setParameter( $Name, $Value ) {
	/**
	* Crée ou met à jour la valeur d'un paramètre.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $Name Nom du paramètre à créer ou mettre à jour
	* @param[in] $Value Valeur du paramètre à créer ou mettre à jour
	*
	* @return Renvoi vrai si le paramêtre a été mis à jour, sinon renvoi une exception
	*/
		if ( $this->getParameter( $Name ) == '' ) {
			if ( ! $Result = $this->prepare( 'INSERT INTO spr_system_parameters ' .
				'( spr_value, spr_name ) ' .
				'VALUES ( :Value, :Name ) ' ) ) {
					$Error = $Result->errorInfo();
					throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			if ( ! $Result = $this->prepare( 'UPDATE spr_system_parameters SET ' .
				'spr_value = :Value ' .
				'WHERE spr_name = :Name ' ) ) {
					$Error = $Result->errorInfo();
					throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
				
		if ( ! $Result->bindParam( ':Name', $Name, PDO::PARAM_STR, 30 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':Value', $Value, PDO::PARAM_STR, 60 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return true;
	}
}

?>