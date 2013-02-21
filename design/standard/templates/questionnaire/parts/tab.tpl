    <div id="tabelement" class="question_{$question.id} question_tabs">
        <ul class="draggable">
            <li><a href="#tabs-1_{$question.id}">{'Frage'|i18n('xrowquestionnaire/datatype/edit')}</a></li>
            <li><a href="#tabs-2_{$question.id}">{'Antworten'|i18n('xrowquestionnaire/datatype/edit')}</a></li>
        </ul>
        <div id="tabs-1_{$question.id}">
            <input type="hidden" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][questions][{$question.id}][id]" value="{$question.id}" />
            <label for="type">{'Antwort Typ:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <select name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][questions][{$question.id}][answer_type]">
                {foreach ezini('AnswerTypes', 'AvailableAnswerTypes', 'xrowquestionnaire.ini') as $answerType}
                    <option {if $question.answer_type|eq($answerType)}selected{/if} value="{$answerType}">{ezini($answerType, 'Description', 'xrowquestionnaire.ini')}</option>
                {/foreach}
            </select>
            <label for="layout">{'Layout:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <select name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][questions][{$question.id}][layout]">
                {foreach ezini('LayoutTypes', 'AvailableLayoutTypes', 'xrowquestionnaire.ini') as $layoutType}
                    <option {if $question.layout|eq($layoutType)}selected{/if} value="{$layoutType}">{ezini($layoutType, 'Description', 'xrowquestionnaire.ini')}</option>
                {/foreach}
            </select>
            <input type="hidden" class="questionPosition" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][questions][{$question.id}][position]" value="{$question.position}" />
            <label for="question">{'Frage:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <textarea name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][questions][{$question.id}][text]" rows="3" cols="24">{$question.text}</textarea>
            <label for="points" title="{'Diese Punktzahl wird vergeben, sobald alle Antworten richtig beantwortet wurden.'|i18n('xrowquestionnaire/datatype/edit')}">{'Punktzahl:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <input type="text" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][questions][{$question.id}][points]" value="{$question.points}" />
            <label for="object_relation">{'Bildverknüpfung:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <input type="text" id="xrowquestionnaire_{$attribute.contentobject_id}_{$attribute.version}_images_{$attribute.id}_{$question.id}_relation" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][questions][{$question.id}][object_relation]" value="{$question.object_relation}" />
            <br />
            <br />
            <button class="button uploadImage" type="button" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][questions][{$question.id}][image]" id="xrowquestionnaire_{$attribute.contentobject_id}_{$attribute.version}_images_{$attribute.id}_{$question.id}">Bild hinzufügen</button>
            <input type="hidden" id="xrowquestionnaire_{$attribute.contentobject_id}_{$attribute.version}_images_{$attribute.id}_{$question.id}_url" value={concat( 'questionnaire/upload/', $attribute.contentobject_id, '/', $attribute.version, '/images' )|ezurl()} />
            <button class="button" type="button" onclick="remove({$question.id});">Frage entfernen</button>
        </div>
        <div id="tabs-2_{$question.id}">
            <ul id="sortable_{$question.id}" class="ui-helper-reset">
            {if is_set( $question.answers)}
                {foreach $question.answers as $answer}
                    {include answer=$answer parentID=$question.id uri="design:questionnaire/parts/answer.tpl"}
                {/foreach}
            {/if}
            </ul>
            <p>
                <button class="button" onclick="addAnswer({$question.id},'{$attribute_base}','{$attribute.id}','{$attribute.contentobject_id}','{$attribute.version}')" type="button">{'Antwort hinzufügen'|i18n( 'xrowquestionnaire/datatype/edit' )}</button>
            </p>
        </div>
    </div>