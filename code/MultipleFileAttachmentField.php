<?php
/**
 * Provides an interface for attaching multiple files associated with
 * a Page or DataObject. Files can be chosen from exting assets in {@link KickAssetAdmin}
 *
 * @package KickAssets
 * @author UncleCheese <unclecheese@leftandmain.com>
 */

class MultipleFileAttachmentField extends KickAssetField {


	
	/**
	 * @var boolean A simple template variable that states whether this is a multiple
			 		upload field or not
	 */	
	public $Multi = true;
	
	

	/**
	 * @var string The template used to render the file list
	 */
	public $AttachedFilesTemplate = "KickAssetFieldFiles_Multi";	
	


	/**
	 * Sets the value of the form field based on data from the request. Gets the list
	 * of file IDs by running the $name() against the object. File relation can be
	 * has_many or many_many.
	 *
	 * @param string The value to set
	 * @param array The array of form data
	 */	
	public function setValue($value = null, $data = null) {
		if(!is_array($value)) {
			if(!$value && $data && $data instanceof DataObject && $data->hasMethod($this->name)) {
				$funcName = $this->name;
				if($obj = $data->$funcName()) {
					if($obj instanceof DataObjectSet) {
						$value = $obj->column('ID');
					}
				}
			}
		}
		parent::setValue($value, $data);
	}



	/**
	 * Refreshes the file list. If passed an array of IDs in the request, 
	 * it augments the list with those files.
	 *
	 * @param SS_HTTPRequest
	 * @return SSViewer
	 */	
	public function refresh(SS_HTTPRequest $r) {
		if($r->requestVar('ids')) {
			$ids = $r->requestVar('ids');
			$files = new DataObjectSet();
			if($set = DataObject::get("File", "\"File\".ID IN (".implode(',',$ids).")")) {
				foreach($set as $file) {
					self::process_file($file);
					$files->push($file);					
				}
				$files->merge($this->Files());
			}
			else {
				die("File $id doesn't exist");
			}
		}
		else {
			$files = $this->Files();
		}
		return $this->customise(array(
			'Files' => $files
		))->renderWith($this->AttachedFilesTemplate);
	}



	/**
	 * Gets all the attached files.
	 *
	 * @return DataObjectSet
	 */
	public function Files() {
		if($val = $this->Value()) {
			if(is_array($val)) {
				$list = implode(',', $val);
				if($files = DataObject::get("File", "\"File\".\"ID\" IN (".Convert::raw2sql($list).")")) {
					$ret = new DataObjectSet();
					foreach($files as $file) {
						self::process_file($file);
						$ret->push($file);
					}
					return $ret;
				}
			}
		}
		return false;
	}



	/**
	 * Saves the form data into a record. Nulls out all of the existing file relationships
	 * and rebuilds them, in order to accommodate any deletions.
	 *
	 * @param DataObject $record The record associated with the parent form
	 */
	public function saveInto(DataObject $record) {
		// Can't do has_many without a parent id
		if(!$record->isInDB()) {
			$record->write();
		}
		if(!$file_class = $this->getFileClass($record)) {
			return false;
		}
		if(isset($_REQUEST[$this->name]) && is_array($_REQUEST[$this->name])) {
			if($relation_name = $this->getForeignRelationName($record)) {
				// Null out all the existing relations and reset.
				$currentComponentSet = $record->{$this->name}();
				$currentComponentSet->removeAll();
				// Assign all the new relations (may have already existed)
				foreach($_REQUEST[$this->name] as $id) {
					if($file = DataObject::get_by_id("File", $id)) {
						$new = ($file_class != "File") ? $file->newClassInstance($file_class) : $file;
						$new->write();
						$currentComponentSet->add($new);
					}
				}
			}
		}		
	}


	
	/**
	 * Gets the name of the relation pointing back to this object. For example, if using
	 * a has_many relation to files, this function determines the name of the $has_one
	 * on the File object that points to the current record.
	 *
	 * @param DataObject $record
	 * @return string|bool
	 */
	public function getForeignRelationName(DataObject $record) {		
		if ($many_info = $record->many_many($this->name)) {
			// return parent field
			return $many_info[2];
		} 
		elseif ($file_class = $record->has_many($this->name)) {
			$class = $record->class;
			$relation_name = false;
			while($class != "DataObject") {
				if($relation_name = singleton($file_class)->getReverseAssociation($class)) {
					break;
				}
				$class = get_parent_class($class);					
			}
			if(!$relation_name) {
				user_error("Could not find has_one or belongs many_many relationship on $file_class", E_USER_ERROR);
			}

			return $relation_name .= "ID";
		}
		return false;
	}
	


	/**
	 * Gets the class of the file being managed. Used in case the relation
	 * is cast as a subclass of File.
	 *
	 * @param DataObject $record
	 * @return string|bool
	 */	
	public function getFileClass (DataObject $record) {
		if(!$file_class = $record->has_many($this->name)) {
			if (!$many_class = $record->many_many($this->name)) {
				return false;
			}
			// set child class. 
			$file_class = $many_class[1];
		}
		return $file_class;
	}
		

}