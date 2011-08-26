<ul id="directory_list">
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