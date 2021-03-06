{def $points = false
     $class = false()
     $result_type = cond($results.settings.results|eq('my'), 'result_my', 'result_percentage')}
{if is_set($results.settings.ranges)}
    {def $text = $results.settings.ranges.0.text}
{/if}
<div class="questionnaire result_page {$result_type}">
    {if $result_type|eq('result_my')}
        {def $user = fetch( 'user', 'current_user' )}
        {if $user.is_logged_in}
            <h3>Ergebnisse von {$user.contentobject.name|wash}</h3>
        {else}
            <h3>{'Ihre Ergebnisse'|i18n( 'xrowquestionnaire/datatype/view' )}</h3>
        {/if}

        {foreach $results.questions as $question}
            <strong>{$question.text}</strong><br />
            {if $question.answer_type|ne('grades')}
                {set $points = true}

                <strong>{'Punkte'|i18n( 'xrowquestionnaire/datatype/view' )}: {$question.score}</strong>
            {/if}
            <ul style="width: 100%;">
            {foreach $question.answers as $answer}
                {set $class = cond($results.settings.quiz, "answer_selected", cond($answer.total, "answer_selected",) )}
                <li{cond($class, concat(' class="',$class,'"'),)}>{$answer.text}
                {if $results.settings.quiz}
                    {if and($answer.correct,$answer.total)}
                        <span class="correct" title="{'Von Ihnen gewählt und richtig.'|i18n( 'xrowquestionnaire/datatype/view' )}">{'gewählt und richtig'|i18n( 'xrowquestionnaire/datatype/view' )}</span>
                    {elseif $answer.total}
                        <span class="false" title="{'Von Ihnen gewählt und falsch.'|i18n( 'xrowquestionnaire/datatype/view' )}">{'gewählt und falsch'|i18n( 'xrowquestionnaire/datatype/view' )}</span>
                    {/if}
                {else}
                    {if $answer.total}
                        <span title="{'Von Ihnen gewählt.'|i18n( 'xrowquestionnaire/datatype/view' )}">{'gewählt'|i18n( 'xrowquestionnaire/datatype/view' )}</span>
                    {/if}
                {/if}
                {set $class = false()}
                </li>
            {/foreach}
            </ul>
        {/foreach}
        {foreach $results.settings.ranges as $key => $range}
           {if $results.total_score|le($range.points)}
               {set $text = $range.text}
               {break}
           {/if}
        {/foreach}
        {if $points|eq(true)}
            <p>{'Ihre Gesamtpunktzahl'|i18n( 'xrowquestionnaire/datatype/view' )}: {$results.total_score}</p>
            {if is_set($text)}
                <h3>{'Ihre Auswertung'|i18n( 'xrowquestionnaire/datatype/view' )}</h3>
                <p>{$text}</p>
            {/if}
        {/if}

    {else}
        <h3>{'Zusammenfasung aller Ergebnisse'|i18n( 'xrowquestionnaire/datatype/view' )}</h3>
        {foreach $results.questions as $question}
            <strong>{$question.text}</strong>
            <ul style="width: 100%;">
            {foreach $question.answers as $answer}
                <li style="background: url({"1x1_result.png"|ezimage(no)}); background-size:{$answer.percent}%;background-repeat:no-repeat;">{$answer.text} (<abbr title="{"%number_answers% von %number_total% Teilnehmern haben diese Antwort gewählt."|i18n( '', '', hash( '%number_answers%', $answer.total, '%number_total%', $question.total ) )}">{$answer.total}</abbr>) <span class="percent">{$answer.percent}%</span></li>
            {/foreach}
            </ul>
        {/foreach}
    {/if}
    <form id="form_{$attribute.id}" method="post" action="">
        {if $attribute.content.settings.play_once|not()}
            <input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'again' );" id="again_button{$attribute.id}" type="button" class="again_button" name="show_result" value="{'Wiederholen'|i18n( 'xrowquestionnaire/datatype/edit' )}" title="{'Umfrage Wiederholen'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
        {/if}
            <input type="hidden" name="attribute_id" value="{$attribute.id}" />
            <input type="hidden" name="contentobject_id" value="{$attribute.contentobject_id}" />
            <input type="hidden" name="language_code" value="{$attribute.language_code}" />
            <input type="hidden" name="version" value="{$attribute.version}" />
    </form>
</div>

