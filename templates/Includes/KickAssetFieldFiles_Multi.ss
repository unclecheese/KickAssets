<h3>$Title</h3>
<div class="file_drop <% if Files %><% else %>empty<% end_if %>" data-uploadurl="$UploadLink">
	<% _t('FileAttachmentField.DROPFILESHERE','Drop files here') %>
</div>
<div class="file_info">

	<div class="file_name">
	<% if Files %>
		<ul id="sortable">
		<% control Files %>
		<li class="sortableli" style="cursor: default;">
			<div class="file_block">
				<img class="fieldHandler" alt="Drag to rearrange order of fields" src="sapphire/images/drag.gif" style="cursor: move;">
				<span class="thumb"><img src="$Thumb" height="24" /></span> <span class="name">$Name</span> <span class="size">($Size)</span>
				<span class="multi_actions">
				<a href="$EditLink" class="file_attach_btn" data-id="$ID" title="<% _t('FileAttachmentField.EDIT','Edit') %>"><img src="kickassets/images/edit.png" height="14" /></a>
				<a href="javascript:void(0);" class="detach_btn" data-id="$ID" title="<% _t('FileAttachmentField.DETACH','Detach') %>"><img src="kickassets/images/remove.png" height="14" /></a>
				<a href="$RemoveLink" class="delete_btn" data-confirmtext="<% _t('FileAttachmentField.AREYOUSURE','Are you sure you want to delete this file permanently?') %>"	 data-id="$ID" title="<% _t('FileAttachmentField.DELETEPERMANENTLY','Delete permanently') %>"><img src="kickassets/images/delete.png" height="14" /></a>
				</span>
				<input type="hidden" name="{$Top.Name}[]" value="$ID" />
				<input type="hidden" class="sortHidden" name="sort[]" value="$POS" />
			</div>
		</li>
		<% end_control %>
		</ul>
	<% else %>
		<% _t('FileAttachmentField.NOFILESATTACHED','No files attached') %>
	<% end_if %>
	</div>
	<div id="progress_wrap">
		<div id="progress"></div>
	</div>
	<h3 class="multi_add_files">Add files</h3>
	<div class="progress_wrap">
		<div class="progress"></div>
	</div>	
	<div class="file_attach_buttons">
		<span class="file-wrapper">
		  <input multiple="multiple" type="file" name="upload" class="file_attach_upload" data-uploadurl="$UploadLink"/>
		  <span class="file_upload_btn btn"><img src="kickassets/images/upload.png" height="16" /> <% _t('FileAttachmentField.FROMYOURCOMPUTER','From your computer') %></span>
		</span>
		<% if ExistingFileSelection %>
			<a href="$BrowseLink" class="file_attach_btn btn"><img src="kickassets/images/cloud.png" height="16" /> <% _t('FileAttachmentField.FROMFILES','From files') %></a>		
		<% end_if %>
	</div>

</div>