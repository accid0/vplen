<?php
namespace Accido\Models;
use Accido\Model;
use Accido\Event;
use Accido\View;
use Accido\Controller;
use Accido\Models\DB;
defined('CORE_ROOT') or die('No direct script access.');
/**
 * Class: Search
 *
 * @package Search
 * @subpackage Model
 *
 * 
 * @see Model
 * @author andrew scherbakov <kontakt.asch@gmail.com>
 * @version $id$
 * @copyright © 2013 andrew scherbakov
 * @license GNU GPL 3.0 http://www.gnu.org/licenses/gpl.txt
 */
class Search extends Model{

  /**
   * vars
   *
   * @var array
   */
  protected $vars                           = array(
    self::OPT_EVENTS                        => array(
      'controllers_search_main'             => Event::ATTR_HIGH_EVENT_PRIORITY,     
    ),
  );

  /**
   * init
   *
   * @uses
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   * @return
   */
  protected function init(){
  }

  /**
   * event_controllers_search_main
   *
   * @param View $view
   * @param Controller $ctrl
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   * @return void
   */
  public function event_controllers_search_main(View $view, Controller $ctrl){
    global $C, $D, $page;
    
    $view->bind('C', $C);
    $view->bind('D', $D);
    $view->bind('page', $page);
    $utf8   = $this->register_model('Utf8');
    $helper = $this->register_model('Helper');

    if( !$page->network->id ) {
      $page->redirect('home');
    }
    elseif(!$page->user->is_logged){
      //$page->redirect('signin');
    }

    $D->can_be_saved	  = FALSE;
    $D->error	          = FALSE;
    $D->errmsg	        = '';
    $D->num_results	    = 0;
    $D->num_pages	      = 0;
    $D->num_per_page	  = 0;
    $D->pg	            = 1;
    $D->posts_html	    = '';
    $D->users_html	    = '';
    $D->groups_html	    = '';
    $D->tags_html       = '';
    $D->ptypes_choosen  = array();
    $D->puser           = '';
    $D->pgroup          = '';
    $D->pdate1          = '';
    $D->pdate2          = '';
    $D->form_user	      = '';
    $D->form_group	    = '';
    $D->saved_searches	= array();
    $D->start_from      = 0;
    $D->lats_post_id    = 0;
    
    if( isset($ctrl->request['parameter.posttag']) && !empty($ctrl->request['parameter.posttag']) ) { 
      $tmp	= str_replace('/', '', $utf8->urldecode($ctrl->request['parameter.posttag']));
      $ctrl->request->redirect($C->SITE_URL.'search/tab:tags/s:' . trim($tmp), false, false);
    }

    $page->load_langfile('inside/global.php');
	  $page->load_langfile('inside/search.php');
		
	  $tabs	= array('posts', 'users', 'groups', 'tags');
	  $D->tab	= 'posts';
    if( isset($ctrl->request['parameter.tab']) && in_array($ctrl->request['parameter.tab'], $tabs) ) {
      $D->tab	= $ctrl->request['parameter.tab'];
    }
    if( isset($ctrl->request['post.lookin']) && !empty($ctrl->request['post.lookin']) && in_array($ctrl->request['post.lookin'], $tabs) ){
      $D->tab	= trim($ctrl->request['post.lookin']);
    }
    
    $D->search_string = '';
    
    if( isset($ctrl->request['parameter.usertag']) && !empty($ctrl->request['parameter.usertag']) ) {
      $D->tab	= 'users';
      $D->search_string = str_replace('/', '', urldecode(trim($ctrl->request->get('parameter.usertag', FILTER_SANITIZE_ENCODED))));
    }elseif( isset($ctrl->request['post.lookfor']) && !empty($ctrl->request['post.lookfor']) ) {
      $D->search_string = str_replace('/', '', $ctrl->request->get('post.lookfor'));
    }elseif( isset($ctrl->request['parameter.s']) && !empty($ctrl->request['parameter.s']) ){
      $D->search_string	= urldecode(trim($ctrl->request->get('parameter.s', FILTER_SANITIZE_ENCODED)));
      $D->search_string	= preg_replace('/\s+/us', ' ', $D->search_string);
    }

    if( '' === $D->tab){
			$D->tab = 'posts';
			$D->error	= TRUE;
			$D->errmsg	= $this->lang('srch_noresult_posts_def');
	  }

    $D->page_title = $page->lang('srch_title_'.$D->tab, array('#SITE_TITLE#'=>$C->SITE_TITLE));
    $D->search_title = $page->lang( (empty($D->search_string)?'srch_title2_':'srch_title3_').$D->tab, array('#STRING#'=>htmlspecialchars(str_cut($D->search_string,30))));
    
  }

}
