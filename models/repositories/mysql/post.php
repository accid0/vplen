<?php
namespace Accido\Models\Repositories\Mysql;
use Accido\Model;
use Accido\Event;
use Accido\View;
use Accido\Controller;
use Accido\Models\DB;
defined('CORE_ROOT') or die('No direct script access.');
/**
 * Post 
 * 
 * @uses Model
 * @package Repository
 * @version 1.0
 * @copyright Sat, 26 Oct 2013 07:41:47 +0300 Accido
 * @author Andrew Scherbakov <kontakt.asch@gmail.com> 
 * @license PHP Version 5.2 {@link http://www.php.net/license/}
 */
class Post extends Model {

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
      DB::ATTR_MYSQL                  => 
<<<EOQ
CREATE TABLE IF NOT EXISTS {:prefix}likes
  (
    post INT(11) NOT NULL,
    user INT(11) NOT NULL,
    type INT(2) NOT NULL,
    PRIMARY KEY(post,user),
    INDEX(post),
    INDEX(user)
  )
  ENGINE=InnoDB
EOQ
      ,
    ),
    /**
     * table name without db prefix
     */
    self::OPT_TABLE                   => 'posts',
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
   
    if( $D->tab == 'posts' )
    {
      $valid_ptypes = array('link','image','video','file','comments','rss','tweets', 'noembed');
      $D->ptypes_choosen = isset($ctrl->request['post.ptype']) ? $ctrl->request->get('post.ptype') : array();
      if( isset($ctrl->request['parameter.ptypes']) && !empty($ctrl->request['parameter.ptypes']) ){
        $D->ptypes_choosen = explode(',', $ctrl->request->get('parameter.ptypes'));
      }
      
      foreach($D->ptypes_choosen as $k=>$v){
        if(!in_array($v, $valid_ptypes)){
          unset($D->ptypes_choosen[$k]);
        }
      }
      
      $D->paging_url = $C->SITE_URL.'search';
      $D->paging_url .= '/ptypes:'.implode(',', $D->ptypes_choosen);
      
      if( isset($ctrl->request['post.puser']) || isset($ctrl->request['parameter.puser']) ){
        $puser = isset($ctrl->request['post.puser'])
          ? $ctrl->request->get('post.puser', FILTER_SANITIZE_STRING) 
          : $ctrl->request->get('parameter.puser', FILTER_SANITIZE_STRING);
        $puser = preg_replace('/[^a-z0-9-\_]/iu', '', $puser);
        $D->paging_url .= (!empty($puser))? '/puser:'.$puser : '';
      }else{
        $puser = '';
      }
      $D->puser = $puser;
      
      if( isset($ctrl->request['post.pgroup']) || isset($ctrl->request['parameter.pgroup']) ){
        $pgroup = isset($ctrl->request['post.pgroup'])
          ? $ctrl->request->get('post.pgroup', FILTER_SANITIZE_STRING) 
          : $ctrl->request->get('parameter.pgroup', FILTER_SANITIZE_STRING);
        $pgroup = preg_replace('/[^ا-یא-תÀ-ÿ一-龥а-яa-z0-9\-\.\s]/iu', '', $pgroup);
        $D->paging_url .= (!empty($pgroup))? '/pgroup:'.$pgroup : '';
      }else{
        $pgroup = '';
      }
      $D->pgroup = $pgroup;
      
      if( isset($ctrl->request['post.pdate1']) || isset($ctrl->request['parameter.pdate1']) ){
        $pdate1	= isset($ctrl->request['post.pdate1'])
          ? $ctrl->request->get('post.pdate1', FILTER_SANITIZE_STRING) 
          : $ctrl->request->get('parameter.pdate1', FILTER_SANITIZE_STRING);
        if( is_array($pdate1) ){
          $pdate1	= trim(implode(',', preg_replace('/[^0-9]/iu','',$pdate1)), ',');
        }
        if( ! preg_match('/^[0-9]{1,2}\,[0-9]{1,2}\,[0-9]{4}$/', $pdate1) ) { $pdate1 = ''; }
        $D->paging_url .= (!empty($pdate1))? '/pdate1:'.$pdate1 : '';
      }else{
        $pdate1 = '';
      }
      $D->pdate1 = $pdate1;
      
      if( isset($ctrl->request['post.pdate2']) || isset($ctrl->request['parameter.pdate2']) ){
        $pdate2	= isset($ctrl->request['post.pdate2'])
          ? $ctrl->request->get('post.pdate2', FILTER_SANITIZE_STRING) 
          : $ctrl->request->get('parameter.pdate2', FILTER_SANITIZE_STRING);
        if( is_array($pdate2) ){
          $pdate2	= trim(implode(',', preg_replace('/[^0-9]/iu','',$pdate2)), ',');
        }
        if( ! preg_match('/^[0-9]{1,2}\,[0-9]{1,2}\,[0-9]{4}$/', $pdate2) ) { $pdate2 = ''; }
        $D->paging_url .= (!empty($pdate2))? '/pdate2:'.$pdate2 : '';
      }else{
        $pdate2 = '';
      }
      $D->pdate2 = '';
      
      if( isset($ctrl->request['post.lookfor']) || !empty($ctrl->request['post.lookfor']) ){
        $D->search_string = str_replace(array('/', '%'), '', $ctrl->request->get('post.lookfor', FILTER_SANITIZE_STRING));
      }elseif(isset($ctrl->request['parameter.s'])){
        $D->search_string = urldecode($ctrl->request->get('parameter.s', FILTER_SANITIZE_STRING & FILTER_SANITIZE_ENCODED)); 
      }
      
      $D->paging_url .= '/s:'.$D->search_string;
      
      if(isset($ctrl->request['post.puser']) || isset($ctrl->request['post.pgroup']) || isset($ctrl->request['post.pdate1']) || isset($ctrl->request['post.pdate2']) || isset($ctrl->request['post.lookfor']) ){
        $ctrl->request->redirect($D->paging_url);
      }

      $D->form_user	    = $puser;
      $D->form_group	  = $pgroup;
      $D->form_date1		= array('d'=>'', 'm'=>'', 'y'=>'');
      $D->form_date2		= array('d'=>'', 'm'=>'', 'y'=>'');
      if( isset($pdate1) && ! empty($pdate1) ) {
        list($D->form_date1['d'], $D->form_date1['m'], $D->form_date1['y']) = explode(',', $pdate1);
      }
      if( isset($pdate2) && ! empty($pdate2) ) {
        list($D->form_date2['d'], $D->form_date2['m'], $D->form_date2['y']) = explode(',', $pdate2);
      }
      $D->form_date1_days	= array('');
      for($i=1; $i<=31; $i++) { $D->form_date1_days[] = $i; }
      $D->form_date1_months	= array('');
      for($i=1; $i<=12; $i++) { $D->form_date1_months[] = $i; }
      $D->form_date1_years	= array();
      $tmp	= intval(date('Y'))-10;
      if( ! $tmp ) { $tmp = intval(date('Y')); }
      for($i=$tmp; $i<=intval(date('Y')); $i++) {
        $D->form_date1_years[]	= $i;
      }
      $D->form_date1_years[]	= '';
      $D->form_date1_years	= array_reverse($D->form_date1_years);
      $D->form_date2_days	= $D->form_date1_days;
      $D->form_date2_months	= $D->form_date1_months;
      $D->form_date2_years	= $D->form_date1_years;
      
      if( true ) { 
        $u	= FALSE;
        $g	= FALSE;
        if( !$D->error && !empty($puser) ) {
          if( ! $u = $page->network->get_user_by_username($puser) ) {
            $D->error	= TRUE;
            $D->errmsg	= $page->lang('srch_noresult_posts_invusr', array('#USERNAME#'=>'<b>'.htmlspecialchars($puser).'</b>'));
          }
        }
        if( !$D->error && !empty($pgroup) ) {
          if( ! $g = $page->network->get_group_by_name($pgroup) ) {
            $D->error	= TRUE;
            $D->errmsg	= $page->lang('srch_noresult_posts_invgrp', array('#GROUP#'=>'<b>'.htmlspecialchars($pgroup).'</b>'));
          }
        }
        if( !$D->error && $g && $g->is_private ) {
          if( ! in_array(intval($page->user->id), $page->network->get_group_invited_members($g->id)) ) {
            $g	= FALSE;
            $D->error	= TRUE;
            $D->errmsg	= $page->lang('srch_noresult_posts_invgrp', array('#GROUP#'=>'<b>'.htmlspecialchars($pgroup).'</b>'));
          }
        }
        $t1	= FALSE;
        $t2	= FALSE;
        if( !$D->error && (!empty($pdate1) || !empty($pdate2)) ) {
          if( ! empty($pdate1) ) {
            list($d,$m,$y) = explode(',', $pdate1);
            $t1	= mktime(0, 0, 1, $m, $d, $y);
            if( $t1 > time() ) {
              $D->error	= TRUE;
              $D->errmsg	= $page->lang('srch_noresult_posts_invdt');
            }
          }
          if( ! empty($pdate2) ) {
            list($d,$m,$y) = explode(',', $pdate2);
            $t2	= mktime(23, 59, 59, $m, $d, $y);
          }
          if( !$D->error && $t1 && $t2 && $t1>$t2 ) {
            $D->error	= TRUE;
            $D->errmsg	= $page->lang('srch_noresult_posts_invdt');
          }
        }
        if( !$D->error ) {
          $select =
<<<EOQ
SELECT p.*, "public" AS `type`, GROUP_CONCAT( CAST( uf.whom AS CHAR ) SEPARATOR ',' ) whom,
    MATCH(p.message) AGAINST('>>"?e:search" ?e:words1 ?e:words2' IN BOOLEAN MODE) as rel
  FROM {$prefix}{$table} p
  ?query:attached
  ?query:followed
  WHERE ?query:tweetrss 
    ?query:qsearch
    ?query:comsearch
    ?query:usearch
    ?query:gsearch
    ?query:exclude
    ?query:date
    GROUP BY p.id, uf.whom
    ORDER BY ?query:order
    LIMIT ?i:from, ?i:count
EOQ;
          $sel_count            =
<<<EOQ
SELECT COUNT(*)
  FROM {$prefix}{$table} p
  ?query:attached
  ?query:followed
  WHERE ?query:tweetrss 
    ?query:qsearch
    ?query:comsearch
    ?query:usearch
    ?query:gsearch
    ?query:exclude
    ?query:date
EOQ;
          $search_rss           = (in_array('rss', $D->ptypes_choosen))? TRUE : FALSE;
          $search_tweets        = (in_array('tweets', $D->ptypes_choosen))? TRUE : FALSE;
          $tweet_rss            = '';
          if( !$search_rss && !$search_tweets ) $tweet_rss    = '(p.user_id<>0)'; //search in everything
          elseif( $search_rss && !$search_tweets ) $tweet_rss = '(p.api_id<>2 AND p.user_id<>0)';
          elseif( !$search_rss && $search_tweets ) $tweet_rss = '(p.api_id<>6 AND p.user_id<>0)';
          else $tweet_rss       = '(p.api_id<>6 AND p.api_id<>2 AND p.user_id<>0)';//only posts, without rss and tweets
 
          $qsearch              = '';
          $followed             = 'JOIN ' . $prefix . 'users_followed uf ON uf.whom = p.user_id AND uf.who = ?:who';
          $search	              = str_replace(array('%','_'), array('\%','\_'), $D->search_string);
          if( !empty($search) && $search[0] != '#' ) {
            $search	= preg_replace('/^\#/', '', $search);
          }
          $words                = preg_split('/[^\pL\pN\'_]++/ui', $search, -1, PREG_SPLIT_NO_EMPTY);
          $words1               = '>(+' . implode('* +', $words) . '*)';
          $words2               = '<(' . implode('* ', $words) . '*)';
          if (!empty($search) && !empty($words)){
            $qsearch            =
<<<EOQ
    AND (p.message LIKE "%?e:search%" 
      OR MATCH(p.message) AGAINST('>>"?e:search" ?e:words1 ?e:words2' IN BOOLEAN MODE))
EOQ;
            $followed           = 'LEFT JOIN ' . $prefix . 'users_followed uf ON uf.whom = p.user_id AND uf.who = ?:who';
          }

          $search_in_comments	  = '';
          if( array_search('comments', $D->ptypes_choosen) !== FALSE ) {
            $search_in_comments	=
<<<EOQ
    OR (
      p.id IN (
      SELECT post_id 
        FROM {$prefix}posts_comments 
        WHERE message LIKE "%?e:search%"
          OR MATCH(message) AGAINST('>>"?e:search" ?e:words1 ?e:words2' IN BOOLEAN MODE)
      )
    )
EOQ;
          }
          
          $usearch = $gsearch = $exclude = '';
          $user    = $group   = $gexclude = $uexclude = '-1';
          if( $u ) {
            $user               = $u->id;
            $usearch            = 
<<<EOQ
    AND (
      p.user_id = ?:user
    )
EOQ;
          }
          if( $g ) {
            $group              = $g->id;
            $gsearch            =
<<<EOQ
    AND (
      p.group_id = ?:group
    )
EOQ;
          }
          else {
            $exclude            =
<<<EOQ
    AND (
      p.group_id NOT IN ('?query:gexclude')
    )
    AND (
      p.user_id NOT IN ('?query:uexclude')
      OR p.group_id > 0
    )
EOQ;
            $not_in_groups	    = array();
            $without_users 	    = array();
            if( !$page->user->is_logged || !$page->user->info->is_network_admin ) {
              $not_in_groups 	  = array_diff( $page->network->get_private_groups_ids(), $page->user->get_my_private_groups_ids() ); 
              $gexclude		      = implode('\', \'', $not_in_groups);
              $gexclude         = empty($gexclude) ? '-1': $gexclude;
              $without_users 	  = array_diff( $page->network->get_post_protected_user_ids(), $page->user->get_my_post_protected_follower_ids() ); 
              $uexclude 		    = implode('\', \'', $without_users);
              $uexclude         = empty($uexclude) ? '-1': $uexclude;
            }
          }

          $date                 = '';
          if( $t1 && $t2 ) {
            $date               = ' AND p.date BETWEEN "'.$t1.'" AND "'.$t2.'"';
          }
          elseif( $t1 ) {
            $date               = ' AND p.date>="'.$t1.'"';
          }
          elseif( $t2 ) {
            $date	              = ' AND p.date<="'.$t2.'"';
          }

          $attached             = '';
          $inter                = array_intersect((array)$D->ptypes_choosen, array('link', 'image', 'video', 'file', 'noembed'));
          
          if (empty($inter)){
            $inter = array('link', 'image', 'video', 'file');
          }
          if( in_array('noembed', $inter) ) {
            $attached           = 'LEFT JOIN posts_attachments pa ON p.id=pa.post_id';
            $qsearch            .=
<<<EOQ
    AND pa.id is NULL
EOQ;
          }
          else{
            $not_in_att	= array();
            $tmp	= array_flip($inter);
            
            if( ! isset($tmp['link']) ) { $not_in_att[] = 'link'; }
            if( ! isset($tmp['image']) ) { $not_in_att[] = 'image'; }
            if( ! isset($tmp['video']) ) { $not_in_att[] = 'videoembed'; $not_in_att[] = 'videoupload'; }
            if( ! isset($tmp['file']) ) { $not_in_att[] = 'file'; }
            $attached	          = 'CROSS JOIN posts_attachments pa ON p.id=pa.post_id';
            
            if( count($not_in_att) == 1 ) {
              $attached	        .= '  AND `type` !=\''.reset($not_in_att) .'\'';
            }
            elseif(!empty($not_in_att)){
              $attached	        .= '  AND `type` NOT IN(\''.implode('\', \'', $not_in_att).'\')';
            }
            else $attached       = 'LEFT JOIN posts_attachments pa ON p.id=pa.post_id';
            $qsearch             = !empty($qsearch) ? preg_replace("/\)[ \s\n\r]*$/ui", '', $qsearch) . 
<<<EOQ
      OR pa.info LIKE "%?e:search%" 
      OR MATCH(pa.info) AGAINST('>>"?e:search" ?e:words1 ?e:words2' IN BOOLEAN MODE))
EOQ
              : '';
          }

          $order                  = 'p.id DESC';
          $D->sortin              = 'relevant';
          if(isset($ctrl->request['parameter.sortin']) && !empty($ctrl->request['parameter.sortin'])){
            $sort                 = $ctrl->request->get('parameter.sortin', FILTER_SANITIZE_STRING);
            $D->sortin            = $sort;
            switch($sort){
              case 'relevant':
              $order              = 'rel DESC';
              break;
              case 'alfa':
              $order              = 'p.message ASC';
              break;
              case 'date':
              default:
              break;
            }
          }
          $D->paging_url .= '/sortin:' . $D->sortin;

          $sel_count      = $db->makeQuery($sel_count, array(
            'tweetrss'    => $tweet_rss,
            'followed'    => $followed,
            'qsearch'     => $qsearch,
            'search'      => $search,
            'words1'      => $words1,
            'words2'      => $words2,
            'comsearch'   => $search_in_comments,
            'usearch'     => $usearch,
            'gsearch'     => $gsearch,
            'exclude'     => $exclude,
            'date'        => $date,
            'attached'    => $attached,
          ));
          
          /*
           *var_dump($db->makeQuery($sel_count, array(
           *  'search'      => $search,
           *  'words1'      => $words1,
           *  'words2'      => $words2,
           *  'user'        => $user,
           *  'group'       => $group,
           *  'gexclude'    => $gexclude,
           *  'uexclude'    => $uexclude,
           *  'who'         => $page->user->id,
           *)));
           */
          $D->num_results	= $db->query($sel_count, array(
            'search'      => $search,
            'words1'      => $words1,
            'words2'      => $words2,
            'user'        => $user,
            'group'       => $group,
            'gexclude'    => $gexclude,
            'uexclude'    => $uexclude,
            'who'         => $page->user->id,
          ), 'el');
          $D->paging_url	.= '/pg:'; 

          if( $D->num_results == 0 ) {
            $D->error	= TRUE;
            $D->errmsg	= $page->lang('srch_noresult_posts_def');
          }
          else {
            $D->num_pages	= ceil($D->num_results / $C->PAGING_NUM_POSTS);
            $D->pg	= isset($ctrl->request['parameter.pg']) ? $ctrl->request->get('parameter.pg', FILTER_SANITIZE_NUMBER_INT) : 1;
            $D->pg	= min($D->pg, $D->num_pages);
            $D->pg	= max($D->pg, 1);
            $from	  = ($D->pg - 1) * $C->PAGING_NUM_POSTS;
            
            $select      = $db->makeQuery($select, array(
              'tweetrss'    => $tweet_rss,
              'followed'    => $followed,
              'qsearch'     => $qsearch,
              'search'      => $search,
              'words1'      => $words1,
              'words2'      => $words2,
              'comsearch'   => $search_in_comments,
              'usearch'     => $usearch,
              'gsearch'     => $gsearch,
              'exclude'     => $exclude,
              'date'        => $date,
              'attached'    => $attached,
              'from'        => $from,
              'count'       => $C->PAGING_NUM_POSTS,
              'order'       => $order,
            ));
            
            /*
             *var_dump($db->makeQuery($select, array(
             *  'search'      => $search,
             *  'words1'      => $words1,
             *  'words2'      => $words2,
             *  'user'        => $user,
             *  'group'       => $group,
             *  'gexclude'    => $gexclude,
             *  'uexclude'    => $uexclude,
             *  'who'         => $page->user->id,
             *)));
             */
            $res	= $db->query($select, array(
              'search'      => $search,
              'words1'      => $words1,
              'words2'      => $words2,
              'user'        => $user,
              'group'       => $group,
              'gexclude'    => $gexclude,
              'uexclude'    => $uexclude,
              'who'         => $page->user->id,
            ), 'object');
            $tmpposts	= array();
            $tmpids	= array();
            $buff		= NULL;
            $postusrs	= array();
            $D->if_follow_me = array();
            $i= 1;
            foreach($res as $obj) {
              $D->start_from = $obj->id;
				      if( $i == 1 ){
					      $D->lats_post_id = $obj->id;
				      }
				      $i++;
              $buff = new \post($obj->type, FALSE, $obj);
              if( $buff->error ) {
                continue;
              }
              if( isset($ctrl->request['parameter.from']) 
                && isset($ctrl->request['parameter.onlypost'])
                && !empty($ctrl->request['parameter.onlypost'])
                && $ctrl->request['parameter.onlypost'] != $buff->post_tmp_id ) {
                continue;
              }
              
              $D->p	= $tmpposts[] = $buff;
              $tmpids[]	= $buff->post_tmp_id;
              $postusrs[]	= $buff->post_user->id;
              if(!empty($o->whom)){
                $whom         = explode(',', $o->whom);
                foreach($whom as $follu){
                  $D->if_follow_user[$follu] = 1;
                }
              }
            }
            unset($buff);
            $D->do_not_check_new_comments = TRUE;
            
            
            $D->i_follow  = array();
            if(isset($page->user) && isset($page->network) && isset($page->user->id))
              $D->i_follow	= array_fill_keys(array_keys((array)($page->network->get_user_follows($page->user->id, FALSE, 'hefollows')->follow_users)), 1); 
            ob_start();
            foreach($tmpposts as $tmp) {
              $D->p	= $tmp;
              $D->post_show_slow	= FALSE;
              if( isset($ctrl->request['parameter.from']) 
                && 'ajax' === $ctrl->request['parameter.from'] 
                && isset($ctrl->request['post.lastpostdate']) 
                && $D->p->post_date > $ctrl->request->get('post.lastpostdate', FILTER_SANITIZE_NUMBER_INT) ) {
                $D->post_show_slow	= TRUE;
              }
              if( isset($ctrl->request['parameter.from']) 
                && 'ajax' === $ctrl->request['parameter.from']
                && isset($ctrl->request['parameter.onlypost'])
                && $ctrl->request['parameter.onlypost'] != $D->p->post_tmp_id
                && !empty($ctrl->request['parameter.onlypost'])) {
                continue;
              }
              $D->parsedpost_attlink_maxlen	= 75;
              $D->parsedpost_attfile_maxlen	= 71;
              if( isset($D->p->post_attached['image']) ) {
                $D->parsedpost_attlink_maxlen	-= 10;
                $D->parsedpost_attfile_maxlen	-= 12;
              }
              if( isset($D->p->post_attached['videoembed']) ) {
                $D->parsedpost_attlink_maxlen	-= 10;
                $D->parsedpost_attfile_maxlen	-= 12;
              }
              $right_post_type = (!$D->p->is_system_post && !$D->p->is_feed_post);
              
              $D->show_my_email = FALSE;
              if( isset( $D->if_follow_me[$D->p->post_user->id] ) ||
                $D->p->post_user->id == $page->user->id ||
                (isset($page->user->info->is_network_admin) &&
                $page->user->info->is_network_admin)){
                $D->show_my_email = TRUE;
              }
              
              $D->protected_profile = FALSE;
              if($right_post_type && !$D->show_my_email && $D->p->post_user->is_profile_protected){
                $D->protected_profile = TRUE;
              }
              
              $D->show_reshared_design = ($D->p->post_resharesnum > 0);
              
              $page->load_template('single_post.php');
            }
            unset($D->p, $tmp, $tmpposts, $tmpids);
            
            if( $D->num_pages > 1 && !$page->param('onlypost') ) {
              $page->load_template('paging_posts.php');
            }
            $D->posts_html	= ob_get_contents();
            ob_end_clean();
          }

          if(isset($ctrl->request['parameter.from']) && 'ajax' === $ctrl->request['parameter.from'] 
            && !isset($ctrl->request['post.savesearch'])) {
            if ( '0' == $D->num_results){
              $D->posts_html = msgbox($page->lang('srch_noresult_posts_ttl'), $D->errmsg, FALSE);
            }
            echo 'OK:'.$D->start_from.':NUM_POSTS:'.$D->num_results.':LAST_POST_ID:'.$D->lats_post_id.':';
            echo $D->posts_html;
            exit;
          }
        }
      }else{
        $D->error	= TRUE;
        $D->errmsg	= $page->lang('srch_noresult_posts_def');
      }
    }
  }

}
