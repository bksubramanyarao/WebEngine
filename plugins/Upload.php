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
namespace Plugin;

/**
 * Upload class
 *
 * A class grouping useful methods to manage file uploads.
 *
 * @category Plugin
 * @package  Machine
 * @author   Paolo Savoldi <paooolino@gmail.com>
 * @license  https://github.com/paooolino/Machine/blob/master/LICENSE 
 *           (Apache License 2.0)
 * @link     https://github.com/paooolino/Machine
 */
class Upload
{
    private $_machine;
	private $_uploadpath;
	
    public function __construct($machine)
    {
        $this->_machine = $machine;
		$this->_uploadpath = "uploads/";
    }

	public function setUploadPath($uploadpath)
	{
		$this->_uploadpath = $uploadpath;
	}
	
	public function detectPostMaxSizeExceeded()
	{
		$r = $this->_machine->getRequest();
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
	
	public function upload($filearr)
	{
		if ($filearr["error"] == 0) {
			$uploadpath = $this->_uploadpath . date("d-m-Y") . "/";
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
