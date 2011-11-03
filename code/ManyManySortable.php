<?php

/**
 * Adds many_many sortable to a relationship
 * Add the following example to your _config with the 
 * The key of the array is the Object that has the many_many relationship set on it.
 * The value is another array in which the 1st element is the Relationship name and the 2nd element is the sort order.
 *
 *	Example: ManyManySortable::add_sortable_many_many_relations(array('ParentManyMany' => 'Relationship'));
 * 
 * The idea here is that the DataObject that has the many_many relationship is decorated. This way we can add the sorting 
 * to the relationship table and not add anyting to the sorted DataObject since this needs to work with File and Image objects
 * instead of creating a new DataObject that has the File as a has_one and decorating that object the way SortableDataObject works.
 * 
 * The method ManyManySorted() can be used on the many_many Parent object to get the related Files or Images in the correct order
 * as set in the database in the ManyManySort column.
 * 
 * @package KickAssets
 * @author UncleCheese <unclecheese@leftandmain.com>
 * @author Micah Sheets <micah@soniceyetec.biz>
 */

class ManyManySortable extends DataObjectDecorator {
	
	static $many_many_sortable_relations = array();
	static $sort_dir = "ASC";
	
	public static function set_sort_dir($dir) {
		self::$sort_dir = $dir;
	}
	
	/**
	 * Used in _config to set up any many_many sortable relationships.
	 * 
	 * @param array where $key is the DataObject with the many_many relationships set on it 
	 * 			and the $value is the name of the relationship.
	 */
	public static function add_sortable_many_many_relations(array $classes) {
		foreach($classes as $id => $value)
			$ownerClass = $id;
			$componentName = $value;
			DataObject::add_extension($ownerClass,'ManyManySortable');
			self::add_sortable_many_many_relation($ownerClass,$componentName);
	}
	
	/**
	 * Adds the ManyManySort column in the database table for the relationship.
	 * Adds the parent many_many and relation name to $many_many_sortable_relations so
	 * they can be used in ManyManySorted()
	 * 
	 * @param DataObject with many_many
	 * @param Relationship name
	 */
	public static function add_sortable_many_many_relation($ownerClass,$componentName) {
	    Object::add_static_var($ownerClass,'many_many_extraFields',array(
	      $componentName => array(
	        'ManyManySort' => 'Int'
	     )));
		 self::$many_many_sortable_relations[$ownerClass]['relationName'] = $componentName;
	}
	
	/**
	 * Used in decorated classes to access the ManyManySorted objects.
	 * 
	 * @param string either 'ASC' or 'DESC'
	 * 
	 */
	function ManyManySorted($sortdir = null) {
		$sortDirection = ($sortdir) ? $sortdir : self::$sort_dir;
		$functionname = self::$many_many_sortable_relations[$this->owner->ClassName]['relationName'];
		return $this->owner->$functionname(null, 'ManyManySort '.$sortDirection);
	}
}