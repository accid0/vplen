<?php
namespace Accido\Models;
use Accido\Model;
use Accido\Event;
use Accido\View;
use Accido\Controller;
use Accido\Models\DB;
use Accido\Models\Request;
defined('CORE_ROOT') or die('No direct script access.');
/**
 * Application 
 * 
 * @uses Model
 * @package 
 * @version $id$
 * @copyright 2013 Accido
 * @author Andrew Scherbakov <kontakt.asch@gmail.com> 
 * @license PHP Version 5.2 {@link http://www.php.net/license/}
 */
class Application extends Model {
  
  /**
   * OPT_INSTALLED
   *
   * @const string
   */
  const OPT_INSTALLED             = 'installed';

  /**
   * vars 
   * 
   * @var array
   * @access protected
   */
  protected $vars                                   =  array(
    self::OPT_EVENTS                                => array(
      'controllers_install'                         => Event::ATTR_EXPRESS_EVENT_PRIORITY,
      'controllers_search_main'                     => Event::ATTR_HIGH_EVENT_PRIORITY,
      'controllers_ajax_main'                       => Event::ATTR_NORMAL_EVENT_PRIORITY,
      'controllers_signup_main'                     => Event::ATTR_NORMAL_EVENT_PRIORITY,
      //'controllers_ajax_postform_attach_content'    => Event::ATTR_NORMAL_EVENT_PRIORITY,
    ),
    self::OPT_SQL_INIT_CODE                         => array(
    ),
  );

  // protected init() {{{ 
  /**
   * init
   * 
   * @access protected
   * @return void
   */
  protected function init(){
    error_reporting(E_ALL);
    register_shutdown_function(array($this, 'shutdown'));
    ob_start();
    $log          = $this->register_model('Log');
    try{

      $helper     = $this->register_model('Helper');
      
      $installed  = $helper->get(self::OPT_INSTALLED);

      if (true !== $installed){
        $helper->set(DB::OPT_REPOSITORIES, 'sessions,user,group'
          . ',comment,attachment,post,tag,whom,userfollowed,admin,groupfollowed,search');
      }

      $dbmodel    = $this->register_model('DB');
      $dbmodel->load_repositories();
      $db         = $dbmodel->get(DB::OPT_DB);
      $db->autocommit(true);
      //Controller::execute('Permissions', array('action' => 'shop', 'area' => 1));

      if (true !== $installed){
        $q = Controller::execute( 'Install' );
      }

      Controller::execute('Session');

      $request    = $this->register_model('Request');
      $request->init_global();
      $echo       = $this->register_model('Router')->run($request);
      if ( strlen($echo) ){
        echo $echo;
      }

      if (true != $installed)
        $helper->set(self::OPT_INSTALLED, true);
    }
    catch( Exception $e ){
      throw $e;
    }
    
  }
  // }}}

  /**
   * shutdown
   *
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   * @return void
   */
  public function shutdown(){
    Controller::execute('Shutdown');
    while(@ob_end_flush());
  }

  /**
   * event_controllers_install
   *
   * @param View $view
   * @param Controller $ctrl
   * @uses OPT_MODEL_HELPER 
   * 
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   * @return void
   */
  public function event_controllers_install( View $view, Controller $ctrl){
    $view->queries    = array ( (array)$this->get( self::OPT_SQL_INIT_CODE ) );
    $helper           = $this->register_model('Helper');
    $view->type       = $helper->get( DB::OPT_DB_SQL_TYPE );
    $view->prefix     = $helper->get( DB::OPT_PREFIX );
    $view->charset    = $helper->get(DB::OPT_DB_CHARSET);
    $ctrl->stream(Controller::ATTR_ACTION)
      ->done($this->register('Action'));
    $ctrl->stream(Controller::ATTR_RENDER)
      ->done($this->register('Render\\MultiQuery'));
  }

  /**
   * event_controllers_search_main
   *
   * @param View $view
   * @uses
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   * @return void
   */
  public function event_controllers_search_main(){
    $this->register_model('Search');
  }

  /**
   * event_controllers_ajax_main
   *
   * @param View $view
   * @param Controller $ctrl
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   */
  public function event_controllers_ajax_main(View $view, Controller $ctrl){
    $ctrl->response->header('Content-Type', 'application/json; charset="' . $ctrl->request[Request::OPT_REQUEST_CHARSET] . '"');
    $ctrl->stream(Controller::ATTR_ACTION)
      ->done($this->register('Action'));
    $ctrl->stream(Controller::ATTR_RENDER)
      ->listen(function() use ($view) {
        return $view->content;
      })
      //->listen($this->register('Render\\Header'))
      ->done($this->register('Render\\Json'));
    $this->handle('ajax/like/content')
      ->done(function($ctrl, $view, $_this){
        $_this->register('Like');
        return $ctrl;
      })
      ->done($this->register('Action'))
      ->fail(function($error){
        echo 'Error:';
        echo $error;
      });
  }

  /**
   * event_controllers_signup_main
   * 
   * @param View $view
   * @param Controller $ctrl
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license MIT http://opensource.org/licenses/MIT
   *
   */
  public function event_controllers_signup_main(View $view, Controller $ctrl){
    $recaptcha          = $this->register('Google\\ReCaptcha',
        '6LfBUe4SAAAAAPC26MjP_f3OptSfK9KHbeXXJCwR',
        '6LfBUe4SAAAAACH4oryrFOJj_Ww7_EB1SmJdsA4L',
        'signup/main');
    $ctrl->stream(Controller::ATTR_RENDER)
      ->done(function($ctrl) use ($recaptcha){
        return $recaptcha->html();
      });
  }
}
