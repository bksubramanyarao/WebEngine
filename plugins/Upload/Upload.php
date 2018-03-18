<?php
/**
 * WebEngine
 *
 * PHP version 5
 *
 * @category  Plugin
 * @package   WebEngine
 * @author    Paolo Savoldi <paooolino@gmail.com>
 * @copyright 2017 Paolo Savoldi
 * @license   https://github.com/paooolino/WebEngine/blob/master/LICENSE 
 *            (Apache License 2.0)
 * @link      https://github.com/paooolino/WebEngine
 */
namespace WebEngine\Plugin;

/**
 * Upload class
 *
 * A class grouping useful methods to manage file uploads.
 *
 * @category Plugin
 * @package  WebEngine
 * @author   Paolo Savoldi <paooolino@gmail.com>
 * @license  https://github.com/paooolino/WebEngine/blob/master/LICENSE 
 *           (Apache License 2.0)
 * @link     https://github.com/paooolino/WebEngine
 */
class Upload
{
  private $_engine;
  private $_uploadpath;
  public $add_date_to_uploadpath;
  
  public function __construct($engine)
  {
    $this->_engine = $engine;
    $this->_uploadpath = "uploads/";
    $this->add_date_to_uploadpath = true;
  }

  public function setUploadPath($uploadpath)
  {
    $this->_uploadpath = $uploadpath;
  }
  
  /**
   *  A function to detect if the PHP post max size setting has been reached
   *
   *  In such case, the PHP will not raise any exception but _POST and _FILES
   *  array are empty, having the content length bigger than zero.
   */
  public function detectPostMaxSizeExceeded()
  {
    $r = $this->_engine->getRequest();
    if (
      $r["SERVER"]["REQUEST_METHOD"] == "POST" && 
      empty($r["POST"]) &&
      empty($r["FILES"]) && 
      $r["SERVER"]["CONTENT_LENGTH"] > 0 
    ) {
      return true;
    }
    return false;
  }
  
  /**
   *  Perform the file upload.
   *
   *  The file will be uploaded in the uploads/ directory by default.
   *  In case of success, the returning array will have the following keys:
   *    "result" => "OK"
   *    "filename" => <file path relative to the project web root>
   *  In case of failure, instead:
   *    "result" => "KO"
   *    "errname" => <a string describing the upload error>
   *    "dump" => a dump of the _FILES array
   *
   *  @return array    
   */
  public function upload($filearr)
  {
    if ($filearr["error"] == 0) {
      $d = $this->add_date_to_uploadpath ? date("d-m-Y") : "";
      $uploadpath = $this->_uploadpath . $d . "/";
      if (!file_exists($uploadpath)) {
        mkdir($uploadpath, 0777, true);
      }
      $uploadfile = $uploadpath . basename($filearr['name']);
      $file_info = pathinfo($uploadfile);
      
      $n = 1;
      while (file_exists($uploadfile)) {
        $newname = $file_info["filename"] . "_" . $n . "." . $file_info["extension"]; 
        $uploadfile = $uploadpath . $newname;
        $n++;
      }
      
      if (move_uploaded_file($filearr['tmp_name'], $uploadfile)) {
        //echo "File is valid, and was successfully uploaded.\n";
        return ["result" => "OK", "filename" => $uploadfile];
      } else {
        //echo "Possible file upload attack!\n";
        die();
      }
    } else {
      return [
        "result" => "KO", 
        "errname" => $this->_getUploadErrName($filearr["error"]), 
        "dump" => $filearr
      ];
    }
  }
  
  private function _getUploadErrName($errno)
  {
    $errors = [
      1 => "upload-err-ini-size",
      2 => "upload-err-form-size",
      3 => "upload-err-partial",
      4 => "upload-err-no-file",
      5 => "upload-err-no-tmp-dir",
      6 => "upload-err-cant-write",
      7 => "upload-err-extension"
    ];
    return $errors[$errno];
  }
}
