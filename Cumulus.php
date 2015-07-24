<?php

require 'CumulusInterface.php';

/**
 * Bibliothèque pour le stockage de fichiers ("cloud") - API : fournit les
 * méthodes de haut niveau correspondant aux cas d'utilisation, et transmets
 * les appels à l'adapteur choisi dans la config
 */
class Cumulus implements CumulusInterface {

	/** Config en JSON */
	protected $config = array();
	public static $CHEMIN_CONFIG = "config/config.json";

	/** Implémentation de la lib par un adapteur */
	protected $adapter;

	public function __construct() {
		// config
		if (file_exists(self::$CHEMIN_CONFIG)) {
			$this->config = json_decode(file_get_contents(self::$CHEMIN_CONFIG), true);
		} else {
			throw new Exception("Le fichier " . self::$CHEMIN_CONFIG . " n'existe pas");
		}

		// adapteur
		$adapterName = $this->config['adapter'];
		$adapterDir = strtolower($adapterName);
		$adapterPath = 'adapters/' . $adapterDir . '/' . $adapterName . '.php';
		if (strpos($adapterName, "..") != false || $adapterName == '' || ! file_exists($adapterPath)) {
			throw new Exception ("L'adapteur " . $adapterPath . " n'existe pas");
		}
		require $adapterPath;
		// on passe la config à l'adapteur - à lui de stocker ses paramètres
		// dans un endroit correct (adapters.nomdeladapteur par exemple)
		$this->adapter = new $adapterName($this->config);
	}

	/**
	 * Si $inverse est true, indique à l'adapteur que les critères de recherche
	 * devront être inversés
	 */
	public function setInverseCriteria($inverse) {
		$this->adapter->setInverseCriteria($inverse);
	}

	/**
	 * Retourne un fichier à partir de sa clef et son chemin
	 */
	public function getByKey($path, $key) {
		return $this->adapter->getByKey($path, $key);
	}

	/**
	 * Retourne une liste de fichiers dont les noms correspondent à $name; si
	 * $trict est true, compare avec un "=" sinon avec un "LIKE"
	 */
	public function getByName($name, $strict=false) {
		return $this->adapter->getByName($name, $strict);
	}

	/**
	 * Retourne une liste de fichiers se trouvant dans le répertoire $path; si
	 * $recursive est true, cherchera dans tous les sous-répertoires
	 */
	public function getByPath($path, $recursive=false) {
		return $this->adapter->getByPath($path, $recursive);
	}

	/**
	 * Retourne une liste de fichiers dont les mots-clefs sont $keywords
	 * (séparés par des virgules ); si $mode est "OR", un "OU" sera appliqué
	 * entre les mots-clefs, sinon un "ET"; si un mot-clef est préfixé par "!",
	 * on cherchera les fichiers n'ayant pas ce mot-clef
	 */
	public function getByKeywords($keywords, $mode="AND") {
		return $this->adapter->getByKeywords($keywords, $mode);
	}

	/**
	 * Retourne une liste de fichiers appartenant aux groupes $groups
	 * (séparés par des virgules ); si $mode est "OR", un "OU" sera appliqué
	 * entre les groupes, sinon un "ET"; si un groupe est préfixé par "!", on
	 * cherchera les fichiers n'appartenant pas à ce groupe
	 * @TODO gérer les droits
	 */
	public function getByGroups($groups, $mode="AND") {
		return $this->adapter->getByGroups($groups, $mode);
	}

	/**
	 * Retourne une liste de fichiers appartenant à l'utilisateur $user
	 * @TODO gérer les droits
	 */
	public function getByUser($user) {
		return $this->adapter->getByUser($user);
	}

	/**
	 * Retourne une liste de fichiers dont le type MIME est $mimetype
	 */
	public function getByMimetype($mimetype) {
		return $this->adapter->getByMimetype($mimetype);
	}

	/**
	 * Retourne une liste de fichiers dont la licence est $license
	 */
	public function getByLicense($license) {
		return $this->adapter->getByLicense($license);
	}

	/**
	 * Retourne une liste de fichiers en fonction de leur date (@TODO de création
	 * ou de modification ?) : si $date1 et $date2 sont spécifiées, renverra les
	 * fichiers dont la date se trouve entre les deux; sinon, comparera à $date1
	 * en fonction de $operator ("=", "<" ou ">")
	 */
	public function getByDate($dateColumn, $date1, $date2, $operator="=") {
		return $this->adapter->getByDate($dateColumn, $date1, $date2, $operator);
	}

	/**
	 * Recherche avancée - retourne une liste de fichiers correspondant aux
	 * critères de recherche contenus dans $searchParams (paires de
	 * clefs / valeurs); le critère "mode" peut valoir "OR" (par défaut) ou
	 * "AND"
	 */
	public function search($searchParams=array()) {
		return $this->adapter->search($searchParams);
	}

	/**
	 * Ajoute le fichier $file au stock, dans le chemin $path, avec la clef $key,
	 * les mots-clefs $keywords (séparés par des virgules) et les métadonnées
	 * $meta (portion de JSON libre); si $key est null, une clef sera attribuée
	 */
	public function addFile($file, $path, $key=null, $keywords=null, $groups=null, $license=null, $meta=null) {
		return $this->adapter->addFile($file, $path, $key, $keywords, $groups, $license, $meta);
	}

	/**
	 * Remplace le contenu (si $file est spécifié) et / ou les métadonnées du
	 * fichier $key situé dans $path
	 */
	public function updateByKey($file, $path, $key, $keywords=null, $meta=null) {
		return $this->adapter->updateByKey($file, $path, $key, $keywords, $meta);
	}

	/**
	 * Supprime le fichier $key situé dans $path; si $keepFile est true, ne
	 * supprime que la référence mais conserve le fichier dans le stockage
	 */
	public function deleteByKey($path, $key, $keepFile=false) {
		return $this->adapter->deleteByKey($path, $key, $keepFile);
	}

	/**
	 * Retourne les attributs (métadonnées) du fichier $key situé dans $path,
	 * mais pas le fichier lui-même
	 */
	public function getAttributesByKey($path, $key) {
		return $this->adapter->getAttributesByKey($path, $key);
	}
}