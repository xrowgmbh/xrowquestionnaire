<div id="question_{$question.id}" class="question {$question.layout}">
<form id="form_{$attribute.id}" method="post" action="">
{if is_set($count)}
    <p>Frage {$number_of} von {$count}</p>
{/if}
                {if $question.object_relation}
                    {attribute_view_gui attribute=fetch( 'content', 'node', hash( 'node_id', $question.object_relation ) ).data_map.image image_class=medium}
                {/if}
                <h2>{$question.text|wash()}</h2>

                    <ul>
                    {foreach $question.answers as $answer}
                        {if $answer.points|ne(0)}
                            {set $points = $answer.points}
                        {else}
                            {set $points = 0}
                        {/if}
                        <li id="answer_{$answer.id}" class="answer">
                            {if $answer.object_relation}
                                {attribute_view_gui attribute=fetch( 'content', 'node', hash( 'node_id', $answer.object_relation ) ).data_map.image image_class=small}
                            {/if}
                            <p>
                                <label>
                                	{if is_set(ezini($question.answer_type, 'SelectMultiple', 'xrowquestionnaire.ini'))}
                                	<input id="answer_{rand()}" type="checkbox" name="answer_id[]" value="{$answer.id}" {if $prev_answers|contains($answer.id)}checked="checked"{/if}/>
                                	{else}
                                	<input id="answer_{rand()}" type="radio" name="answer_id[]" value="{$answer.id}" {if $prev_answers|contains($answer.id)}checked="checked"{/if}/>
                                	{/if} 
                                	{$answer.text|wash()}
                                	</label>
                              </p>
                          </li>
                    {/foreach}
                    </ul>

                {undef $answers}
                <div class="buttonblock">
                    {if and($first|not, $attribute.content.settings.quiz|ne('on'))}
                    	<input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'prev' );" id="question_submit{$question.id}" class="question_submit" type="button" name="submit_vote{$question.id}" value="{'Zurück'|i18n( 'xrowquestionnaire/datatype/edit' )}" title="{'vorherige Frage'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
                    {/if}
                    <input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'submit' );" id="question_submit{$question.id}" class="question_submit" type="button" name="submit_vote{$question.id}" value="{'Abstimmen'|i18n( 'xrowquestionnaire/datatype/edit' )}" title="{'Geben Sie Ihre Stimme ab!'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
                    {if or(and($last,$attribute.content.settings.results|ne('no')), and( $first, $attribute.content.settings.results|eq('all') ) )}
                    	<input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'results' );" id="show_result_button{$attribute.id}" type="button" class="show_result_button" name="show_result" value="{'Ergebnis anzeigen'|i18n( 'xrowquestionnaire/datatype/edit' )}" title="{'Ergebnisse anzeigen'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
                    {/if}
                </div>
        <input type="hidden" name="question_id" value="{$question.id}" />
        <input type="hidden" name="attribute_id" value="{$attribute.id}" />
        <input type="hidden" name="contentobject_id" value="{$attribute.contentobject_id}" />
        <input type="hidden" name="language_code" value="{$attribute.language_code}" />
        <input type="hidden" name="version" value="{$attribute.version}" />

{if is_set($errors)}
    <div class="message-warning">
        {foreach $errors as $error}
            {$error}<br />
        {/foreach}
    </div>
{/if}
</form>
</div>