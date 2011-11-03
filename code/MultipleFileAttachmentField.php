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
	 *	 		upload field or not
	 */	
	public $Multi = true;
	

	/**
	 * @var string The template used to render the file list
	 */
	public $AttachedFilesTemplate = "KickAssetFieldFiles_Multi";	
	
	/**
	 * @See Pull Request by Micah Sheets
	 * FieldHolder overriden here so we can add javascript required for manymanysortable
	 */
	public function FieldHolder() {
		if ($this->getForm()->getRecord()->hasExtension('ManyManySortable')){
			Requirements::javascript(SAPPHIRE_DIR ."/thirdparty/jquery-ui/jquery-ui-1.8rc3.custom.js");
			Requirements::javascript('kickassets/javascript/manymanysortable.js');
		}
		return parent::FieldHolder();
	}

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
			$ids = array_unique($r->requestVar('ids'));
			$files = new DataObjectSet();
			$implodestring = implode(',',$ids);
			$implodestring = preg_replace("/^[,]/", "", $implodestring);
			if($set = DataObject::get("File", "`ID` IN ($implodestring)")) {
				foreach($set as $file) {
					$this->processFile($file);
					$files->push($file);					
				}
				$files->merge($this->Files());
				$files->removeDuplicates();
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
	 * 
	 * @See Modified for pull request by Micah Sheets to add manymanysortable.
	 */
	public function Files() {
		$many_many_parent = $this->getForm()->getRecord();
		if($val = $this->Value()) {
			if(is_array($val)) {
				$list = implode(',', $val);
				
				if ($many_many_parent->hasExtension('ManyManySortable')) {
					$files = $many_many_parent->ManyManySorted();
				}
				else {
					$files = DataObject::get("File", "\"File\".\"ID\" IN (".Convert::raw2sql($list).")");
				}
				if($files->Count() > 0) {
					$ret = new DataObjectSet();
					foreach($files as $file) {
						$this->processFile($file);
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
	 * 
	 * @See Modified for pull request by Micah Sheets to add manymanysortable
	 */
	public function saveInto(DataObject $record) {
		// Can't do has_many without a parent id
		if(!$record->isInDB()) {
			$record->write();
		}
		if(!$file_class = $this->getFileClass($record)) {
			return false;
		}
		// Null out all the existing relations and reset.
		$currentComponentSet = $record->{$this->name}();
		$currentComponentSet->removeAll();

		if(isset($_REQUEST[$this->name]) && is_array($_REQUEST[$this->name])) {
			if($relation_name = $this->getForeignRelationName($record)) {
				// Assign all the new relations (may have already existed)
				$data = $_REQUEST;
				for($count = 0; $count < count($data[$this->name]); ++$count) {
					$id = $data[$this->name][$count];
					$sort = $data['sort'][$count];
					if($file = DataObject::get_by_id("File", $id)) {
						$new = ($file_class != "File") ? $file->newClassInstance($file_class) : $file;
						$new->write();
						$currentComponentSet->add($new, array('ManyManySort'=>$sort));
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