<?php

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php' );


class IICA_Civilities extends IICA_DB_Connector {
/**
* Cette classe gère les civilités.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-07
*/

   public $LastInsertID;

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
	** Gestion des Civilités
	*/
	
	public function set( $cvl_id, $LastName, $FirstName, $Sex, $BirthDate, $BornTown ) {
	/**
	* Créé ou actualise une Civilité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $cvl_id Identifiant de la civilité (à préciser si modification)
	* @param[in] $LastName Nom de famille de l'utilisateur
	* @param[in] $FirstName Prénom de l'utilisateur
	* @param[in] $Sex Sexe de l'utilisateur
	* @param[in] $BirthDate Date de naissance de l'utilisateur
	* @param[in] $BornTown Ville de naissance de l'utilisateur
	*
	* @return Renvoi un booléen sur le succès de la création ou la modification de la civilité
	*/
		if ( $cvl_id == '' ) {
			if ( ! $Result = $this->prepare( 'INSERT INTO cvl_civilities ' .
				'( cvl_last_name, cvl_first_name, cvl_sex, cvl_birth_date, ' .
				'cvl_born_town ) ' .
				'VALUES ( :LastName, :FirstName, :Sex, :BornDate, :BornTown )' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			if ( ! $Result = $this->prepare( 'UPDATE cvl_civilities SET ' .
				'cvl_last_name = :LastName, ' .
				'cvl_first_name = :FirstName, ' .
				'cvl_sex = :Sex, ' .
				'cvl_birth_date = :BornDate, ' .
				'cvl_born_town = :BornTown ' .
				'WHERE cvl_id = :cvl_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			if ( ! $Result->bindParam( ':cvl_id', $cvl_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
		
		if ( ! $Result->bindParam( ':LastName', $LastName, PDO::PARAM_STR, 35 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':FirstName', $FirstName, PDO::PARAM_STR, 25 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':Sex', $Sex, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':BornDate', $BirthDate,
		 PDO::PARAM_STR, 10 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':BornTown', $BornTown,
		 PDO::PARAM_STR, 40 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
      
		
		if ( $cvl_id == '' ) {
			switch( $this->getAttribute(PDO::ATTR_DRIVER_NAME) ) {
			 default;
				$this->LastInsertId = $this->lastInsertId();
				break;

			 case 'pgsql';
				$this->LastInsertId = $this->lastInsertId( 'cvl_civilities_cvl_id_seq' );
				break;
			}
		}
		
		return true;
	}


	public function listCivilities( $Type = 0, $Order = 'last_name' ) {
	/**
	* Lister les Civilités.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $Type Type d'affichage pour les données supprimées
	* @param[in] $Order Permet de gérer l'ordre d'affichage
	*
	* @return Renvoi une liste de civilité ou une liste vide
	*/
		$Data = false;
		
		$Request = 'SELECT ' .
		 'cvl_id, cvl_last_name, cvl_first_name, cvl_sex, cvl_birth_date, ' .
		 'cvl_born_town ' .
		 'FROM cvl_civilities ' ;

		if ( $Type == 0 ) {
			$Request .= 'WHERE cvl_logical_delete = false ' ;
		}
		
		switch( $Order ) {
		 case 'last_name':
		 default:
			$Request .= 'ORDER BY cvl_last_name ';
			break;

		 case 'last_name-desc':
			$Request .= 'ORDER BY cvl_last_name DESC ';
			break;

		 case 'first_name':
			$Request .= 'ORDER BY cvl_first_name ';
			break;

		 case 'first_name-desc':
			$Request .= 'ORDER BY cvl_first_name DESC ';
			break;

		 case 'sex':
			$Request .= 'ORDER BY cvl_sex ';
			break;

		 case 'sex-desc':
			$Request .= 'ORDER BY cvl_sex DESC ';
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


	public function get( $cvl_id, $Type = 0 ) {
	/**
	* Récupère les informations d'une Civilité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $cvl_id Identifiant de la civilité à afficher
	* @param[in] $Type Type d'affichage pour les données supprimées
	*
	* @return Renvoi l'occurrence d'une civilité
	*/
		$Request = 'SELECT ' .
		 'cvl_last_name, cvl_first_name, cvl_sex, cvl_birth_date, ' .
		 'cvl_born_town ' .
		 'FROM cvl_civilities ' .
		 'WHERE cvl_id = :cvl_id ' ;

		if ( $Type == 0 ) {
			$Request .= 'AND cvl_logical_delete = false ' ;
		}
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':cvl_id', $cvl_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return $Result->fetchObject() ;
	}


	public function deleted( $cvl_first_name, $cvl_last_name ) {
	/* -------------------
	** Rechercher une Civilité précédemment supprimée et la réactive.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $cvl_first_name Prénom de l'utilisateur à rechercher
	* @param[in] $cvl_last_name Nom de l'utilisateur à rechercher
	*
	* @return Renvoi vrai si l'utilisateur a été réactivé et faux dans le cas contraire.
	*/
		$Request = 'SELECT ' .
		 'cvl_id ' .
		 'FROM cvl_civilities ' .
		 'WHERE cvl_last_name = :cvl_last_name ' .
		 'AND cvl_first_name = :cvl_first_name ' . 
		 'AND cvl_logical_delete = true ';
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':cvl_last_name', $cvl_last_name, PDO::PARAM_STR,
		 35 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':cvl_first_name', $cvl_first_name, PDO::PARAM_STR,
		 25 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject();
		
		if ( $Result->rowCount() == 1 ) {
			$Request = 'UPDATE cvl_civilities ' .
			 'SET cvl_logical_delete = false ' .
			 'WHERE cvl_id = ' . $Occurrence->cvl_id ;

		 
			if ( ! $Result = $this->prepare( $Request ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}

			if ( ! $Result->execute() ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			return true;
		
		}
		
		return false;
	}


	public function delete( $cvl_id, $Type = 0 ) {
	/**
	* Supprime une Civilité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $cvl_id Identifiant de la civilité à supprimer
	* @param[in] $Type Type de suppression à réaliser (0 = logique, 1 = physique)
	*
	* @return Renvoi vrai si l'occurrence a été supprimée
	*/
		$this->beginTransaction();
		
		
		if ( $Type == 0 ) {  // Suppression logique
			if ( ! $Result = $this->prepare( 'UPDATE ' .
			 'cvl_civilities ' .
			 'SET cvl_logical_delete = true ' .
			 'WHERE cvl_id = :cvl_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			if ( ! $Result = $this->prepare( 'DELETE ' .
			 'FROM cvl_civilities ' .
			 'WHERE cvl_id = :cvl_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
		
		$Result->bindParam( ':cvl_id', $cvl_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		if ( ! $Result = $this->prepare( 'UPDATE ' .
		 'idn_identities ' .
		 'SET cvl_id = NULL ' .
		 'WHERE cvl_id = :cvl_id' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':cvl_id', $cvl_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		// Sauvegarde l'ensemble des modifications.
		$this->commit();
 
 		return true;
	}


	public function isAssociated( $cvl_id, $Table ) {
	/**
	* Vérifie si la Civilité est associé.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $cvl_id Identifiant de la civilité à contrôler
	* @param[in] $Table Table à contrôler
	*
	* @return Renvoi vrai si l'occurrence a été supprimée
	*/
		switch( strtoupper( $Table ) ) {
		 default:
		 case 'IDN':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM idn_identities ' .
			 'WHERE cvl_id = :cvl_id ' ;
			break;
		}

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':cvl_id', $cvl_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Value = $Result->fetch() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $Value[ 0 ] == 0 ) return false ;
		
		return true ;
	}


	public function total() {
	/**
	* Calcul le nombre total de Civilités.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @return Renvoi le total d'occurrences trouvé
	*/
		$Request = 'SELECT ' .
		 'count(*) AS total ' .
		 'FROM cvl_civilities ' .
		 'WHERE cvl_logical_delete = 0 ';

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

} // Fin class IICA_Civilities

?>