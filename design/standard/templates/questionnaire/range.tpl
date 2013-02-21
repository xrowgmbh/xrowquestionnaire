	<li class="{literal}{$id}{/literal} ui-state-default">
		<input type="hidden" name="{$attribute_base}_xrowquestionnaire[{$attrID}][settings][ranges][{literal}{$position}{/literal}][id]" value="{literal}{$id}{/literal}" />
		<input type="hidden" name="{$attribute_base}_xrowquestionnaire[{$attrID}][settings][ranges][{literal}{$position}{/literal}][position]" value="{literal}{$position}{/literal}" />
		bis:<br /> <input type="text" name="{$attribute_base}_xrowquestionnaire[{$attrID}][settings][ranges][{literal}{$position}{/literal}][points]" /></label>
		<br />Text:<br />
		<textarea name="{$attribute_base}_xrowquestionnaire[{$attrID}][settings][ranges][{literal}{$position}{/literal}][text]" rows="2" cols="20"></textarea></label>
		<br /><button style="margin-right: 1em;vertical-align:top;" class="button" type="button" onclick="remove_range({literal}{$id}{/literal});">entfernen</button>
		<div class="float-break"></div>
	</li>