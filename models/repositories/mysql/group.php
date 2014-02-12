<?php
namespace Accido\Models\Repositories\Mysql;
use Accido\Model;
use Accido\Event;
use Accido\View;
use Accido\Controller;
use Accido\Models\DB;
defined('CORE_ROOT') or die('No direct script access.');
/**
 * Group 
 * 
 * @uses Model
 * @package Repository
 * @version 1.0
 * @copyright Sat, 26 Oct 2013 07:41:47 +0300 Accido
 * @author Andrew Scherbakov <kontakt.asch@gmail.com> 
 * @license PHP Version 5.2 {@link http://www.php.net/license/}
 */
class Group extends Model {

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
    self::OPT_TABLE                   => 'groups',
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
  public function event_controllers_search_main(View $view, Controller $ctrl){
    $D      = $view->D;
    $C      = $view->C;
    $page   = $view->page;

    $db     = $this->register_model('DB')->get(DB::OPT_DB);
    $table  = $this->get(self::OPT_TABLE);
    $helper = $this->register_model('Helper');
    $prefix = $helper->get(DB::OPT_PREFIX);
    if( $D->tab=='groups' )
    {
      $gids	= array();
      $D->if_follow_group = array();
      $D->if_can_leave 	= array();
      if( !empty($D->search_string) ){
        $not_in_groups	= array();
        if( !$page->user->is_logged || !$page->user->info->is_network_admin ) {
          $not_in_groups	= array();
          $not_in_groups 	= array_diff( $page->network->get_private_groups_ids(), $page->user->get_my_private_groups_ids() ); 
        }
        $tmp	    = str_replace(array('%','_'), array('\%','\_'), $D->search_string);
        $exclude  = count($not_in_groups) > 0 ? 'AND g.id NOT IN('.implode(',', $not_in_groups).')' : '';
        $select           =
<<<EOQ
SELECT COUNT(*)
  FROM {$prefix}{$table} g
  WHERE (g.groupname LIKE "%?e:search%" 
      OR g.title LIKE "%?e:search%")
    ?query:exclude
  ORDER BY title ASC, num_followers DESC
EOQ;
        $D->num_results   = $db->query($select, array('search' => $tmp, 'exclude' => $exclude), 'el');
        $D->num_pages	    = ceil($D->num_results / $C->PAGING_NUM_USERS);
        $D->pg	          = isset($ctrl->request['parameter.pg']) ? $ctrl->request->get('parameter.pg', FILTER_SANITIZE_NUMBER_INT) : 1;
        $D->pg	          = min($D->pg, $D->num_pages);
        $D->pg	          = max($D->pg, 1);
        $start            = ($D->pg - 1) * $C->PAGING_NUM_USERS;
        
        $select           =
<<<EOQ
SELECT GROUP_CONCAT( CAST( gf.group_id AS CHAR ) SEPARATOR ',' ) whog
    , GROUP_CONCAT( CAST( ga.group_id AS CHAR ) SEPARATOR ',' ) whoa
    , g.id
    , g.groupname
    , g.title
    , g.num_followers
    , g.num_posts
    , g.is_public
    , g.about_me
    , g.avatar
  FROM {$prefix}{$table} g
  LEFT JOIN {$prefix}groups_followed gf ON gf.group_id = g.id AND gf.user_id = ?:user
  LEFT JOIN {$prefix}groups_admins ga ON ga.group_id = g.id AND ga.user_id != ?:user
  WHERE (g.groupname LIKE "%?e:search%" 
      OR g.title LIKE "%?e:search%")
    ?query:exclude
  GROUP BY gf.group_id, ga.group_id
  ORDER BY g.groupname ASC
  LIMIT ?i:start, ?i:count
EOQ;
        $result           = $db->query( $select, array(
            'search'      => $tmp
            , 'exclude'   => $exclude
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
          $gids[$o->id] = array($o->id, $o->groupname, $o->title, $o->num_followers, $o->num_posts, $o->is_public, $o->about_me, $o->avatar);
          if(!empty($o->whog)){
            $whog         = explode(',', $o->whog);
            foreach($whog as $follu){
              $D->if_follow_group[$follu] = 1;
            }
          }
          $D->if_can_leave[$o->id] = isset($D->if_can_leave[$o->id]) ? $D->if_can_leave[$o->id] : -1;
          if(!empty($o->whoa)){
            $whoa         = explode(',', $o->whoa);
            foreach($whoa as $follu){
              $D->if_can_leave[$follu] = 1;
            }
          }
        }
      }
      
      if( 0 == $D->num_results ) {
        $D->noposts_box_title	= $page->lang('srch_noresult_groups_ttl');
        $D->noposts_box_text	= $page->lang('srch_noresult_groups_txt');
        $D->groups_html	= $page->load_template('noposts_box.php', FALSE);
      }
      else{
        ob_start();
        foreach($gids as $k=>$v) {
          $g = new \stdClass;
          $g->id = $v[0]; 
          $g->groupname = $v[1]; 
          $g->title = $v[2];
          $g->num_followers = $v[3];
          $g->num_posts = $v[4];
          $g->is_public = $v[5];
          $g->is_private = ($g->is_public == 0);
          $g->about_me = $v[6];
          $g->avatar = (empty($v[7]))? $C->DEF_AVATAR_GROUP : $v[7];
          $D->g	= $g;
          $page->load_template('single_group.php');
        }
        
        $D->paging_url	= $C->SITE_URL.'search/tab:group/s:'.urlencode($D->search_string).'/pg:';
        if( $D->num_pages > 1 ) {
          $page->load_template('paging_groups.php');
        }
        $D->groups_html	= ob_get_contents();
        ob_end_clean();
      }
      if( isset($ctrl->request['parameter.from']) && 'ajax' === $ctrl->request['parameter.from'] 
        && !isset($ctrl->request['post.savesearch'])) {
        echo 'OK:'.$D->start_from.':NUM_POSTS:'.$D->num_results.':LAST_POST_ID:'.$D->lats_post_id.':';
        echo $D->groups_html;
        exit;
      }
    }
  }

}
