<?php

/**
 * Provides an interface for attaching a single image that is associated with
 * a Page or DataObject. Files can be chosen from exting assets in {@link KickAssetAdmin}
 *
 * @package KickAssets
 * @author Luke Hudson <lukeletters@gmail.com>
 * @author UncleCheese <unclecheese@leftandmain.com>
 */
class ImageAttachmentField extends FileAttachmentField {
	

	public function __construct($name, $title = null, $value = null, $form = null, $rightTitle = null){
		parent::__construct($name, $title, $value, $form, $rightTitle);
		$this->setLimitFileType('Image');
	}

	/**
	 * Sets the value of the form field based on data from the request. Automatically
	 * adds the "ID" to the field name. Assumes a $has_one relation to the file.
	 *
	 * @param string The value to set
	 * @param array The array of form data
	 */
	public function setValue($value = null, $data = null) {
		if(!is_numeric($value)) {
			if($id = Controller::curr()->getRequest()->requestVar($this->Name()."ID")) {
				$value = $id;
			}
			elseif(!$value && $data && $data instanceof DataObject && $data->hasMethod($this->name)) {
				$funcName = $this->name;
				if($obj = $data->$funcName()) {
					if($obj instanceof Image) {
						$value = $obj->ID;
					}
				}
			}
		}
		parent::setValue($value, $data);
	}


	
	/**
	 * Refreshes the file list. If passed an array of ids, it will add those to the list.
	 *
	 * @todo Add some better error handling.
	 * @param SS_HTTPRequest
	 * @return string|SS_Viewer
	 */
	public function refresh(SS_HTTPRequest $r) {
		if($r->requestVar('ids')) {
			$id = reset($r->requestVar('ids'));
			if($file = DataObject::get_by_id("Image", (int) $id)) {
				$this->processFile($file);
			}
			else {
				die("Image $id doesn't exist");
			}
		}
		else {
			$file = $this->File();
		}
		return $this->customise(array(
			'File' => $file
		))->renderWith($this->AttachedFilesTemplate);
	}	
	
	
	
	/**
	 * Gets all the attached files. This should only return one file, but we return
	 * a {@link DataObjectSet} in order to maintain a single template
	 *
	 * @return DataObjectSet
	 */
	public function File() {
		if($val = $this->Value()) {
			if($file = DataObject::get_by_id("Image", (int) $val)) {
				$this->processFile($file);
				return $file;
			}
		}
		return false;
	}

	

	/**
	 * Saves the form data into a record. The {@see $name} property of the object is used
	 * to determine the foreign key on the record, e.g. "SomeFileID".
	 *
	 * @param DataObject $record The record associated with the parent form
	 */
	public function saveInto(DataObject $record) {
		if(isset($_REQUEST[$this->name."ID"])) {
			$file_id = (int) $_REQUEST[$this->name."ID"];
			if($file_class = $record->has_one($this->name)) {
				if($f = DataObject::get_by_id("Image", $file_id)) {
					if($f->ClassName != $file_class) {
						$file = $f->newClassInstance($file_class);
						$file->write();
					}
				}
			}
			$record->{$this->name . 'ID'} = $_REQUEST[$this->name."ID"];
		}
	}
	
	
}