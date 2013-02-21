	<li class="{$range.id} ui-state-default">
		<input type="hidden" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][ranges][{$range.position}][id]" value="{$range.id}" />
		<input type="hidden" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][ranges][{$range.position}][position]" value="{$range.position}" />
		bis:<br /><input type="text" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][ranges][{$range.position}][points]" value="{$range.points}" />
		<br />Text:<br />
		<textarea name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][ranges][{$range.position}][text]" rows="2" cols="20">{$range.text}</textarea>
		<br /><button class="button" type="button" onclick="remove_range({$range.id});">entfernen</button>
	</li>