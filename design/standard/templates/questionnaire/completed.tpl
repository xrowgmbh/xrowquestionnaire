<div id="question_{$question.id}" class="questionnaire question {$question.layout}">

<p>Vielen Dank für Ihre Teilnahme.</p>

<form id="form_{$attribute.id}" method="post" action="">
{if $attribute.content.settings.play_once|ne('on')}
	<input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'again' );" id="again_button{$attribute.id}" type="button" class="again_button" name="show_result" value="{'Wiederholen'|i18n( 'xrowquestionnaire/datatype/edit' )}" title="{'Umfrage Wiederholen'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
{/if}

{if $attribute.content.settings.results|ne('no')}
    <input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'results' );" id="show_result_button{$attribute.id}" type="button" class="show_result_button" name="show_result" value="{'Ergebnis anzeigen'|i18n( 'xrowquestionnaire/datatype/edit' )}" title="{'Ergebnisse anzeigen'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
{/if}
		<input type="hidden" name="attribute_id" value="{$attribute.id}" />
		<input type="hidden" name="contentobject_id" value="{$attribute.contentobject_id}" />
		<input type="hidden" name="language_code" value="{$attribute.language_code}" />
		<input type="hidden" name="version" value="{$attribute.version}" />

	</form>
</div>