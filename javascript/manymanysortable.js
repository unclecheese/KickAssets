jQuery(document).ready(function(){
	
	/**
	 * Sort Fields in the Field List
	 */
	jQuery("#sortable").livequery(function(){
		
		jQuery(this).sortable({
			handle : '.fieldHandler',
			items: 'li.sortableli',
			opacity: 0.6,
			revert: true,
			change : function (event, ui) {
				jQuery("#sortable").sortable('refreshPositions');
			},
	    	update : function (event, ui) {
	      		// get all the fields
				var sort = 1;
				jQuery("li.sortableli").each(function() {
					jQuery(this).find(".sortHidden").val(sort++);
				});
	    	}
		});
	});

});