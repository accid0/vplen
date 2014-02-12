<?php
namespace Accido\Controllers\Ajax\Like;
use Accido\Controller;
use Accido\Models\Router;
defined('CORE_ROOT') or die('No direct script access.');
/**
 * Class: Content
 *
 * @package Content
 * @subpackage Controller
 *
 * 
 * @see Controller
 * @author andrew scherbakov <kontakt.asch@gmail.com>
 * @version 0.1
 * @copyright © 2013 andrew scherbakov
 * @license GNU GPL 3.0 http://www.gnu.org/licenses/gpl.txt
 */
class Content extends Controller{

  /**
   * template
   *
   * @var string
   */
  protected $template = '';

  /**
   * do_action
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   * @return void
   */
  protected function do_action(){
    $router = $this->register_model('Router');
    $router->extend('content', $this->view);
  }

  /**
   * initialize
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   * @return void
   */
  protected function initialize(){

  }

  /**
   * finalize
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2013 andrew scherbakov
   * @license GNU GPL v3.0 http://www.gnu.org/licenses/gpl.txt
   *
   * @return void
   */
  protected function finalize(){

  }

}

