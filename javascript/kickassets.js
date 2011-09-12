(function($) {
	var refreshFiles = function() {
		$('#drop').load(window.location.href);
	};
	
	
    var dnd = {
      ready : function()
      {        
        $('#drop, #replace-file')
          .live(
            'dragenter',
            function(e) {
              e.preventDefault();
              e.stopPropagation();
			  $(this).addClass('over');
            }
          )
		  .live(
				'dragover',
				function(e) {
					e.preventDefault();
					e.stopPropagation();
				}
			
			)
          .live(
            'drop',
            function(e) {
			  if(e.originalEvent.dataTransfer) {
	              if (e.originalEvent.dataTransfer.files.length) {
	                dnd.upload(e.originalEvent.dataTransfer.files, $(this).data('uploadurl'));
	                e.preventDefault();
	                e.stopPropagation();
					$(this).removeClass('over');
	              }
			   }	
            }
          )
		  .live(
		  	'dragleave',
			function(e) {
				e.preventDefault();
				e.stopPropagation();
				$(this).removeClass('over');
			}
		  );
		
		  
      },

      upload : function(files,url)
      {
        // This is a work-around for Safari occaisonally hanging when doing a 
        // file upload.  For some reason, an additional HTTP request for a blank
        // page prior to sending the form will force Safari to work correctly.

//        $.get('file/blank.html');

        var http = new XMLHttpRequest();
		// var progressBar = document.getElementById('progress');
		// 
		// // Update progress bar
		// http.upload.addEventListener("progress", function (evt) {
		// 	if (evt.lengthComputable) {
		// 		progressBar.style.width = (evt.loaded / evt.total) * 100 + "%";
		// 	}
		// 	else {
		// 	}
		// }, false);
		
		http.addEventListener("load", function () {
			if(http.responseText != "OK") {
				apprise(http.responseText);
			}
			refreshFiles();
			if($('#drop').is('.open')) {
				var id = $('#Form_FileEditForm').find('[name=ID]').val();
				$('li[data-id='+id+'] img').dblclick();
			}
		}, false);


        if (typeof(FormData) != 'undefined') {
          var form = new FormData();


          for (var i = 0; i < files.length; i++) {
            form.append('file[]', files[i]);
          }

          http.open('POST', url);
          http.send(form);
        } else {
          alert('Your browser does not support standard HTML5 Drag and Drop');
        }
      },

      getFileSize : function(bytes)
      {
        switch (true) {
          case (bytes < Math.pow(2,10)): {
            return bytes + ' Bytes';
          };
          case (bytes >= Math.pow(2,10) && bytes < Math.pow(2,20)): {
            return Math.round(bytes / Math.pow(2,10)) +' KB';
          };
          case (bytes >= Math.pow(2,20) && bytes < Math.pow(2,30)): {
            return Math.round((bytes / Math.pow(2,20)) * 10) / 10 + ' MB';
          };
          case (bytes > Math.pow(2,30)): {
            return Math.round((bytes / Math.pow(2,30)) * 100) / 100 + ' GB';
          };
        }
      }
    };
	 
$(document).ready(dnd.ready);
$(document).ready(function() {
//	$('#drop, #replace-file').livequery(dnd.ready);
//	$('#drop').css('height',$('body').height()-20+'px');
	var toRename;
	
	$('#filesystemsync').click(function() {
		$.get($(this).attr('href'), function(data) {
			apprise(data);
		});
		return false;
	});
	
	$('#newfolder').click(function() {
		var $t = $(this);
		$('#drop').load($t.attr('href'));
		return false;
	});
	
	$('.editable').live("dblclick", function(e) {
		if(e.target.tagName == "INPUT") {e.target.select();return}
		var $t = $(this);
		$t.closest('li').addClass('ui-selected');
		toRename = $t.text();
		var $i = $('<input type="text" value="'+toRename+'" />');
		$t.html($i);
		$i.focus();
		$i.select();


	});
	
	$('.editable input').live("blur", function(e) {
		if(!e.currentTarget.tagName == "INPUT") return;
		var newName = $(this).val();
		var $t = $(this);
		var $li = $t.closest('li');
		$li.removeClass('ui-selected');
		$li.load(
			$('base').attr('href')+"admin/files/updatefilename",
			{	
				'new' : newName, 
				'fileid' : $li.data('id')
			}
		);
	}).live("keyup", function(e) {
		if(e.keyCode == 13) {
			$(this).focusout();
		}
	});
	

	
	$('#drop').livequery(function() {$(this).selectable({
		cancel : '.editable,input',
		filter : 'li',
		selected : function() {
			if($('.ui-selected').length > 1) {
				$('a.togglestate.single').addClass('disabled');				
				$('a.togglestate:not(.single)').removeClass('disabled');
			}
			else {
				$('a.togglestate').removeClass('disabled');
			}

		},
		unselected : function() {
			$('a.togglestate').addClass('disabled');
		}
	});});
	
	$('#directory_list li').livequery(function() {$(this).draggable({
	  delay: 500,
	  distance: 30,
	  helper: function(){
	    var selected = $('#directory_list .ui-selected');
	    if (selected.length === 0) {
	      selected = $(this);
	    }
	    var container = $('<div/>').attr('id', 'draggingContainer');
	    container.append(selected.clone());
	    return container; 
	  }
	});});
	
	$('#directory_list.gallery li img, #directory_list.grid li').live("click", function(event) {
		if(!event.metaKey) {
			$('.ui-selected').removeClass('ui-selected');
			$(this).closest('li').addClass('ui-selected');			
		}
		else {
			$(this).closest('li').toggleClass('ui-selected');
		}
		if($('.ui-selected').length) {
			if($('.ui-selected').length > 1) {
				$('a.togglestate.single').addClass('disabled');				
				$('a.togglestate:not(.single)').removeClass('disabled');
			}
			else {
				$('a.togglestate').removeClass('disabled');
			}
		}
		else {
			$('a.togglestate').addClass('disabled');
		}
		event.stopPropagation();
	});
	
	// #('#drop').live("click",function(event) {
	// 	$('.ui-selected').removeClass('ui-selected');
	// })


	$('li.folder').livequery(function() {$(this).droppable({
		over: function() {
			$(this).css('background','#ddd');
		},
		out: function() {
			$(this).css('background','transparent');			
		},
		drop: function(event, ui) {
			var $li = $(this);
			var files = [];
			$('#draggingContainer li').each(function() {

				files.push($(this).data('id'));
			});
			$('#drop').load(
				$('base').attr('href')+"admin/files/move/"+$('#drop').data('folderid'),
				{
					'source': files,
					'dest': $li.data('id')
				}
			)
		},
		tolerance: 'pointer'
	});});
	
	
	$('li:not(.head)').each(function(i,e) {
		if(i % 2 == 1) {
			$(e).addClass('even');
		}
	});
	
	
	var timeout;
	$('#search input').keyup(function() {
		if(timeout) window.clearTimeout(timeout);
		s = $(this).val();		
		timeout = window.setTimeout(function() {
			$('#directory_list li:not(.head)').hide();
			$('#directory_list li:not(.head)').each(function() {
				reg = new RegExp(s,"i");
				if($(this).find('.filename').text().match(reg)) {
					$(this).show();
				}
			});		
		},150);		
	});
	
	
	$('img[data-tooltipurl]').livequery(function() {
		var $t = $(this);
		var tooltipType = $t.data('tooltipurl');
		if(tooltipType == "") return;
		
		$t.tooltip({
			track: true,
			delay: 500,
			extraClass: "pretty",
			fixPNG: true,
			showURL: false,
			bodyHandler: function() {
				return $("<img/>").attr('src', tooltipType);
			}
		});

	});
	
	var openEditWindow = function (callback) {
		if(!$('#drop').hasClass('open')) {
			doResize();
			$('#drop').animate({'width': $(window).width()-360+'px'});
			$('#edit').animate({'width':'350px'},function() {
			 	$('select[name=ParentID]').find('option').each(function() {
					$(this).html($(this).html().replace(/__/g,'&nbsp;&nbsp;'));
				})
				$('select[name=ParentID]').chosen();
				if(callback && typeof(callback) == "function") {
					callback();
				}
			});
			$('#drop').addClass('open');

		}
	};
	
	var closeEditWindow = function (callback) {
		if($('#drop').hasClass('open')) {
			$('#drop').animate({'width':$(window).width()-10+'px'});
			$('#edit').animate({'width':'0'}, function() {
				if(callback && typeof(callback) == "function") {
					callback();
				}				
			});
			$('#drop').removeClass('open');
		}

	}
	
	var doResize = function () {
		$('#drop, #edit').css('height', ($(window).height()-98)+'px');
		if($('#drop').hasClass('open')) {
			$('#drop').css('width', $(window).width()-360+'px');
		}
		else {
			$('#drop').css('width', $(window).width()-10+'px');
		}
		if($(window).width() < 1000) {
			$('body').addClass('small');
		}
		else {
			$('body').removeClass('small');
		}
	};
	
	
	var doUpload = function(files, url) {
        var http = new XMLHttpRequest();
		var $progressBar = $('#progress');
		var uploadTimeout = window.setTimeout(function() {
		// Update progress bar
		http.upload.addEventListener("progress", function (evt) {						
			if (evt.lengthComputable) {
				$progressBar.css('width', (evt.loaded / evt.total) * 100 + "%");
			}
			else {
			}
		}, false);
		},1000);

		http.addEventListener("load", function () {
			window.clearTimeout(uploadTimeout);
			if(http.status != "200") {
				apprise(http.responseText);
			}
			else {
				refreshFiles();
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
	
	
	$('.file img, .grid .file').live("dblclick", function(e) {
		if($(e.target).is('.editable')) return;
		var $t = $(this).closest('li');
		$('#edit').load(
			$('base').attr('href')+"admin/files/editfile/"+$('#drop').data('folderid')+"/"+$t.data('id'),
			openEditWindow
		);

	});
	
	$('.folder img, .grid .folder').live('dblclick', function(e) {
		if($(e.target).is('.editable')) return;
		var $t = $(this).closest('li');
		window.location.href = $t.data('link');
		return false;
	});
	
	$('#delete').live("click", function() {
		if($(this).is('.disabled')) return false;
		
		var $t = $(this);
		var files = [];
		$('.ui-selected').each(function() {
			files.push($(this).data('id'));
		});
		apprise($t.data('confirmtext'),{'confirm':true},function(r) {
			if(r) {
				$('#drop').load($t.attr('href'), {list : files});				
			}
		});
		return false;
	});


	
	$('#edit_button').click(function() {
		if(!$(this).is('.disabled')) {
			if($('#directory_list').is('.grid')) {
				$('.ui-selected:first').dblclick();	
			}
			else {
				$('.ui-selected:first img').dblclick();
			}
			
		}
		return false;
	});
	
	
	
	$('#Form_FileEditForm_action_doFileCancel').live("click", function() {
		closeEditWindow();
		return false;
	});
	
	$('#Form_FileEditForm').livequery(function() {
		$(this).ajaxForm(function(response) {
			closeEditWindow(function() {
				refreshFiles();
			});
		});
		
	});
	

	$('.file_attach_upload').live("change", function(e) {
		doUpload(e.target.files, $('#drop').data('uploadurl'));
        e.preventDefault();
        e.stopPropagation();
	}).live("mouseenter", function() {
		$(this).siblings('.file_upload_btn').addClass('over');
	}).live("mouseleave", function() {
		$(this).siblings('.file_upload_btn').removeClass('over');
	});
	
	
	$(window).resize(doResize);	
	$('#drop').css('width', $(window).width()-10+'px');		
	doResize();
	$('#footer').css('visibility','visible');

	var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
	if(vars["edit"]) {
		$('li[data-id='+vars["edit"]+'] img').dblclick();
	}
	
	var ajaxTimeout;
	
	$('body').ajaxStart(function() {
		ajaxTimeout = window.setTimeout(function() {
			window.parent.jQuery.fancybox.showActivity();
		},1500)
	}).ajaxStop(function() {
		window.clearTimeout(ajaxTimeout);
		window.parent.jQuery.fancybox.hideActivity();
	});
	

	// FileAttachmentField
	function getParent() {
		return window.parent.$parentField;
	}
	
	if(getParent() && !getParent().is('.multi')) {
		$('#attach').addClass('single');
	}
	
	$('#attach').click(function() {
		if(!$(this).is('.disabled')) {
			var ids = [];
			$('.ui-selected').each(function() {
				ids.push($(this).data('id'));
			});
			$('#selected_files').append(ids.join(','));
			window.parent.jQuery.fancybox.close();			
		}
	});
	
	
	$('#view a').click(function() {
		var $t = $(this);
		$.post(
			$t.attr('href'),
			function(response) {
				$('#view a').removeClass('current');
				$('#view .'+response).addClass('current');
				$('#directory_list').attr('class', response);
			}
		);
		return false;		
	});



});
	
})(jQuery);