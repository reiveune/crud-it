<?php
/**
 * Fichier contenant le classe class_pdo
 *
 * @version		$Id: class_pdo.php 2931 2010-05-22 13:06:47Z Stéphane DELAUNE$
 * 
 */
/*
EXDEMPLES D'UTILISATION : 
$pdo = class_pdo::getInstance();

FETCH :
$data = $pdo->fetch("SELECT * FROM affilie WHERE id=".$id);
echo $data["type"];

FETCHALL :
$data = $pdo->fetchAll ( "SELECT * FROM tablecible" );
foreach ( $data as $donnee ) { echo $donnee ["champ2"];}

UPDATE :
$clause_where='id=\''.$id.'\''; //on définie correctement la clause where et on met les données dans une array $cols	
$cols = array('champ3' => $contenuchamp3);
if($pdo->update('tablecible', $cols, $clause_where))
{ $error="L'entrée a été modifiée avec succès";}else{ $error="Une erreur s'est produite";}

INSERT : 
$cols = array('champ1' => $contenuchamp1 , 'champ2' => $contenuchamp2 , 'champ3' => $contenuchamp3);
if($pdo->insert('tablecible', $cols))
{ $error="L'entrée a été ajoutée avec succès";}else{ $error="Une erreur s'est produite";}

DELETE :
$clause_where='id=\''.$id.'\'';
if($pdo->delete('tablecible', $clause_where))
{ $error="L'entrée a été supprimée avec succès";}else{ $error="Une erreur s'est produite";}

*/
/**
 * Classe class_pdo
 * 
 * Cette classe permet d'accéder à une base de données et de manipuler les
 * données avec des fonctions utiles.
 * 
 * @see			PDO
 * @author		Jérémy Romey
 * @author		Adrien Genzor
  * @author		Stéphane Delaune
 * @version		2.0
 * 
 */
class class_pdo
{

    /**
     * Instance de class_pdo
     *
     * @var class_pdo
     */
    protected static $_instance;

    
    /**
     * Instance de PDO
     *
     * @var PDO
     */
    protected $_PDOInstance;

    
    /**
     * Hôte de la base de données
     *
     * @var string
     */
    protected $_dbHost;

    
    /**
     * Nom de la base de données
     *
     * @var string
     */
    protected $_dbBase;

    
    /**
     * Nom d'utilisateur de connexion à la base de données
     *
     * @var string
     */
    protected $_dbUser;

    
    /**
     * Mot de passe de connexion à la base de données
     *
     * @var string
     */
    protected $_dbPass;

    
    /**
     * Mode de retour des jeux de résultats;
     *
     * @var int
     */
    protected $_fetchMode;

    
    /**
     * Tableau contenant les erreurs pdo
     *
     * @var array
     */
    protected $_errorInfo;

    
    /**
     * La requete SQL
     *
     * @var string
     */
    protected $m_sqlRequest;
    
    
    /**
     * Booleen indiquant si la clause where a deja ete definie
     *
     * @var boolean
     */
    protected $m_whereAlreadyDefined;
    
    
    /**
     * 
     * Variables correspondant aux clauses SQL et 
     * contenant la partie de la requete correspondante.
     * 
     */
    private $m_select;
    private $m_from;
    private $m_join;
    private $m_condition;
    private $m_orderBy;
    private $m_limit;
    
    
    
    
    /**
     * Constructeur
     */
    private function __construct ()
    {
        /**
         * Initialisation des propriétés
         */
        $this->_dbHost = DBHOST;
        $this->_dbBase = DBBASE;
        $this->_dbUser = DBUSER;
        $this->_dbPass = DBPASS;
        $this->_fetchMode = PDO::FETCH_ASSOC;
        $this->_errorInfo = array();
        /*
         * Création de l'objet PDO.
         */
        try {
            $this->_PDOInstance = new PDO("mysql:host=" . $this->_dbHost . ";dbname=" . $this->_dbBase, $this->_dbUser, $this->_dbPass);
            $this->_PDOInstance->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Retourne une instance de cette classe.
     *
     * @return class_pdo
     */
    public static function getInstance ()
    {
        if (! isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c();
        }
        return self::$_instance;
    }

    /**
     * Construit la condition d'une requête.
     *
     * @param	mixed $condition
     * @return	string
     */
    protected function _makeCondition ($condition)
    {
        if ($condition === null) {
            return '';
        }
        if (! is_array($condition)) {
            $condition = array(
                $condition
            );
        }
        foreach ($condition as $term) {
            $term = '(' . $term . ')';
        }
        $condition = implode(' AND ', $condition);
        $condition = ' WHERE ' . $condition;
        return $condition;
    }

    /**
     * Prépare une requête SQL à être exécutée par la méthode 
     * PDOStatement::execute(). 
     *
     * @param	string $statement
     * @return	PDOStatement
     */
    public function prepare ($sql)
    {
        // Préparation et exécution de la requête avec les paramètres.
        $stmt = $this->_PDOInstance->prepare($sql);
        if (! $stmt->execute()) {
            return false;
        }
        return $stmt;
    }

    /**
     * Exécute une requête SQL en appelant une seule fonction, 
     * retourne le jeu de résultats (s'il y en a) retourné par la requête en 
     * tant qu'objet PDOStatement. 
     *
     * @param	string $statement
     * @param	mixed bind
     * @return	PDOStatement
     */
    public function query ($sql, $bind = array())
    {
        // Mise en forme des paramètres pour PDO.
        if (is_array($bind)) {
            foreach ($bind as $name => $value) {
                if (! is_int($name) && ! preg_match('/^:/', $name)) {
                    $newName = ":$name";
                    unset($bind[$name]);
                    $bind[$newName] = $value;
                }
            }
        }
        // Les paramètres doivent absolument être un tableau.
        if (! is_array($bind)) {
            $bind = array(
                $bind
            );
        }
        // Préparation et exécution de la requête avec les paramètres.
        $stmt = $this->_PDOInstance->prepare($sql);
        if (! $stmt->execute($bind)) {
            $this->_errorInfo = $stmt->errorInfo();
            return false;
        }
        return $stmt;
    }

    /**
     * Retourne l'identifiant de la dernière ligne insérée, ou la dernière 
     * valeur d'une séquence d'objets, dépendamment du driver utilisé
     *
     * @param	string $name Nom de la séquence d'objets depuis laquelle l'identifiant 
     * doit être retourné. 
     * @return	string
     */
    public function getLastInsertId ($name = null)
    {
        return $this->_PDOInstance->lastInsertId($name);
    }

    /**
     * Alias de getLastInsertId
     *
     * @param	string $name Nom de la séquence d'objets depuis laquelle l'identifiant 
     * doit être retourné. 
     * @return	string
     */
    public function lastInsertId ($name = null)
    {
        return $this->_PDOInstance->lastInsertId($name);
    }

    /**
     * Récupère une ligne depuis un jeu de résultats associé à l'objet PDOStatement. 
     * Le paramètre fetchStyle détermine la façon dont PDO retourne la ligne.
     * 
     * La valeur retournée par cette fonction en cas de succès dépend du type 
     * récupéré. Dans tous les cas, FALSE est retourné si une erreur survient. 
     * 
     * Voir documentation officielle de PDO.
     * 
     * @param	string $statement
     * @return	array
     */
    public function fetch ($statement, $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $stmt = $this->query($statement, $bind);
        if (! $stmt) {
            return false;
        } else {
            $result = $stmt->fetch(PDO::FETCH_BOTH);
        }
        return $result;
    }

    /**
     * Retourne un tableau contenant toutes les lignes du jeu d'enregistrements.
     * 
     * fetchAll() retourne un tableau contenant toutes les lignes du jeu 
     * d'enregistrements. Le tableau représente chaque ligne comme soit un 
     * tableau de valeurs des colonnes, soit un objet avec des propriétés 
     * correspondant à chaque nom de colonne. 
     * 
     * @param	string $sql 
     * @param	mixed $bind
     * @param	mixed $fetchMode
     * @return	array
     */
    public function fetchAll ($sql, $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $stmt = $this->query($sql, $bind);
        if (! $stmt) {
            $result = false;
        } else {
            $result = $stmt->fetchAll($fetchMode);
        }
        return $result;
    }

    /**
     * Retourne une colonne depuis la ligne suivante d'un jeu de résultats ou 
     * FALSE s'il n'y a plus de ligne. 
     *
     * @param	string $sql
     * @param	mixed $bind
     * @param	int $columnNumber
     * @return	string
     */
    public function fetchColumn ($sql, $bind = array(), $columnNumber = null)
    {
        $stmt = $this->query($sql, $bind);
        $result = $stmt->fetchColumn($columnNumber);
        return $result;
    }

    /**
     * Insert un nouvel enregistrement dans la table $table avec les valeurs de
     * $values.
     * 
     * $values est un tableau où les clés sont le nom des colones de la table et
     * les valeurs, les valeurs à insérer.
     * 
     * La méthode retourne le nombre d'enregistement affectée par la requête.
     *
     * @param	string $table Nom de la table
     * @param	array bind Valeurs à insérer
     * @return	int
     */
    public function insert ($table, array $bind)
    {
        /**
         * Construction des valeurs à insérer
         */
        $arrayCols = array();
        $arrayVals = array();
        // Construction de la requête
        foreach ($bind as $c => $v) {
            $arrayCols[] = $c;
            $arrayVals[] = '?';
        }
        /**
         * Construction de la requête
         */
        $cols = implode(',', $arrayCols);
        $vals = implode(',', $arrayVals);
        $sql = "INSERT INTO `$table` ($cols) VALUES ($vals)";
        /**
         * Exécution de la requête et retour du nombre de lignes affectées
         */
        if (($stmt = $this->query($sql, array_values($bind)))) {
            $result = $stmt->rowCount();
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * Met à jour un ou plusieurs enregistrement de la table $table avec les valeurs
     * de $values.
     * 
     * $values est un tableau où les clés sont le nom des colones de la table et
     * les valeurs, les valeurs à insérer.
     * 
     * La méthode retourne le nombre d'enregistement affecté par la requête.
     *
     * @param	string $table Nom de la table
     * @param	array $bind Valeurs à mettre à jour.
     * @param	string $condition Clause WHERE de la requête UPDATE
     * @return	int
     */
    public function update ($table, array $bind, $condition = null)
    {
        /**
         * Construction "col = ?"
         */
        $pairs = array();
        foreach ($bind as $key => $vals) {
            $pairs[] = $key . " = ?";
        }
        /**
         * Contruction de la clause WHERE
         */
        $condition = $this->_makeCondition($condition);
        /**
         * Construction de la requête
         */
        $sql = "UPDATE " . $table . " SET " . implode(', ', $pairs) . $condition;
        /**
         * Execution de la requête et retour du nombre de ligne affectées
         */
        $stmt = $this->query($sql, array_values($bind));
        $result = $stmt->rowCount();
        return $result;
    }

    /**
     * Supprime un ou plusieurs enregistrement de la table $table selon les
     * conditions $conditions
     * 
     * La méthode retourne le nombre d'enregistrement affecté par la requête.
     *
     * @param	string $table Nom de la table
     * @param	mixed $condition Clause WHERE de la requête DELETE.
     * @return	int
     */
    public function delete ($table, $condition = null)
    {
        /**
         * Construction de la clause WHERE
         */
        $condition = $this->_makeCondition($condition);
        /**
         * Construction de la requête
         */
        $sql = "DELETE FROM `" . $table . "`" . $condition;
        /**
         * Execution de la requête et retour du nombre de ligne affectées
         */
        $stmt = $this->query($sql);
        $result = $stmt->rowCount();
        return $result;
    }

    /**
     * Retourne un tableau contenant les enregistrements des colones $cols selon
     * les conditions $conditions.
     *
     * @param string $table
     * @param array $cols
     * @param string $condition
     * @param string $order
     * @param string $by
     * @return array
     */
    public function select ($table, array $cols, $condition = null, $order = null, $by = null, $limit = null)
    {
        $condition = ($condition === null) ? '' : " WHERE " . $condition;
        $order = ($order === null) ? '' : " ORDER BY " . $order;
        $by = ($by === null) ? '' : '' . ' ' . $by;
        $limit = ($limit === null) ? '' : '' . ' LIMIT ' . $limit;
        return $this->fetchAll("SELECT " . implode(',', $cols) . " FROM " . $table . $condition . $order . $by . $limit);
    }

    /**
     * Retourne un tableau contenant les enregistrements de toutes les colones
     * selon les conditions $conditions.
     *
     * @param string $table
     * @param string $condition
     * @param string $order
     * @param string $by
     * @return array
     */
    public function selectAll ($table, $condition = null, $order = null, $by = null, $limit = null)
    {
        $condition = ($condition === null) ? '' : " WHERE " . $condition;
        $order = ($order === null) ? '' : " ORDER BY " . $order;
        $by = ($by === null) ? '' : '' . ' ' . $by;
        $limit = ($limit === null) ? '' : '' . ' LIMIT ' . $limit;
        return $this->fetchAll("SELECT* FROM " . $table . $condition . $order . $by . $limit);
    }

    /**
     * Retourne le nombre d'enregistrements quelque soit la requête $statement.
     *
     * @param	string $statement
     * @param	mixed $bind
     * @return	int
     */
    public function numRows ($statement, $bind = array())
    {
        if (preg_match('`^SELECT`', $statement)) {
            $result = $this->fetchAll($statement, $bind);
            return count($result);
        }
        $stmt = $this->query($statement, $bind);
        $result = $stmt->rowCount();
        return $result;
    }

    /**
     * Alias de numRows()
     *
     * @param	string $statement
     * @param	mixed $bind
     * @return	int
     */
    public function rowCount ($statement, $bind = array())
    {
        return $this->numRows($statement, $bind);
    }

    /**
     * Définie le mode de résultat
     *
     * @param	int $fetchMode
     * @return	class_pdo
     */
    public function setFetchMode ($fetchMode)
    {
        $this->_fetchMode = $fetchMode;
        return $this;
    }

    /**
     * retourne un tableau contenant des informations sur l'erreur survenu 
     * lors de la dernière opération exécutée par ce gestionnaire de base de 
     * données. Le tableau contient les champs suivants : 
     * 
     * 0 code erreur SQLSTATE (un identifiant alphanumérique de cinq caractères 
     * 	 défini dans le standard ANSI SQL). 
     * 1 Code erreur spécifique au driver. 
     * 2 Message d'erreur spécifique au driver. 
     *
     * @param	boolean $string
     * @return	array|string
     */
    public function getErrors ($string = true, $pdo = false)
    {
        if ($pdo) {
            $error = $this->_PDOInstance->errorInfo();
        } else {
            $error = $this->_errorInfo;
        }
        if ($string) {
            return print_r($error, true);
        }
        return $error;
    }

    /**
     * Ajout des slash à value.
     *
     * @param mixed $value
     * @return unknown
     */
    public function quote ($value)
    {
        if (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }
        return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
    }
    
    
	/**
     * Ajoute un SELECT à la requete SQL
     * 
     * @param $tables
     */
    public function AddSelect() {
    	$args = func_get_args();
		$nbrArgs = func_num_args();
		
		$this->m_sqlRequest .= (' SELECT '.$args[0]);
		
    	for ($i = 1 ; $i < $nbrArgs ; $i++)
   			$this->m_sqlRequest .= (','.$args[$i]);
    }
    
    
    /**
     * Ajoute un SELECT à la requete SQL
     * 
     * @param $tables
     */
    public function AddFrom() {
    	$args = func_get_args();
		$nbrArgs = func_num_args();
		
		$this->m_sqlRequest .= (' FROM '.$args[0]);
		
    	for ($i = 1 ; $i < $nbrArgs ; $i++)
   			$this->m_sqlRequest .= (', '.$args[$i]);
    }
    
    
    /**
     * Ajoute une condition à la requete SQL
     * 
     * @param $condition
     */
    public function AddCondition($condition) {
    	
    		// Si WHERE n'a pas encore été utilisé
    	if(!$this->m_whereAlreadyDefined) {
    		$this->m_sqlRequest .= (' WHERE '.$condition);
    		$this->m_whereAlreadyDefined = TRUE;
    	}
    	
    		// WHERE défini -> condition supplémentaire
    	else 
    		$this->m_sqlRequest .= (' AND '.$condition);
    }
    
    
    /**
     * Ajoute une jointure à la requete SQL
     * 
     * @param $table La table à joindre
     * @param $on La condition de jointure
     */
    public function AddJoin($table, $on) {
		$this->m_sqlRequest .= (' JOIN '.$table.' ON '.$on);
    }
    
    
    /**
     * Ajoute un OrderBy à la requete SQL
     * 
     * @param $by Le critère de tri
     * @param $order Le sens du tri
     */
    public function AddOrderBy($by, $order="DESC") {
		$this->m_sqlRequest .= (' ORDER BY '.$by.' '.$order);
    }
    
    
	/**
     * Ajoute une limite de résultat à la requete SQL
     * 
     * @param $limit La limite du nombre de résultats à retourner
     */
    public function AddLimit($limit) {
		$this->m_sqlRequest .= (' LIMIT '.$limit);
    }
    
    
	/**
     * Retourne la requete SQL
     * 
     * @return string La requête SQL
     */
    public function GetQuery() {
    	return $this->m_sqlRequest;
    }
    
    
	/**
     * Execute la requete SQL
     * 
     * @param $bind Les flags PDOStatement
     * @return Résultat de la requete
     */
    public function ExecuteQuery($bind = array()) {
    	$this->CreateQuery();						
    	return $this->query( $this->m_sqlRequest, $bind);
    }
    
    
	/**
     * Supprime la requete SQL
     */
    public function DeleteQuery() {
    	$this->m_sqlRequest = '';
		$this->m_select = '';
    	$this->m_from = ''; 
    	$this->m_join = ''; 
    	$this->m_condition = ''; 
    	$this->m_orderBy = ''; 
    	$this->m_limit = '';
    }
    
    
    /**
     * Cree la requete SQL correspondant aux clauses renseignees
     */
    private function CreateQuery() {
    	$this->m_sqlRequest = 	 $this->m_select 
    							.$this->m_from 
    							.$this->m_join 
    							.$this->m_condition 
    							.$this->m_orderBy 
    							.$this->m_limit;
    }
}
?>