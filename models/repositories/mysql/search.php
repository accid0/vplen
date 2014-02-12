<?php
namespace Accido\Models\Repositories\Mysql;
use Accido\Model;
use Accido\Event;
use Accido\View;
use Accido\Controller;
use Accido\Models\DB;
use Accido\Models\Request;
defined('CORE_ROOT') or die('No direct script access.');
/**
 * Search 
 * 
 * @uses Model
 * @package Repository
 * @version 1.0
 * @copyright Sat, 26 Oct 2013 07:41:47 +0300 Accido
 * @author Andrew Scherbakov <kontakt.asch@gmail.com> 
 * @license PHP Version 5.2 {@link http://www.php.net/license/}
 */
class Search extends Model {

  /**
   * OPT_TABLE
   *
   * @const string
   */
  const OPT_TABLE                     = 'table';

  /**
   * vars 
   * 
   * @var array
   * @access protected
   */
  protected $vars                    = array(
    self::OPT_EVENTS                  => array(
      'controllers_install'           => Event::ATTR_NORMAL_EVENT_PRIORITY,
      'controllers_search_main'       => Event::ATTR_LOW_EVENT_PRIORITY,
    ),
    self::OPT_SQL_INIT_CODE           => array(
      /**
       * use {:prefix} construction for prefix table 
       * use {:charset} construction for charachter table
       */
      DB::ATTR_MYSQL                  => '',
    ),
    /**
     * table name without db prefix
     */
    self::OPT_TABLE                   => 'searches',
  );

  // protected init() {{{ 
  /**
   * init
   * 
   * @access protected
   * @return void
   */
  protected function init(){
  }
  // }}}

  // public event_controllers_install(View view) {{{ 
  /**
   * event_controllers_install
   * 
   * @param View $view 
   * @access public
   * @return void
   */
  public function event_controllers_install( View $view ){
    $queries = $view->queries;
    $queries[] = (array)$this->get( self::OPT_SQL_INIT_CODE );
    $view->queries = $queries;
  }
  // }}}

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
   */
  public function event_controllers_search_main(View $view, Controller $ctrl){
    $D        = $view->D;
    $C        = $view->C;
    $page     = $view->page;

    $db       = $this->register_model('DB')->get(DB::OPT_DB);
    $table    = $this->get(self::OPT_TABLE);
    $helper   = $this->register_model('Helper');
    $prefix   = $helper->get(DB::OPT_PREFIX);

    if( isset($ctrl->request['parameter.saved']) && !empty($ctrl->request['parameter.saved']) ) {
      $tmp	  = $ctrl->request->get('parameter.saved');
      $select = 
<<<EOQ
SELECT search_url 
  FROM {$prefix}{$table} 
  WHERE user_id=?:user AND search_key=?:search LIMIT 1
EOQ;
      $search_url = $db->query($select, array('user' => $page->user->id, 'search' => $tmp), 'el');
      $ctrl->request->redirect($C->SITE_URL . $search_url, false, false);
    }
    $D->can_be_saved	    = TRUE;
    $D->search_saved	    = FALSE;
    $D->saved_searches	  = $page->user->get_saved_searches();
    $search_key	= md5($D->tab."\n".$D->search_string."\n".serialize($D->ptypes_choosen)."\n".$D->puser."\n".$D->pgroup."\n".serialize($D->pdate1)."\n".serialize($D->pdate2));
    foreach( $D->saved_searches as $k=>$v ){
      if($v->search_key == $search_key){
        $D->search_saved  = $v->id;
      }
    }
    $tmp_url              = implode('/', $ctrl->request->get(Request::OPT_REQUEST_URI));
    $tmp_url              = preg_replace('/(^|\/)pg\:[^\/]*/iu', '', $tmp_url);
    $tmp_url              = preg_replace('/(^|\/)from\:[^\/]*/iu', '', $tmp_url);
    $tmp_url              = preg_replace('/(^|\/)r\:[^\/]*/iu', '', $tmp_url);
    $tmp_url              = preg_replace('/(^|\/)savesearch\:[^\/]*/iu', '', $tmp_url);
    $tmp_url              = preg_replace('/\/+/', '/', $tmp_url);
    $tmp_url              = trim($tmp_url, '/');
    $D->ajax_url          = str_replace('/search/', '/search/from:ajax/', $tmp_url);
    if( isset($ctrl->request['parameter.from']) 
      && 'ajax' === $ctrl->request['parameter.from']  
      && isset($ctrl->request['post.savesearch']) ) {
      if( 'on' === $ctrl->request['post.savesearch'] && !$D->search_saved ) {
        $insert     =
<<<EOQ
INSERT INTO {$prefix}{$table} 
  SET user_id=?:user
      , search_key=?:key
      , search_string=?:search
      , search_url=?:url
      , added_date=?:time
      , total_hits=1
      , last_results=?i:result
EOQ;
        $id = $db->query($insert, array(
          'user'        => $page->user->id,
          'key'         => $search_key,
          'search'      => $D->search_string,
          'url'         => $tmp_url,
          'time'        => time(),
          'result'      => $D->num_results,
        ), 'id');
        die('OK:'.$id);
      }
      elseif( 'off' === $ctrl->request['post.savesearch'] && $D->search_saved ) {
        $db->query('DELETE FROM searches WHERE id=?:key LIMIT 1', array('key' => $D->search_saved), 'ar');
        die('OK:0');
      }
      die('ERROR');
    }
  }

}
