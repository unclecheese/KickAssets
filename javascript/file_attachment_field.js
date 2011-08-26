var $parentField;

(function($) {
$(function() {
	$('.file_attach_btn').livequery(function() {
		$(this).fancybox({
			'width'				: '90%',
			'height'			: '98%',
	        'autoScale'     	: false,
			'padding'			: 0,
	        'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'type'				: 'iframe',
			'onStart'			: function(btn) {
				$parentField = $(btn).closest('.FileAttachmentField');
			},
			'onCleanup'			: function() {
				var ids = $('#fancybox-frame').contents().find('#selected_files').text();
				if(!ids.length) return;
				
				$parentField.closest('.FileAttachmentField').find('.attached_files').load(
					$parentField.attr('data-refreshlink'),
					{ 'ids' : ids.split(',') }
				);
				$parentField = null;				
			}
		})
	});
	
	$('.detach_btn').live("click",function() {
		var $div = $(this).closest('.FileAttachmentField');
		var id = $(this).attr('data-id');
		if($div.is('.multi')) {
			$div.find('input[value='+id+']').remove();
			$(this).closest('.file_block').remove();
		}
		else {
			$div.find('.file_drop').addClass('empty').css('background-image','none');
			$div.find('.file_name').empty();
			$(this).remove();
			$div.find('input[value='+id+']').val(0);			
		}
		return false;
	});
	
	
	
	

    var dnd = {
      ready : function() {        
        $('.file_drop').live('dragenter', function(e) {
              e.preventDefault();
              e.stopPropagation();
			  $(this).addClass('over');
         }).live('dragover', function(e) {
			  e.preventDefault();
			  e.stopPropagation();
		 }).live('drop', function(e) {
			  if(e.originalEvent.dataTransfer) {
	              if (e.originalEvent.dataTransfer.files.length) {

					var files = e.originalEvent.dataTransfer.files;
					var $t = $(this);
			        var http = new XMLHttpRequest();
			  		if($t.closest('.FileAttachmentField').is('.single') && files.length > 1) {
						return false;
					} 
					var $progressBar = $('#progress');

					var uploadTimeout = window.setTimeout(function() {
					$progressBar.parent().css('visibility', 'visible');
					// Update progress bar
					http.upload.addEventListener("progress", function (evt) {						
						if (evt.lengthComputable) {
							$progressBar.css('width', (evt.loaded / evt.total) * 100 + "%");
						}
						else {
						}
					}, false);
					},1000);
					var url = $t.attr('data-uploadurl');

					http.addEventListener("load", function () {
						window.clearTimeout(uploadTimeout);
						$progressBar.parent().css('visibility','hidden');
						if(http.status != "200") {
							alert(http.responseText);
						}
						else {
							var $div = $t.closest('.FileAttachmentField')
							$div.find('.attached_files').load(
								$div.attr('data-refreshlink'),
								{ 'ids' : http.responseText.split(',')}
							);
						}
					}, false);


			        if (typeof(FormData) != 'undefined') {
			          var form = new FormData();

			          for (var i = 0; i < files.length; i++) {
			            form.append('file[]', files[i]);
			          }

			          http.open('POST', url);
			          http.send(form);
			        } 
					else {
			          alert('Your browser does not support standard HTML5 Drag and Drop');
			        }

	                e.preventDefault();
	                e.stopPropagation();
					$(this).removeClass('over');
	              }
			   }	
          }).live('dragleave', function(e) {
				e.preventDefault();
				e.stopPropagation();
				$(this).removeClass('over');
		  });
      },



	};
	
	$('.file_drop').livequery(dnd.ready());

})
})(jQuery);