<div class="pad">
	<img src="$IconURL" height="64" 
		<% if Image %>
			data-tooltipurl="$TooltipURL"
		<% end_if %> 
	/>
</div>
<span class="editable filename">$Item.Name</span>
<span class="path">$Item.Filename</span>
<span class="size">$Item.Size</span>
<% if Image %>
<span class="sizepx">$Item.Width x $Item.Height</span>
<% end_if %>