<div id="question_{$question.id}" class="questionnaire question_result {$question.layout}">

{if is_set($count)}
    <p>Frage {$number_of} von {$count}</p>
{/if}
<h2>{$question.text|wash()}</h2>
<h3>Ihre Antwort:</h3>
<ul>
{foreach $current_answers as $current_answer}
	    <li>
        	{$current_answer.text|wash()} 
        	{if is_set($current_answer.correct)}
            	<span class="correct">{'richtig'|i18n( 'xrowquestionnaire/datatype/edit' )}</span>
            {else}
            	<span class="false">{'falsch'|i18n( 'xrowquestionnaire/datatype/edit' )}</span>
          	{/if}
        </li>
     {/foreach}
</ul>

<h3>Richtige Antwort:</h3>
<ul>
{foreach $correct_answers as $right_answer}
	<li>{$right_answer.text|wash()}</li>
{/foreach}
</ul>

<form id="form_{$attribute.id}" method="post" action="">
<div class="buttonblock">
        {if and($first|not, $attribute.content.settings.quiz|ne('on'))}
        <input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'prev' );" id="question_submit{$question.id}" class="question_submit" type="button" name="submit_vote{$question.id}" value="{'Zurück'|i18n( 'xrowquestionnaire/datatype/edit' )}" title="{'vorherige Frage'|i18n( 'xrowquestionnaire/datatype/edit' )}" />                     
        {/if}
        <input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'next' );" id="question_submit{$question.id}" class="question_submit" type="button" name="submit_vote{$question.id}" value="{'Weiter'|i18n( 'xrowquestionnaire/datatype/edit' )}" title="{'nächste Frage'|i18n( 'xrowquestionnaire/datatype/edit' )}" />

        {if and($last,$attribute.content.settings.results|ne('no'))}
        	<input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'results' );" id="show_result_button{$attribute.id}" type="button" class="show_result_button" name="show_result" value="{'Ergebnis anzeigen'|i18n( 'xrowquestionnaire/datatype/edit' )}" title="{'Ergebnisse anzeigen'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
        {/if}
</div>
        <input type="hidden" name="question_id" value="{$question.id}" />
        <input type="hidden" name="attribute_id" value="{$attribute.id}" />
        <input type="hidden" name="contentobject_id" value="{$attribute.contentobject_id}" />
        <input type="hidden" name="language_code" value="{$attribute.language_code}" />
        <input type="hidden" name="version" value="{$attribute.version}" />
        <input type="hidden" name="number_of" value="{$number_of}" />
</form>

</div>
