<?php

/**
 * An abstract base class for all fields implementing the KickAssetAdmin window.
 *
 * @author UncleCheese <unclecheese@leftandmain.com>
 * @package KickAssets
 */
abstract class KickAssetField extends FormField {

	
	
	/**
	 * @var array The actions allowed on this controller
	 */
	static $allowed_actions = array (
		'refresh',
		'upload'
	);
	

	
	/**
	 * @var string Path to the starting folder, e.g. "assets/my-folder"
	 */
	public $defaultFolder = null;



	/**
	 * @var string The template to use for the list of attached files. Refreshed
	 *				when the list changes.
	 */	
	public $AttachedFilesTemplate;
		
	
	
	/**
	 * Adds extra metadata to a File object that used by the form field, such as a thumbnail source.
	 *
	 * @param File $file
	 */
	public static function process_file($file) {
		if($file->ClassName == "Image" || is_subclass_of($file->ClassName, "Image")) {
			if($thumb = $file->CroppedImage(64,64)) {
				$file->Thumb = $thumb->getURL();			
			}
		}
		else {
			$file->Thumb = KickAssetUtil::get_icon($file);						
		}
	}
	


	/**
	 * Sets the default folder where the file browser will start, e.g. "assets/my-folder" 
	 *
	 * @param string $folder
	 */
	public function setDefaultFolder($folder) {
		$this->defaultFolder = Folder::findOrMake($folder);
	}



	/**
	 * Processes the uploaded files in the request. Most of the legwork is handled in {@link KickAssetUtil}
	 *
	 * @return SS_HTTPResponse
	 */
	public function upload(SS_HTTPRequest $r) {
		$response = KickAssetUtil::handle_upload($r, $this->defaultFolder);
		if(empty($response)) {
			return new SS_HTTPResponse("File did not upload", 500);
		}
		if(is_array($response)) {
			return new SS_HTTPResponse(implode(',',$response),200);
		}
		return new SS_HTTPResponse("Error: ".$response,500);
		
	}


	
	/**
	 * Loads the requirements and returns a rendered form field
	 *
	 * @return SSViewer
	 */
	public function FieldHolder() {
		Requirements::css('kickassets/css/file_attachment_field.css');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');
		Requirements::javascript('kickassets/javascript/fancybox/fancybox.js');
		Requirements::css('kickassets/javascript/fancybox/fancybox.css');
		Requirements::javascript('kickassets/javascript/file_attachment_field.js');
		return $this->renderWith('FileAttachmentField');
	}
	

	
	/**
	 * Returns a list of the files attached to this form field
	 *
	 * @return SSViewer
	 */
	public function AttachedFiles() {
		return $this->renderWith($this->AttachedFilesTemplate);
	}

	
	
	/**
	 * Generates a link to the file browser.
	 * @see KickAssetAdmin
	 *
	 * @return string
	 */
	public function BrowseLink() {
		$folder = $this->defaultFolder ? Folder::findOrMake($this->defaultFolder) : singleton('Folder');
		return Director::absoluteBaseURL() . "/admin/files/select/{$folder->ID}";
	}
	
	
	
	/**
	 * Generates a link to refresh the list of files
	 *
	 * @return string
	 */
	public function RefreshLink() {
		return $this->Link('refresh');
	}
	
	
	
	/**
	 * Generates a link to the upload script
	 *
	 * @return string
	 */
	public function UploadLink() {
		return $this->Link('upload');
	}
	
		
}