<?php
namespace Accido\Models\Repositories\Mysql;
use Accido\Model;
use Accido\Event;
use Accido\View;
use Accido\Controller;
use Accido\Models\DB;
defined('CORE_ROOT') or die('No direct script access.');
/**
 * Attachment 
 * 
 * @uses Model
 * @package Repository
 * @version 1.0
 * @copyright Sat, 26 Oct 2013 07:41:47 +0300 Accido
 * @author Andrew Scherbakov <kontakt.asch@gmail.com> 
 * @license PHP Version 5.2 {@link http://www.php.net/license/}
 */
class Attachment extends Model {

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
      'controllers_ajax_postform_submit_content' => Event::ATTR_NORMAL_EVENT_PRIORITY,
    ),
    self::OPT_SQL_INIT_CODE           => array(
      /**
       * use {:prefix} construction for prefix table 
       * use {:charset} construction for charachter table
       */
      DB::ATTR_MYSQL                  => 
<<<EOQ
SET @s = (SELECT IF(
    (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_name = '{:prefix}posts_attachments'
        AND table_schema = DATABASE()
        AND column_name = 'info'
    ) > 0,
    "SELECT 1",
    "ALTER TABLE {:prefix}posts_attachments ADD COLUMN info TEXT, ADD FULLTEXT(info)"
));
PREPARE stmt FROM @s;
EXECUTE stmt;
UPDATE {:prefix}posts_attachments SET info = data WHERE info=NULL;
EOQ
    ),
    /**
     * table name without db prefix
     */
    self::OPT_TABLE                   => 'posts_attachments',
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
   * event_controllers_ajax_postform_attach_content
   *
   * @param View $view
   * @param Controller $ctrl
   * @uses
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   * @return
   */
  public function event_controllers_ajax_postform_submit_content(View $view, Controller $ctrl){
    global $saved_post_id;
    $embera     = $this->register_model('Embera');
    $db         = $this->register_model('DB')->get(DB::OPT_DB);
    $table      = $this->get(self::OPT_TABLE);
    $helper     = $this->register_model('Helper');;
    $prefix     = $helper->get(DB::OPT_PREFIX);
    if($saved_post_id){
      $id       = intval(str_ireplace('_public', '', $saved_post_id));
      $select   =
<<<EOQ
SELECT type, data, id
  FROM ?t USE INDEX(post_id)
  WHERE post_id=?i
EOQ;
      $db->autocommit(true);
      $db->transactionRun(function() use($db, $select, $prefix, $table, $id, $embera){
        $data     = $db->query($select, array($prefix . $table, $id), 'assoc');
        foreach($data as $row){
          $id       = $row['id'];
          if ('videoembed' === $row['type']){
            $row    = unserialize($row['data']);
            $info   = $embera->info($row->orig_url);
            $info   = empty($info) ? $row->orig_url : serialize($info);
          }
          else{
            $info   = $row['data'];
          }
          $update = 
<<<EOQ
UPDATE ?t
  SET info=?
  WHERE id=?i
EOQ;
          //var_dump($db->makeQuery($update, array($prefix . $table, serialize($info), $id)));
          $db->query($update, array($prefix . $table, $info, $id), 'ar');
        }
      });
    }
  }
}
