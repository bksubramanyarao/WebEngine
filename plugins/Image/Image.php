<?php
/**
 * The Machine
 *
 * PHP version 5
 *
 * @category  Plugin
 * @package   Machine
 * @author    Paolo Savoldi <paooolino@gmail.com>
 * @copyright 2017 Paolo Savoldi
 * @license   https://github.com/paooolino/Machine/blob/master/LICENSE 
 *            (Apache License 2.0)
 * @link      https://github.com/paooolino/Machine
 */
namespace Machine\Plugin;

use Intervention\Image\ImageManager;

/**
 * Image class
 *
 * A class grouping useful methods to manage images.
 *
 * @category Plugin
 * @package  Machine
 * @author   Paolo Savoldi <paooolino@gmail.com>
 * @license  https://github.com/paooolino/Machine/blob/master/LICENSE 
 *           (Apache License 2.0)
 * @link     https://github.com/paooolino/Machine
 */
class Image
{
  private $_machine;
  private $_maxW;
  private $_maxH;
  private $_imageManager;
  
  public function __construct($machine)
  {
    $this->_machine = $machine;
    $this->_maxW = 2000;
    $this->_maxH = 2000;
    $this->_imageManager = new ImageManager(array('driver' => 'gd'));
  }
  
  /**
   *  Get the image thumbnail
   *
   *  If needed, creates the thumbnail on the fly.
   *  Usage
   *    {{Image|Get|<filename>}}
   *    {{Image|Get|<filename>|640|480}}
   *    {{Image|Get|<filename>|W|480}}
   *    {{Image|Get|<filename>|640|H}}
   *
   *  @return 
   */
  public function Get($params)
  {
    $return_value = "";
    
    $filename = $params[0];
    if (isset($params[1]) && isset($params[2])) {
      $dest_w = $params[1];
      $dest_h = $params[2];
    }
    
    $pathinfo = pathinfo($filename);
    
    // check if the original file exists.
    if (!file_exists($filename)) {
      return "";
    }
    
    // check if the big/ version exists.
    // big/ version is required and is always created, if not exists.
    $big_dir = $pathinfo["dirname"] . "/big/";
    if (!file_exists($big_dir)) {
      mkdir($big_dir, 0777, true);
    }
    $filename_big = $big_dir . basename($filename);
    if (!file_exists($filename_big)) {
      $image = $this->_imageManager->make($filename);
      $w = $new_w = $image->width();
      $h = $new_h = $image->height();
      if ($w > $this->_maxW) {
        $new_w = $this->_maxW;
        $new_h = ($h * $new_w) / $w; 
      }
      if ($new_h > $this->_maxH) {
        $new_h = $this->maxH;
        $new_w = ($new_h * $w) / $h;
      }
      // load
      $image->resize($new_w, $new_h);
      $image->save($filename_big);
    }
    $return_value = $filename_big;
    
    // check for fixed, if requested.
    if (isset($dest_w) && isset($dest_h)) {
      $thumbsdir = $pathinfo["dirname"] . "/big/thumbs/" . $dest_w . "x" . $dest_h . "/";
      if (!file_exists($thumbsdir)) {
        mkdir($thumbsdir, 0777, true);
      }
      $filename_thumb = $thumbsdir . basename($filename);
      if (!file_exists($filename_thumb)) {
        $image = $this->_imageManager->make($filename_big);
        $w = $image->width();
        $h = $image->height();
        if ($dest_h == "H" && is_int($dest_w)) {
          $dest_h = ($h * $dest_w) / $w;
        }
        if ($dest_w == "W" && is_int($dest_h)) {
          $dest_w = ($w * $dest_h) / $h;
        }
        $image->fit(intval($dest_w), intval($dest_h));
        $image->save($filename_thumb);
      }
      $return_value = $filename_thumb;
    }
            
    $r = $this->_machine->getRequest();
    return "//" . $r["SERVER"]["HTTP_HOST"] . $this->_machine->basepath . $return_value;
  }
}
