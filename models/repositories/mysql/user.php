<?php
namespace Accido\Models\Repositories\Mysql;
use Accido\Model;
use Accido\Event;
use Accido\View;
use Accido\Controller;
use Accido\Models\DB;
defined('CORE_ROOT') or die('No direct script access.');
/**
 * User 
 * 
 * @uses Model
 * @package Repository
 * @version 1.0
 * @copyright Sat, 26 Oct 2013 07:41:47 +0300 Accido
 * @author Andrew Scherbakov <kontakt.asch@gmail.com> 
 * @license PHP Version 5.2 {@link http://www.php.net/license/}
 */
class User extends Model {

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
    self::OPT_TABLE                   => 'users',
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
   *
   * @return void
   */
  public function event_controllers_search_main( View $view, Controller $ctrl ){
    $D      = $view->D;
    $C      = $view->C;
    $page   = $view->page;

    $db     = $this->register_model('DB')->get(DB::OPT_DB);
    $table  = $this->get(self::OPT_TABLE);
    $helper = $this->register_model('Helper');
    $prefix = $helper->get(DB::OPT_PREFIX);
    if( $D->tab == 'users' )
    { 
      $uids	= array();
      $D->if_follow_user = array();
      if( !empty($D->search_string) ){
        $tmp	            = str_replace(array('%','_'), array('\%','\_'), $D->search_string);
        $select           =
<<<EOQ
SELECT COUNT(*) 
  FROM {$prefix}{$table} u 
  WHERE u.username LIKE "%?e:name%"
    OR u.fullname LIKE "%?e:name%"
    OR tags REGEXP "(^|\,| )?e:name($|\,)"
EOQ;
        $D->num_results   = $db->query($select, array('name' => $tmp), 'el');
        $D->num_pages	    = ceil(intval($D->num_results) / $C->PAGING_NUM_USERS);
        $D->pg	          = isset($ctrl->request['parameter.pg']) ? $ctrl->request->get('parameter.pg', FILTER_SANITIZE_NUMBER_INT) : 1;
        $D->pg	          = min($D->pg, $D->num_pages);
        $D->pg	          = max($D->pg, 1);
        $start            = ($D->pg - 1) * $C->PAGING_NUM_USERS;
        
        $select           =
<<<EOQ
SELECT GROUP_CONCAT( CAST( uf.whom AS CHAR ) SEPARATOR ',' ) whom
    , u.id
    , u.username
    , u.fullname
    , u.position
    , u.num_followers
    , u.num_posts
    , u.avatar
  FROM {$prefix}{$table} u
  LEFT JOIN {$prefix}users_followed uf ON uf.whom = u.id AND uf.who = ?:user
  WHERE u.username LIKE "%?e:name%"
    OR u.fullname LIKE "%?e:name%"
    OR tags REGEXP "(^|\,| )?e:name($|\,)"
  GROUP BY u.id
  ORDER BY u.username ASC
  LIMIT ?i:start, ?i:count
EOQ;
        $result           = $db->query( $select, array(
            'name'        => $tmp
            , 'start'     => $start
            , 'count'     => $C->PAGING_NUM_USERS
            , 'user'      => $page->user->id
          ), 'object');
        $i = 1;
        foreach($result as $o){
          $D->start_from = $o->id;
				  if( $i == 1 ){
					  $D->lats_post_id = $o->id;
				  }
				  $i++;
          $uids[$o->id] = array($o->id, $o->username, $o->fullname, $o->position, $o->num_followers, $o->num_posts, $o->avatar);
          if(!empty($o->whom)){
            $whom         = explode(',', $o->whom);
            foreach($whom as $follu){
              $D->if_follow_user[$follu] = 1;
            }
          }
        }
      }
      
      if( 0 == $D->num_results ) {
        $D->noposts_box_title	= $page->lang('srch_noresult_users_ttl');
        $D->noposts_box_text	= $page->lang('srch_noresult_users_txt');
        $D->users_html	= $page->load_template('noposts_box.php', FALSE);
      }
      else {
        ob_start();
        foreach($uids as $k=>$v) {
          $D->u = new \stdClass;
          $D->u->id = $v[0];
          $D->u->username = $v[1];
          $D->u->fullname = $v[2];
          $D->u->position = $v[3];
          $D->u->num_followers = $v[4];
          $D->u->num_posts = $v[5];
          $D->u->avatar = (empty($v[6]))? $C->DEF_AVATAR_USER : $v[6];

          $page->load_template('single_user.php');
        }
        
        $D->paging_url	= $C->SITE_URL.'search/tab:users/s:'.urlencode($D->search_string).'/pg:';
        if( $D->num_pages > 1 ) {
          $page->load_template('paging_users.php');
        }
        $D->users_html	= ob_get_contents();
        ob_end_clean();
      }
      if( isset($ctrl->request['parameter.from']) && 'ajax' === $ctrl->request['parameter.from'] 
        && !isset($ctrl->request['post.savesearch'])) {
        echo 'OK:'.$D->start_from.':NUM_POSTS:'.$D->num_results.':LAST_POST_ID:'.$D->lats_post_id.':';
        echo $D->users_html;
        exit;
      }
    }
    
  }

}
