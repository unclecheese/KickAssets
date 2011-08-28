<div class="file_drop <% if Files %><% else %>empty<% end_if %>" data-uploadurl="$UploadLink">
	<% _t('FileAttachmentField.DROPFILESHERE','Drop files here') %>
</div>
<div class="file_info">
	<h3>$Title</h3>
	<div class="file_name">
	<% if Files %>
		<% control Files %>
			<div class="file_block">
				<img src="$Thumb" height="16" /> $Name ($Size) <a href="javascript:void(0);" class="detach_btn" data-id="$ID"><% _t('FileAttachmentField.DETACH','Detach file') %></a>
				<input type="hidden" name="{$Top.Name}[]" value="$ID" />
			</div>
		<% end_control %>
	<% else %>
		<% _t('FileAttachmentField.NOFILESATTACHED','No files attached') %>
	<% end_if %>
	</div>
	<div id="progress_wrap">
		<div id="progress"></div>
	</div>

	<div class="file_attach_buttons">
		<a href="$BrowseLink" class="file_attach_btn"><% _t('FileAttachmentField.BROWSEFILES','Browse files') %></a>
	</div>

</div>
