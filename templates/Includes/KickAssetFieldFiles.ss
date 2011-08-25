<div class="file_drop <% if Files %><% else %>empty<% end_if %>" data-uploadurl="$UploadLink"
	<% if Files %>
		<% control Files %>
		style="background-image:url($Thumb)"
		<% end_control %>
	<% end_if %>					
>
</div>
<div class="file_info">
	<h3>$Title</h3>
	<div class="file_name">
	<% if Files %>
		<% control Files %>
			$Name ($Size)
		<% end_control %>
	<% else %>
		<% _t('FileAttachmentField.NOFILESATTACHED','(No files attached)') %>
	<% end_if %>
	</div>
	<div id="progress_wrap">
		<div id="progress"></div>
	</div>
	<div class="file_attach_buttons">
		<a href="$BrowseLink" class="file_attach_btn"><% _t('FileAttachmentField.BROWSEFILES','Browse files') %></a>
		<% if Files %>
			<% control Files %>
				<a href="#" class="detach_btn" data-id="$ID"><% _t('FileAttachmentField.DETACH','Detach file') %></a>
				<input type="hidden" name="{$Top.Name}ID" value="$ID" />
			<% end_control %>
		<% end_if %>
	</div>

</div>
