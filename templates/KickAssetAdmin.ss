<!DOCTYPE html>
<html>
	<head>
		<% base_tag %>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><% _t('AssetAdmin.MENUTITLE','Files & Images') %></title>
	</head>
	<body>
		
		<div id="head">
			<h2>$BreadCrumbs</h2>
			<div id="search">
				<input type="text" name="search" />
			</div>

		</div>
		
		<div id="drop" data-folderid="$CurrentFolder.ID" data-uploadurl="$UploadLink" class="clr">
			<% include FileList %>
		</div>
		
		<div id="edit">
		</div>
		
		<div id="footer">
			<div id="view">
				<span><% _t('KickAssetAdmin.VIEW','View') %>:</span>
				<a class="gallery <% if CurrentView(gallery) %>current<% end_if %>" href="$GalleryLink" title="<% _t('KickAssetAdmin.GALLERY','Gallery') %>"><img src="kickassets/images/gallery.png" alt="gallery" height="16"/></a>
				<a class="grid <% if CurrentView(grid) %>current<% end_if %>" href="$GridLink" <% if CurrentView(grid) %>class="current"<% end_if %> title="<% _t('KickAssetAdmin.GRID','Grid') %>"><img src="kickassets/images/grid.png" alt="grid" height="16" /></a>
			</div>
			<div id="actions">
				<% if SelectMode %>
					<a class="button disabled togglestate" id="attach" href="javascript:void(0);"><img src="kickassets/images/check.png" height="16" /> <% _t('KickAssetAdmin.ATTACHFILES','Attach selected file(s)') %></a>
					<div id="selected_files"></div>
				<% end_if %>

				<a class="button disabled togglestate single" id="edit_button" href="javascript:void(0);"><img src="kickassets/images/edit.png" height="16" /> <% _t('KickAssetAdmin.EDIT','Edit') %></a>				
				<a class="button disabled togglestate" data-confirmtext="<% _t('KickAssetAdmin.DELETEFILES','Are you sure you want to delete the selected file(s)?') %>" id="delete" href="$DeleteLink"><img src="kickassets/images/delete.png" height="16" /> <% _t('KickAssetAdmin.DELETE','Delete') %></a>				
				<a class="button" id="newfolder" href="$NewFolderLink"><img src="kickassets/images/add.png" height="16" /> <% _t('KickAssetAdmin.NEWFOLDER','New Folder') %></a>				
				<a class="button" id="filesystemsync" href="/admin/assets/sync" title="<% _t('AssetAdmin_left.ss.FILESYSTEMSYNC_DESC', 'SilverStripe maintains its own database of the files &amp; images stored in your assets/ folder.  Click this button to update that database, if files are added to the assets/ folder from outside SilverStripe, for example, if you have uploaded files via FTP.') %>"><img src="kickassets/images/replace.png" height="16" /> <% _t('AssetAdmin_left.ss.FILESYSTEMSYNC','Look for new files') %></a>
				<span class="file-wrapper">
				  <input type="file" name="upload" class="file_attach_upload" multiple="multiple" />
				  <span class="file_upload_btn button"><img src="kickassets/images/upload.png" height="16" /> <% _t('FileAttachmentField.UPLOADFILES','Upload files') %></span>
				</span>

			</div>
			<div id="status">
			</div>

		</div>
	</body>
</html>
