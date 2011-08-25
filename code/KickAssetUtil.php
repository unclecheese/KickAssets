<?php
/**
 * A static class used for various factory methods that are used in file uploading
 * and proessing.
 *
 * @package KickAssets
 * @author UncleCheese <unclecheese@leftandmain.com>
 */
abstract class KickAssetUtil {

	
	/**
	 * @var string The maximum allowed file size for uploads.
	 * @todo Make this tie into the php.ini setting "upload_max_filesize"
	 */
	public static $allowed_max_file_size;



	/**
	 * Creates a relative path to the assets dir. Folders return "assets" as
	 * part of their filename, but other classes such as {@link Upload} don't want
	 * that in there.
	 *
	 * @param string The folder path
	 * @return string
	 */	
	public static function relative_asset_dir($dirname) {
		return preg_replace('|^'.ASSETS_DIR.'/|', '', $dirname);
	}



	/**
	 * Gets an icon based on the file extension. If an image, use its own source. 
	 *
	 * @param File
	 * @return string The path to the file.
	 */	
	public static function get_icon($f) {
		$ext = $f->Extension;
		if(!Director::fileExists("kickassets/images/{$ext}_64.png")) {
			$ext = $f->appCategory();
		}

		if(!Director::fileExists("kickassets/images/{$ext}_64.png")) {
			$ext = "generic";
		}
		$ext = strtolower($ext);
		return "kickassets/images/{$ext}_64.png";
		
		
	}
	


	/**
	 * Handles the upload request. This is a static function to ensure that it is easily
	 * accessible to other classes without having to instantiate a {@link Controller} object. 
	 * A lot of this code is lifted from {@link AssetAdmin}.
	 *
	 * @todo Error handling on this is crap.
	 * @param SS_HTTPRequest
	 * @param Folder A folder that will be the destination of the upload.
	 * @return array|string
	 */	
	public static function handle_upload(SS_HTTPRequest $r, $folder = null) {
		if(!$folder) {
			$folder = singleton('Folder');
		}
		$newFiles = array ();
		$errorResponse = "";
		if(isset($_FILES['file']) && is_array($_FILES['file'])) {
			$file_array = $_FILES['file'];
			foreach($file_array['tmp_name'] as $index => $value) {
				if(is_uploaded_file($value)) {
					$tmpFile = array (
						'tmp_name' => $value,
						'name' => $file_array['name'][$index],
						'size' => $file_array['size'][$index],
						'error' => $file_array['error'][$index]
					);
					// validate files (only if not logged in as admin)
					if(!File::$apply_restrictions_to_admin && Permission::check('ADMIN')) {
						$valid = true;
					} 
					else {

						// Set up the validator instance with rules
						$validator = new Upload_Validator();
						 $validator->setAllowedExtensions(File::$allowed_extensions);
						 $validator->setAllowedMaxFileSize(self::$allowed_max_file_size);

						// Do the upload validation with the rules
						$upload = new Upload();
						$upload->setValidator($validator);
						$valid = $upload->validate($tmpFile);
						if(!$valid) {
						 	$errors = $upload->getErrors();
						 	if($errors) foreach($errors as $error) {
						 		$errorResponse .= $error;
						 	}
						}
					}

					// move file to given folder
					if($valid) {
						$newFile = $folder->addUploadToFolder($tmpFile);
						$newFiles[] = $newFile;
					}
					else {
						return $errorResponse;
					}
					
					foreach($newFiles as $newFile) {
						$fileIDs[] = $newFile;
						$fileObj = DataObject::get_one('File', "\"File\".\"ID\"=$newFile");
						if (method_exists($fileObj, 'onAfterUpload')) $fileObj->onAfterUpload();
					}
				}
			}			
		}
		else {
			return "File is too large.";
		}
		return $newFiles;
	}
	
}