<ul id="directory_list" class="$View">
<li class="head">
	<span class="pad">&nbsp;</span>
	<span class="filename"><% _t('KickAssets.NAME','Name') %></span>
	<span class="path"><% _t('KickAssets.FILENAME','Filename') %></span>
	<span class="size"><% _t('KickAssets.SIZE','Size') %></span>
	<span class="widthheight"><% _t('KickAssets.WIDTHHEIGHT','Size (px)') %></span>
</li>
<% if Folders %>
<% control Folders %>
	<li class="folder" data-id="$Item.ID" data-link="$Link">
		<% include Folder %>
	</li>
<% end_control %>
<% end_if %>

<% if Files %>
<% control Files %>
	<li class="file" data-id="$Item.ID">
		<% include File %>
	</li>
<% end_control %>
<% end_if %>