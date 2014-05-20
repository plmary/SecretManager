<?php

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php' );

class MyApplications extends IICA_DB_Connector {
/**
* Cette classe gère les Applications du Client associées aux Secrets.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
*/
	public $LastInsertId;

	public function __construct() {
	/**
	* Connexion à la base de données.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @return Renvoi un booléen sur le succès de la connexion à la base de données
	*/
		parent::__construct();
		
		return true;
	}


	/* ===============================================================================
	** Gestion des Applications
	*/
	
	public function set( $app_id, $app_name ) {
	/**
	* Créé ou actualise une Entité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-13
	*
	* @param[in] $ent_id Identifiant de l'entité à modifier (si précisé)
	* @param[in] $Code Code de l'entité
	* @param[in] $Label Libeller de l'entité
	*
	* @return Renvoi vrai si l'entité a été créée ou modifiée.
	*/
		if ( $app_id == '' ) {
			if ( ! $Result = $this->prepare( 'INSERT INTO app_applications ' .
				'( app_name ) ' .
				'VALUES ( :app_name )' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			if ( ! $Result = $this->prepare( 'UPDATE app_applications SET ' .
				'app_name = :app_name ' .
				'WHERE app_id = :app_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			if ( ! $Result->bindParam( ':app_id', $app_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
				
		if ( ! $Result->bindParam( ':app_name', $app_name, PDO::PARAM_STR, 100 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $app_id == '' ) {
			switch( $this->getAttribute(PDO::ATTR_DRIVER_NAME) ) {
			 default;
				$this->LastInsertId = $this->lastInsertId();
				break;

			 case 'pgsql';
				$this->LastInsertId = $this->lastInsertId( 'app_applications_app_id_seq' );
				break;
			}
		}
		
		return true;
	}


	public function listApplications( $orderBy = 'name') {
	/**
	* Lister les Applications.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @return Renvoi une liste des Applications ou une liste vide.
	*/
		$Request = 'SELECT ' .
		 'app_id, app_name ' .
		 'FROM app_applications ' ;
		
		switch( $orderBy ) {
		 default:
		 case 'name':
		 	$Request .= 'ORDER BY app_name ';
			break;

		 case 'name-desc':
		 	$Request .= 'ORDER BY app_name DESC ';
			break;
		}
		 
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


	public function get( $app_id ) {
	/**
	* Récupère les informations d'une Application.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @param[in] $app_id Identifiant de l'application à récupérer
	*
	* @return Renvoi l'occurrence d'une Application
	*/
		$Data = false;
		
		$Request = 'SELECT ' .
		 'app_id, app_name ' .
		 'FROM app_applications ' .
		 'WHERE app_id = :app_id ' ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':app_id', $app_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return $Result->fetchObject() ;
	}


	public function delete( $app_id ) {
	/**
	* Supprimer une Entité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @param[in] $app_id Identifiant de l'Application à supprimer
	*
	* @return Renvoi vrai si l'Application a été supprimée
	*/
		/*
		** Détruit l'entité physiquement.
		*/
        if ( ! $Result = $this->prepare( 'DELETE ' .
         'FROM app_applications ' .
         'WHERE app_id = :app_id' ) ) {
            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
        }
		
		$Result->bindParam( ':app_id', $app_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
 		return true;
	}


	public function total() {
	/**
	* Calcul le nombre total d'Application.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-12-24
	*
	* @return Renvoi le nombre total d'Applications stockées en base
	*/
		$Request = 'SELECT ' .
		 'count(*) AS total ' .
		 'FROM app_applications ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject() ;
		
		return $Occurrence->total;
	}


	/* -------------------
	** Construit le message détaillé à remonter dans l'Historique.
	*/
	public function getMessageForHistory( $app_id, $Application = '' ) {
		if ( $app_id == '' ) return '';

		include_once( DIR_LIBRARIES . '/Class_HTML.inc.php');

		$pHTML = new HTML();

    	// Récupère les dernières informations du Secret qui vient d'être modifié.
    	if ( $Application == '' ) $Application = $this->get( $app_id );

    	// Récupère les libellés pour le message
    	$Labels = $pHTML->getTextCode( 'L_Name' );

    	return ' (' . $Labels . ':"' . $Application->app_name . '")';
    }


} // Fin class MyApplications

?>