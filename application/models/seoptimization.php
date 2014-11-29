<?php

/**
 * Description of seOptimization
 *  Model permettant de g�rer le renseignement des balises <meta> d'une "page" du site
 *
 * @author alto971
 */
class seOptimization extends DATA_Model {
	/*	 * *** les constantes pour la page d'accueil *** */

	public $metaHome = array('desc' => 'Tutoriels et formations sur unity3d, libgdx, unreal engine et slick 2d.', 'keywords' => 'tutoriel, formation, unity3d, libgdx, unreal engine, créer jeux vidéos, jeu vidéo');
	public $metaTuto = array('desc' => 'Retrouvez tous les tutoriels sur la création de jeux vidéos avec libgdx, unity, unreal engine et slick 2d', 'keywords' => 'tutoriels libgdx, tutoriels unity, tuytoriels slick 2d');
	public $metaArticles = array('desc' => 'Suivez les dernières nouvelles sur unity, libgdx, unreal engine et slick 2d', 'keywords' => 'articles, libgdx, unity, unreal engine, slick 2d');
	public $metaVideos = array('desc' => 'meta desc videos', 'keywords' => 'meta keywords Vidéo');
	public $metaForum = array('desc' => 'Participez au forum, apportez votre contribution et recevez de l\'aide pour la création de vos jeux vidéos', 'keywords' => 'forum, aide, création jeux vidéos');

	public function getTableName() {
		return '';
	}

	/** on r�cup�re la table selon nomController  (ex ...tutorials)   ** */
	public function getName($nomController) {

		/*		 * ** cas des �l�ments comme articles ou tutoriels ** */
		return $nomController;
		/*		 * ** cas des cat�gories    *** */
		//TODO
	}

	/*
	 * 	Permet de r�cup�rer les deux champs utiles aux balises
	 */

	public function getData($where, $nomTable) {
		$query = $this->db->select('metaDesc,metaKeywords')
						->where($where)
						->from($nomTable)->get();

		if ($query->num_rows() == 1) {
			$res = $query->result();
			$res = $res[0];
			return array('desc' => $res->metaDesc, 'keywords' => $res->metaKeywords);
		}
		return false;
	}

	/*
	 * 	Permet de r�cup�rer le contenu des balises meta de type "description" et "keywords"
	 *    Ce Model travaille avec plusieurs tables (articles, tutoriels...)
	 */

	public function getMeta($nomController = null, $cat = null, $alias = null) {

		/*		 * ** cas des pages de l'accueil **** */
		if (!$nomController) {
			return $this->metaHome;
		}
		/*		 * ** cas des pages de listage des cat�gories (pages g�n�rales)**** */ 
		else if (!$cat) {
			if ($nomController == 'tutorials') {
				return $this->metaTuto;
			} else if ($nomController == 'articles') {
				return $this->metaArticles;
			} else if ($nomController == 'videos') {
				return $this->metaVideos;
			} else if ($nomController == 'forum') {
				return $this->metaForum;
			}
		}
		/*  cas des pages de listage des �l�ments par cat�gories 
		 * (pages g�n�rales mais devant �tre personnalis�es en fonction de la cat�gorie en BDD !!!)
		 */ 
		 else if (!$alias) {
			if ($nomController == 'tutorials') {
				return $this->getData(array('alias' => $cat), 'tuto_categories');
			} else if ($nomController == 'articles') {
				return $this->getData(array('alias' => $cat), $nomController . '_categories');
			} else if ($nomController == 'forum') {
				return $this->getData(array('alias' => $cat), 'topic_categories');
			}
		}
		/*		 * * cas des pages des �l�ments devant �tre personnalis�s en bdd ** */ 
		else {
			if ($nomController == 'forum') {
				return $this->getData(array('alias' => $alias), 'topics');
			} else {
				return $this->getData(array('alias' => $alias), $nomController);
			}
		}
	}

}

?>