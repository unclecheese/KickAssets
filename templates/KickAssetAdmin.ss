<!DOCTYPE html>
<html>
	<head>
		<% base_tag %>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Files and Images</title>
	</head>
	<body>
		
		<div id="head">
			<div id="back">
				<a class="button <% if TopLevel %>disabled<% end_if %>" href="$ParentLink"><% _t('KickAssetAdmin.BACKTOPARENT','&laquo; Back') %></a>
			</div>
			<h2>$CurrentFolderName</h2>
			<div id="buttons">
				<a class="button" id="newfolder" href="$NewFolderLink">+ <% _t('KickAssetAdmin.NEWFOLDER','New Folder') %></a>
			</div>			
		</div>
		
		<div id="drop" data-folderid="$CurrentFolder.ID" data-uploadurl="$UploadLink" class="clr">
			<% include FileList %>
		</div>
		
		<div id="edit">
		</div>
		
		<div id="footer">
			<div id="search">
				<input type="text" name="search" />
			</div>
			<div id="actions">
				<% if SelectMode %>
					<a class="button disabled togglestate" id="attach" href="javascript:void(0);"><% _t('KickAssetAdmin.ATTACHFILES','Attach selected file(s)') %></a>
					<div id="selected_files"></div>
				<% end_if %>

				<a class="button disabled togglestate single" id="edit_button" href="javascript:void(0);">Edit</a>				
				<a class="button disabled togglestate" data-confirmtext="<% _t('KickAssetAdmin.DELETEFILES','Are you sure you want to delete the selected file(s)?') %>" id="delete" href="$DeleteLink">Delete</a>				
			</div>
			<div id="status">
			</div>

		</div>
	</body>
</html>
