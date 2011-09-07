	<h3>$Title</h3>
<div class="file_drop <% if File %><% else %>empty<% end_if %>" data-uploadurl="$UploadLink"
	<% if File %>
		<% control File %>
		style="background-image:url($Thumb)"
		<% end_control %>
	<% end_if %>					
>
</div>
<div class="file_info">

	<div class="file_name">
	<% if File %>
		<% control File %>
			$Name ($Size)
		<% end_control %>
	<% else %>
		<% _t('FileAttachmentField.NOFILESATTACHED','(No files attached)') %>
	<% end_if %>
	</div>
	<div class="progress_wrap">
		<div class="progress"></div>
	</div>
	<div class="file_attach_buttons">
			<div class="controls_no_file" <% if File %>style="display:none;"<% end_if %>>
				<span class="file-wrapper">
				  <input type="file" name="upload" class="file_attach_upload" data-uploadurl="$UploadLink"/>
				  <span class="file_upload_btn btn"><img src="kickassets/images/upload.png" height="16" /> <% _t('FileAttachmentField.FROMYOURCOMPUTER','From your computer') %></span>
				</span>
				<% if ExistingFileSelection %>
					<a href="$BrowseLink" class="file_attach_btn btn"><img src="kickassets/images/cloud.png" height="16" /> <% _t('FileAttachmentField.FROMFILES','From files') %></a>
				<% end_if %>
				<a style="display:none;" href="javascript:void(0);" class="file_cancel_btn btn"><img src="kickassets/images/cancel.png" height="16" /> <% _t('FileAttachmentField.CANCEL','Cancel') %></a>
			</div>
			<div class="controls_has_file" <% if File %><% else %>style="display:none;"<% end_if %>>
				<% if File %>
				<% control File %>
					<a href="javascript:void(0);" class="replace_btn btn" data-id="$ID"><img src="kickassets/images/replace.png" height="16" /> <% _t('FileAttachmentField.REPLACE','Replace') %></a>
					<% if ExistingFileSelection %>
						<a href="$EditLink" class="file_attach_btn btn" data-id="$ID"><img src="kickassets/images/edit.png" height="16" /> <% _t('FileAttachmentField.EDIT','Edit') %></a>
					<% end_if %>
					<a href="javascript:void(0);" class="detach_btn btn" data-id="$ID"><img src="kickassets/images/remove.png" height="16" /> <% _t('FileAttachmentField.REMOVE','Remove') %></a>
					<a href="$RemoveLink" class="delete_btn btn" data-id="$ID" data-confirmtext="<% _t('FileAttachmentField.AREYOUSURE','Are you sure you want to delete this file permanently?') %>"><img src="kickassets/images/delete.png" height="16" /> <% _t('FileAttachmentField.DELETEFROMFILES','Delete from files') %></a>
					<input type="hidden" name="{$Top.Name}ID" value="$ID" />
				<% end_control %>
				<% end_if %>
			</div>


	</div>

</div>
