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
 *  Class: Like
 *
 * @package
 * @subpackage
 * 
 * 
 * @see Model
 * @author andrew scherbakov <kontakt.asch@gmail.com>
 * @version $id$
 * @copyright © 2014 andrew scherbakov
 *
 * The MIT License (MIT)
 * Copyright (c) 2013 <copyright holders>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class Like extends Model{

  /**
   * vars
   *
   * @var array
   */
  protected $vars                                                 = array(
    self::OPT_EVENTS                                              => array(
      
    ),
  );

  /**
   * init
   * 
   *
   * @since 0.1 Start version
   * @author andrew scherbakov <kontakt.asch@gmail.com>
   * @copyright © 2014 andrew scherbakov
   * @license MIT http://opensource.org/licenses/MIT
   *
   */
  protected function init(){
    global $page;
    $this->handle('ajax/like/all/content')
      ->listen(function($ctrl){
        return $ctrl->request->stream('get.id', '/^(?>\d++,?)+$/');
      })
      ->listen(intval($page->user->id))
      ->done(function($ctrl, $view, $_this, $id, $user){
        $param              = array($id, $user);
        $res                = 'kassoc:id';
        $view->set_filename('sql/like/all');
        return array($ctrl, $param, $res, $view, $id);
      })
      ->iterate()
      ->apply(range(0,2), $this->register('Render\\Query'))
      ->done(function($data, $view, $id){
        $ids                = explode(',', $id);
        $result             = array();
        foreach($ids as $index){
          $index            = intval($index);
          if(array_key_exists($index,$data))
            $result[$index] = $data[$index];
          else
            $result[$index] = array('id' => $index, 'like' => 0, 'mlike' => 0, 'dislike' => 0, 'user' => null);
        }
        $view->content      = $result;
      })
      ->fail(function($error){
        var_dump($error);
      });

    $this->handle('ajax/like/delete/content')
      ->listen(function($ctrl){
        return $ctrl->request->stream('get.id', FILTER_VALIDATE_INT);
      })
      ->listen(intval($page->user->id))
      ->done(function($ctrl, $view, $_this, $id, $user){
        $param              = array($id, $user);
        $res                = 'ar';
        $view->set_filename('sql/like/delete');
        return array($ctrl, $param, $res, $view, $id);
      })
      ->iterate()
      ->apply(range(0,2), $this->register('Render\\Query'))
      ->done(function($ar, $view, $id){
        $view->content = array('response' => $ar);
      })
      ->fail(function($error){
        $view->content = array('error' => $error);
      });

    $this->handle('ajax/like/add/content')
      ->listen(function($ctrl){
        return $ctrl->request->stream('get.id', FILTER_VALIDATE_INT);
      })
      ->listen(function($ctrl){
        return $ctrl->request->stream('get.type', FILTER_SANITIZE_STRING);
      })
      ->listen(intval($page->user->id))
      ->done(function($ctrl, $view, $_this, $id, $type, $user){
        switch($type){
          case 'like':
          $type             = 0;
          break;
          case 'mlike':
          $type             = 1;
          break;
          case 'dislike':
          $type             = 2;
          break;
          default:
          $type             = 3;
        }
        $param              = array($id, $user, $type);
        $res                = 'ar';
        $view->set_filename('sql/like/add');
        return array($ctrl, $param, $res, $view, $id);
      })
      ->iterate()
      ->apply(range(0,2), $this->register('Render\\Query'))
      ->done(function($ar, $view, $id){
        $view->content = array('response' => $ar);
      })
      ->fail(function($error){
        $view->content = array('error' => $error);
      });
  }

}
