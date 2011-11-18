<?php
/**
 * An administrative controller for managing files and images in the CMS.
 * Serves to replace the role of {@link AssetAdmin}
 *
 * @package KickAssets
 * @author UncleCheese <unclecheese@leftandmain.com>
 */
class KickAssetAdmin extends LeftAndMain implements PermissionProvider {
	
	
	
	/**
	 * @var string The URL segment of the controller, preceded by "admin." Careful,
	 *				don't change this. Right now the JS expects a value of "files"
	 */	
	static $url_segment = "files";
	
	
	
	/**
	 * @var string A label for the CMS menu button. 
	 * @todo This should be i18n.
	 */	
	static $menu_title = "Browse files...";



	/**
	 * @var array The allowed actions on this controller
	 */	
	static $allowed_actions = array (
		'browse',
		'select',
		'upload',
		'newfolder',
		'updatefoldername',
		'updatefilename',
		'move',
		'delete',
		'editfile',
		'FileEditForm',
		'replace',
		'updateview'
	);
	
	
	
	/**
	 * @var array A lookup of file extensions that define an image.
	 */	
	static $image_extensions = array (
		'jpg',
		'png',
		'gif'
	);



	/**
	 * @var int The size of the tool tip (width or height, which ever is greater)
	 */
	static $tooltip_size = 300;
	

	
	/**
	 * @var Folder The folder on which to start by default
	 */		
	protected $currentFolder;

	
	
	/**
	 * @var boolean Determines if the interface will support "selection" of files, e.g.
	 *				attaching them to pages.
	 */	
	public $SelectMode = false;
	
	
	/*
	 * @var boolean Determines if we're showing an edit form on load
	 *
	 */
	public $editMode = false;
	
	
	
	/**
	 * Loads the requirements, checks perms, etc. If an ID is in the URL, that becomes the
	 * current folder.
	 */	
	public function init() {
		parent::init();
		if(!Permission::check("ADMIN") && !Permission::check("CMS_ACCESS_BrowseFiles")) {
			return Security::permissionFailure($this, _t('KickAssets.PERMISSIONFAIL','You do not have permission to access this section of the CMS.'));
		}
		Requirements::clear();
		Requirements::css('kickassets/css/core.css');
		Requirements::css('kickassets/css/kickassets.css');
		Requirements::javascript('kickassets/javascript/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');
		Requirements::javascript('kickassets/javascript/apprise/apprise.js');
		Requirements::javascript('kickassets/javascript/jquery.tooltip.js');
		Requirements::css('kickassets/javascript/apprise/apprise.css');
		Requirements::javascript('kickassets/javascript/kickassets_ui.js');
		Requirements::javascript('kickassets/javascript/chosen/chosen.jquery.js');
		Requirements::css('kickassets/javascript/chosen/chosen.css');
		Requirements::javascript('kickassets/javascript/jquery.form.js');
		Requirements::javascript('kickassets/javascript/kickassets.js');
		Requirements::css('kickassets/css/kickassets_ui.css');


		if($this->getRequest()->param('ID')) {
			$this->currentFolder = DataObject::get_by_id("Folder", (int) $this->getRequest()->param('ID'));
			$this->currentPath = KickAssetUtil::relative_asset_dir($this->currentFolder->Filename);
		}
		else {
			$this->currentFolder = singleton('Folder');
			$this->currentPath = false;
		}
		
	}
	


	/**
	 * 
	 * The browse action is "select" if we're in select mode.
	 * @return
	 */
	protected function getBrowseAction() {
		return $this->SelectMode ? "select" : "browse";
	}

	
	
	/**
	 * Template accessor for the current folder
	 *
	 * @return Folder
	 */
	public function getCurrentFolder() {
		return $this->currentFolder;
	}
	
	
	
	/**
	 * Template accessor for the current folder name
	 *
	 * @return string
	 */
	public function getCurrentFolderName() {
		if($this->currentFolder) {
			return $this->currentFolder->Filename;
		}
		return ASSETS_DIR;
	}
	
	
	
	/**
	 * Template accessor for the current folder ID
	 *
	 * @return int
	 */
	public function getCurrentFolderID() {
		return $this->currentFolder ? $this->currentFolder->ID : 0;
	}
	

	
	/**
	 * By default, the controller forwards to the "browse" action 
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */	
	public function index(SS_HTTPRequest $r) {
		return Director::redirect($this->Link('browse'));
	}
	
	
	
	/**
	 * The select action just tells the controller that {@link KickAssetAdmin::$SelectMode} is on
	 * and proceeds to the browsing.
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */
	public function select(SS_HTTPRequest $r) {
		$this->SelectMode = true;
		return $this->browse($r);
	}
	
	
	
	/**
	 * Not much happens here -- mostly a placeholder method. If the request comes in as ajax
	 * we only need to render the file list.
	 *
	 * @param SS_HTTPRequest
	 * @return array
	 */
	public function browse(SS_HTTPRequest $r) {
		if(Director::is_ajax()) {
			return $this->renderWith('FileList');
		}
		return array();
	}
	
	
	
	/**
	 * Replaces a file with another while keeping a persistent ID.
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */
	public function replace(SS_HTTPRequest $r) {
		$file_array = $_FILES['file'];
		if(sizeof($file_array['tmp_name']) > 1) {
			return _t('KickAssets.PLEASEUPLOADONEFILE','Please upload only one file.');
		}
		$response = KickAssetUtil::handle_upload($r, $this->currentFolder);
		if(is_array($response)) {
			$new_id = reset($response);
		}
		if(($existing = DataObject::get_by_id("File", (int) $r->param('OtherID'))) && ($new = DataObject::get_by_id("File", (int) $new_id))) {
			$new->ID = $existing->ID;
			$new->setParentID($existing->ParentID);
			// We circumvent the ORM here so we don't destroy any of the objects
			DB::query("DELETE FROM File WHERE ID IN ($new_id, $existing->ID)");
			$new->forceChange();
			$id = $new->write();
			return new SS_HTTPResponse("OK");
		}
	}



	/**
	 * Handles the uploading of files. Most of the legwork is done in {@link KickAssetUtil}
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */
	public function upload(SS_HTTPRequest $r) {
		$response = KickAssetUtil::handle_upload($r, $this->currentFolder);
		if(is_array($response)) {
			return new SS_HTTPResponse("OK");
		}
		return $response;
	}
	

	
	/**
	 * Creates a new folder at the level of {@link KickAssetAdmin::$currentFolder}
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */
	public function newfolder(SS_HTTPRequest $r) {
		$f = new Folder();
		$f->ParentID = $this->currentFolder ? $this->currentFolder->ID : 0;
		$f->Name = "New-Folder";
		$f->Title = "New-Folder";
		$f->write();
		if(!file_exists($f->getFullPath())) {
			Filesystem::makeFolder($f->getFullPath());
		}
		return $this->browse($r);
	}
	
		
	
	/**
	 * Changes the name of a file
	 *
	 * @param SS_HTTPRequest
	 * @return SSViewer
	 */
	public function updatefilename(SS_HTTPRequest $r) {
		if($file = DataObject::get_by_id("File", (int) $r->requestVar('fileid'))) {
			$file->setName($r->requestVar('new'));
			$file->write();
			$template = $file instanceof Folder ? "Folder" : "File";
			return $this->customise($this->getFields($file))->renderWith($template);
		}

	}
	

	
	/**
	 * Moves a file from one folder to another.
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */
	public function move(SS_HTTPRequest $r) {
		if($r->requestVar('source') && is_array($r->requestVar('source')) && $r->requestVar('dest')) {    
			foreach($r->requestVar('source') as $id) {
				if($id == $r->requestVar('dest')) continue;
				
				if($file = DataObject::get_by_id("File", (int) $id)) {
					$file->ParentID = $r->requestVar('dest');
					$file->write();
				}
			}
			return $this->browse($r);
		}
	}
	


	/**
	 * Deletes a given list of files
	 *
	 * @param SS_HTTPRequest
	 * @return SS_HTTPResponse
	 */
	public function delete(SS_HTTPRequest $r) {
		if(is_array($r->requestVar('list'))) {
			$files = $r->requestVar('list');
			foreach($files as $id) {
				if($file = DataObject::get_by_id("File", (int) $id)) {
					$file->delete();
				}
			}
			return $this->browse($r);
		}
	}



	/**
	 * Handles the edit of a file given an ID
	 *
	 * @param SS_HTTPRequest
	 * @return SSViewer
	 */
	public function editfile(SS_HTTPRequest $r) {
		if($file = DataObject::get_by_id("File", (int) $r->param('OtherID'))) {
			$form = $this->FileEditForm($file);
			$form->loadDataFrom($file);
			return $this->customise(array(
				'Form' => $form
			))->renderWith('EditFields');
		}
	}
	
	
	
	/**
	 * Changes the layout of the files. Store in session so it persists throughout
	 * other implementations of this window, e.g. {@link FileAttachmentField}
	 *
	 * @param SS_HTTPRequest
	 */
	public function updateview(SS_HTTPRequest $r) {
		if($r->requestVar('view') == "grid") {
			Session::set("KickAssetAdmin.VIEW","grid");

		}
		elseif($r->requestVar('view') == "gallery") {
			Session::set("KickAssetAdmin.VIEW","gallery");
		}
		return new SS_HTTPResponse(Session::get("KickAssetAdmin.VIEW"),200);		
	}

	

	/**
	 * Creates the edit form for a file given a File object.
	 * 
	 * @todo This is a bit ugly -- the first parameter is a SS_HTTPRequest on POST, but
	 *		 otherwise a File. 
	 * @param SS_HTTPRequest|File Ambiguous first paramteter!
	 * @return Form
	 */
	public function FileEditForm($file = null) {
		$fields = new FieldSet();
		if($file instanceof File) {
			$fields = $this->getFieldsForFile($file);
		}
		elseif($this->getRequest()->requestVar('ID')) {
			if($file = DataObject::get_by_id("File", (int) $this->getRequest()->requestVar('ID'))) {
				$fields = $this->getFieldsForFile($file);
			}
		}
		$f = new Form (
			$this,
			"FileEditForm",
			$fields,
			new FieldSet (
				$save = new FormAction('doFileSave', _t('KickAssetts.SAVE','Save')),
				$cancel = new FormAction('doFileCancel', _t('KickAssetts.CANCEL','Cancel'))
			)
		);
		$save->useButtonTag = true;
		$cancel->useButtonTag = true;
		$save->addExtraClass("btn primary");
		$cancel->addExtraClass("btn");
		return $f;
	}
	
	
	
	/**
	 * Generate a link to delete a file. The File ID comes off the element's metadata. 
	 *
	 * @return string
	 */	
	public function DeleteLink() {
		return $this->Link('delete/'.$this->getCurrentFolderID());
	}
	
	
	
	/**
	 * Generate a link to create a new folder 
	 *
	 * @return string
	 */
	public function NewFolderLink() {
		return $this->Link('newfolder/'.$this->getCurrentFolderID());
		
	}
	
	
	
	/**
	 * A template accessor for the tooltip size.
	 *
	 * @return string
	 */
	public function TooltipSize() {
		return self::$tooltip_size;
	}
	
	
	
	/**
	 * Get the list of Folder objects under the current folder
	 *
	 * @return DataObjectSet
	 */
	public function Folders() {
		$set = DataObject::get("Folder","ParentID = {$this->getCurrentFolderID()}");
		if(!$set) return false;
		
		$ret = new DataObjectSet();
		foreach($set as $folder) {
			$ret->push(new ArrayData($this->getFields($folder)));
		}
		return $ret;
	}



	/**
	 * Get the File objects under the current folder
	 *
	 * @return DataObjectSet
	 */
	public function Files() {		
		$set = DataObject::get("File","ClassName != 'Folder' AND ParentID = {$this->getCurrentFolderID()}");
		if(!$set) return false;
		$ret = new DataObjectSet();
		foreach($set as $file) {
			$ret->push(new ArrayData($this->getFields($file)));
		}
		return $ret;

	}
	


	/**
	 * Determines if this is the highest level folder (e.g. assets/)
	 *
	 * @return boolean
	 */
	public function TopLevel() {
		return $this->getCurrentFolderID() == 0;
	}
	
	
	
	/**
	 * Generate a link to the parent folder
	 *
	 * @todo Maybe the folders should be breadcrumbs?
	 * @return string
	 */
	public function ParentLink() {
		return $this->Link($this->getBrowseAction().'/'.$this->currentFolder->ParentID);
	}
	
	
	/**
	 * Generate linked breadcrumbs for the folder hierarchy
	 *
	 * @return string
	 */
	public function BreadCrumbs() {
		$folder = $this->currentFolder;
		$breadcrumbs = array();
		while($folder->ID) {
			$breadcrumbs[$folder->Name] = $this->Link($this->getBrowseAction()."/".$folder->ID);
			$folder = $folder->Parent();
		}
		$breadcrumbs[ASSETS_DIR] = $this->Link($this->getBrowseAction()."/0");
		$list = array_reverse($breadcrumbs, true);
		$ret = "";
		foreach($list as $name => $link) {
			$ret .= " / <a href='$link'>$name</a>";
		}
		return substr_replace($ret, "",0,3);
		
	}
	
	
	
	/**
	 * Generate a link to upload to this controller
	 *
	 * @return string
	 */
	public function UploadLink() {
		return Director::absoluteBaseURL().$this->Link('upload/'.$this->getCurrentFolderID());
	}
	
	
	public function GalleryLink() {
		return Controller::join_links($this->Link(),'updateview', '?view=gallery');
	}


	public function GridLink() {
		return Controller::join_links($this->Link(),'updateview', '?view=grid');
	}
	
	
	
	public function CurrentView($view) {
		return $view == $this->View();
	}
	
	
	public function View() {
		if($view = Session::get("KickAssetAdmin.VIEW")) {
			return $view;
		}
		return "gallery";
	}

	
	/**
	 * Creates the permissions for using this interface {@see PermissionProvider} 
	 *
	 * @return array
	 */	
	public function providePermissions() {
		return array (
			'CMS_ACCESS_BrowseFiles' => 'Browse and upload files'
		);
	}


	
	/**
	 * Adds some metadata to a {@link File} object that is used by the view. This function
	 * allows us to avoid having to use a decorator on the {@link File} class.
	 *
	 * @param File
	 * @return array
	 */
	protected function getFields(File $f) {
		if($f instanceof Folder) {
			return array (
				'Link' => $this->Link($this->getBrowseAction().'/'.$f->ID),
				'Item' => $f				
			);
		}
		$image = ($f instanceof Image);
		$tooltipurl = "";
		if($image) {
			if($f->getHeight() > 64 || $f->getWidth() > 64) {
				if($f->getOrientation() == Image::ORIENTATION_SQUARE || $f->getOrientation() == Image::ORIENTATION_LANDSCAPE) {
						$tooltipurl = $f->getWidth() > self::$tooltip_size ? $f->SetWidth(self::$tooltip_size)->getURL() : $f->getURL();
				}
				else {
						$tooltipurl = $f->getHeight() > self::$tooltip_size ? $f->setHeight(self::$tooltip_size)->getURL() : $f->getURL();
				}	
			}
		}
		return array(
			'Link' => '',	
			'Item' => $f,
			'IconURL' => $image ? (($i = $f->SetHeight(64)) ? $i->getURL() : KickAssetUtil::get_icon($f)) : KickAssetUtil::get_icon($f),
			'Image' => $image,
			'TooltipURL' => $tooltipurl
		);

		
	}
	
	
	
	/**
	 * For the edit form, we don't want the default scaffolding because it adds a lot of
	 * noise. Allow for the decorator pattern by providing and extend() to updateCMSFields.
	 *
	 * @param File
	 * @return FieldSet
	 */
	protected function getFieldsForFile($file) {
		$map = array();
		$filename = "";
		if($file && $file instanceof File) {
			$filename = $file->Filename;
		}
		if($set = DataObject::get("Member")) {
			$map = $set->toDropdownMap();
		}
		$fields = new FieldSet (
			new TextField('Name', _t('KickAssets.NAME','Name')),
			new TextField('Title', _t('KickAssets.TITLE','Title')),
			$folders = new KickAssetAdmin_FolderDropdownField('ParentID', _t('KickAssets.MOVETOFOLDER','Move to folder')),
			$owner = new DropdownField('OwnerID',_t('KickAssets.OWNER','Owner'), $map),
			new LiteralField('ReplaceFileText','<h4>'._t('KickAssets.REPLACEFILE','Replace file').'</h4><div id="replace-file" data-uploadurl="'.$this->Link("replace/{$this->getCurrentFolderID()}/{$file->ID}").'">'.$filename.'</div>'),
			new HiddenField('ID','')
		);
		$owner->setEmptyString('('._t('KickAssets.NONE','None').')');
		$folders->setEmptyString('(root)');
		if($file->hasMethod('updateCMSFields')) {
			if(version_compare(PHP_VERSION, '5.3') >= 0) {
				$file->updateCMSFields(&$fields);	
			}
			else {
				$file->updateCMSFields($fields);	
			}
			
		}
		return $fields;
	}



	/**
	 * Handles saving the edited file.
	 *
	 * @param array The form data that was posted
	 * @param Form The form object used
	 * @return SS_HTTPResponse
	 */
	public function doFileSave($data, $form) {
		if($file = DataObject::get_by_id("File", (int) $data['ID'])) {
			$form->saveInto($file);
			$file->write();
			return new SS_HTTPResponse("OK", 200);
		}		
	}
	
	
	
}



/**
 * In an effort to avoid coupling with the {@link DataObjectManager} package,
 * the {@link SimpleTreeDropdownField} has been ported over with some minor changes.
 *
 * @package KickAsssets
 * @author UncleCheese <unclecheese@leftandmain.com>
 */
class KickAssetAdmin_FolderDropdownField extends DropdownField {
	
	
	
	protected $parentID, $filter;

	
	
	/**
	 * Assigns the parent ID to the form field. The parent ID is the level of hierarchy where
	 * the tree will start.
	 *
	 */
	function __construct($name, $title = "", $value = "",  $form = null, $emptyString = null, $parentID = 0) {
		$this->parentID = $parentID;
		parent::__construct($name, $title, null, $value, $form, $emptyString);
	}

	
	
	/**
	 * Sets a filter clause for the tree.
	 *
	 * @param string
	 */
	public function setFilter($filter) {
		$this->filter = $filter;
	}



	/**
	 * Returns the source of the tree, e.g. a list of ID/Title pairs with hierarchical indenation
	 *
	 * @return array
	 */
	function getSource() {
		if (!$this->source) {
			$this->source = $this->getHierarchy((int)$this->parentID);
		}
		return parent::getSource();
	}


	
	/**
	 * A recursive function that generates the hierarchy of folders.
	 *
	 * @param int The current parentID (level of hierarchy)
	 * @param level Determines how far to indent the text
	 * @return array
	 */
	private function getHierarchy($parentID, $level = 0) {
		$options = array();
		$filter = ($this->filter) ? "\"ParentID\" = $parentID AND $this->filter" : "\"ParentID\" = $parentID";
		if($children = DataObject::get("Folder", $filter)) {
			foreach($children as $child) {
				$indent="";
				for($i=0;$i<$level;$i++) $indent .= "__";
				$text = $child->Name;
				$options[$child->ID] = $indent.$text;		
				$options += $this->getHierarchy($child->ID, $level+1);
			}
		}
		return $options;
	}
		
}