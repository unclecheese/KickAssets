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
			'titleShow'			: false,
			'type'				: 'iframe',
			'onStart'			: function(btn) {
				$parentField = $(btn).closest('.FileAttachmentField');
			},
			'onCleanup'			: function() {
				var ids = $('#fancybox-frame').contents().find('#selected_files').text();
				if(!ids.length) return;
				
				var $wrap = $parentField.closest('.FileAttachmentField');
				// make sure we don't lose any unsaved files.
				if($wrap.is('.multi')) {
					$wrap.find('.file_block').each(function() {
						ids += ',';
						ids += $(this).find(':hidden').val();
					})
				}
				$wrap.find('.attached_files').load(
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
		showNoFile(false);
		return false;
	});
	
	$('.file_attach_upload').live("change", function(e) {
		doUpload($(this), e.target.files, $(this).attr('data-uploadurl'));
        e.preventDefault();
        e.stopPropagation();
	}).live("mouseenter", function() {
		$(this).siblings('.file_upload_btn').addClass('over');
	}).live("mouseleave", function() {
		$(this).siblings('.file_upload_btn').removeClass('over');
	});
	
	
	var showNoFile = function (cancel) {
		$('.controls_has_file').fadeOut(function() {
			if(cancel) {
				$('.file_cancel_btn').show();
			}
			$('.controls_no_file').fadeIn();
		})				
	};
	
	
	var showHasFile = function (cancel) {
		$('.controls_no_file').fadeOut(function() {
			$('.file_cancel_btn').hide();
			$('.controls_has_file').fadeIn();
		})		
	}
	
	$('.replace_btn').live("click", function() {showNoFile(true)});
	
	$('.file_cancel_btn').live("click", showHasFile);

	
	$('.delete_btn').live("click", function() {
		if(window.confirm($(this).attr('data-confirmtext'))) {
			var $t = $(this);
			$.get($t.attr('href'), function() {
				$t.siblings('.detach_btn').click();
			})
		}
		return false;
	});
	

	var doUpload = function($t, files, url) {
        var http = new XMLHttpRequest();
  		if($t.closest('.FileAttachmentField').is('.single') && files.length > 1) {
			return false;
		} 
		var $progressBar = $t.closest('.FileAttachmentField').find('.progress');

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

	}

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
					doUpload($t, files, $t.attr('data-uploadurl'));
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
	
	$('.file_drop').livequery(dnd.ready);

})
})(jQuery);