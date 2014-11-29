<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	http://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There area two reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router what URI segments to use if those provided
  | in the URL cannot be matched to a valid route.
  |
 */
$route['default_controller'] = "home";
$route['404_override'] = '';

/*
 * 		------------  Custom Routes ------------------------
 */

/* * * on pr�serve les routes g�n�rales *** */
$route['tutorials/viewCategories'] = "tutorials/viewCategories";
$route['tutorials/categories'] = "tutorials/viewCategories";
$route['tutorials/categories/:any'] = "tutorials/viewByCategory/$1";
$route['tutorials/viewByCategory/:any'] = "tutorials/viewByCategory/$1";
$route['articles/viewCategories'] = "articles/viewCategories";
$route['articles/categories'] = "articles/viewCategories";
$route['articles/viewByCategory/:any'] = "articles/viewByCategory/$1";
$route['articles/categories/:any'] = "articles/viewByCategory/$1";
$route['tutorials/ajaxViewByCategory/:any'] = "tutorials/ajaxViewByCategory/$1";
$route['articles/ajaxViewByCategory/:any'] = "articles/ajaxViewByCategory/$1";
$route['tutorials/ajaxViewCategories/:any'] = "tutorials/ajaxViewCategories/$1";
$route['articles/ajaxViewCategories/:any'] = "articles/ajaxViewCategories/$1";
$route['articles/ajaxMostPopularList/:any'] = "articles/ajaxMostPopularList/$1";
$route['articles/ajaxOnTopList/:any'] = "articles/ajaxOnTopList/$1";

//Préservation des requêtes ajax

$route['forum/ajaxViewTopic/(:any)'] = "forum/ajaxViewTopic/$1";
$route['forum/ajaxHotTopics/(:any)'] = "forum/ajaxHotTopics/$1";
$route['forum/newTopic'] = 'forum/newTopic';
$route['forum/newTopic/(:any)'] = 'forum/newTopic/$1';
$route['forum/viewMessage/(:any)'] = "forum/viewMessage/$1";
$route['forum/viewTopicsByCategory/(:any)'] = "forum/viewTopicsByCategory/$1";
$route['forum/ajaxViewTopicsByCategory/(:any)'] = "forum/ajaxViewTopicsByCategory/$1";
$route['forum/listCategories'] = "forum/listCategories";
$route['forum/addMessage'] = "forum/addMessage";
$route['forum/editMessage'] = "forum/editMessage";
$route['forum/editMessage/(:any)'] = "forum/editMessage/$1";
$route['forum/quoteMessage'] = "forum/quoteMessage";
$route['forum/closeTopic'] = "forum/closeTopic";
$route['forum/closeTopic/(:any)'] = "forum/closeTopic/$1";
$route['forum/openTopic'] = "forum/openTopic";
$route['forum/openTopic/(:any)'] = "forum/openTopic/$1";
$route['forum/quoteMessage/(:any)'] = "forum/quoteMessage/$1";
$route['forum/quoteMessage'] = "forum/quoteMessage";
$route['forum/hideMessage/(:any)'] = "forum/hideMessage/$1";
$route['forum/hideMessage'] = "forum/hideMessage";
$route['forum/showMessage/(:any)'] = "forum/showMessage/$1";
$route['forum/showMessage'] = "forum/showMessage";
$route['forum/viewTopic/(:any)'] = "forum/viewTopic/$1";
$route['forum/(:any)/(:any)'] = "forum/index/$1/$2";
$route['forum/(:any)'] = "forum/viewTopicsByCategory/$1";


/* * ** pour les routes de types : tutorials/catTuto/aliasTuto ** */

$route['tutorials/viewPage/(:any)'] = "tutorials/viewPage/$1";
$route['tutorials/(:any)'] = "tutorials/index/$1";
$route['videos/viewChapter/:any'] = "videos/viewChapter/$1";
$route['videos/(:any)'] = "videos/index/$1";

/* * ** pour les routes de types : articles/catArticle/aliasArticles ** */
$route['articles/(:any)'] = "articles/index/$1";


/* End of file routes.php */
/* Location: ./application/config/routes.php */