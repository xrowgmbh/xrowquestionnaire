    <div id="question_{literal}{$id}{/literal} " class="question_{literal}{$id}{/literal} question_tabs">
        <ul class="draggable">
            <li><a href="#tabs-1_{$id}">{'Frage'|i18n('xrowquestionnaire/datatype/edit')}</a></li>
            <li><a href="#tabs-2_{$id}">{'Antworten'|i18n('xrowquestionnaire/datatype/edit')}</a></li>
        </ul>
        <div id="tabs-1_{$id}">
            <input type="hidden" name="{$attribute_base}_xrowquestionnaire[{$attrID}][questions][{literal}{$id}{/literal}][id]" value="{literal}{$id}{/literal}" />
            <label for="type">{'Antwort Typ:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <select class="new_set_{literal}{$id}{/literal}" name="{$attribute_base}_xrowquestionnaire[{$attrID}][questions][{literal}{$id}{/literal}][answer_type]" onChange="addAnswer({literal}{$id}{/literal},'{$attribute_base}','{$attrID}','{$contentobject_id}','{$version}', true)" >
                {foreach ezini('AnswerTypes', 'AvailableAnswerTypes', 'xrowquestionnaire.ini') as $answerType}
                    <option {if $question.answer_type|eq($answerType)}selected{/if} value="{$answerType}">{ezini($answerType, 'Description', 'xrowquestionnaire.ini')}</option>
                {/foreach}
            </select>
            {if ezini('LayoutTypes', 'AvailableLayoutTypes', 'xrowquestionnaire.ini')|count}
            <label for="layout">{'Layout:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <select name="{$attribute_base}_xrowquestionnaire[{$attrID}][questions][{literal}{$id}{/literal}][layout]">
                {foreach ezini('LayoutTypes', 'AvailableLayoutTypes', 'xrowquestionnaire.ini') as $layoutType}
                    <option {if $question.layout|eq($layoutType)}selected{/if} value="{$layoutType}">{ezini($layoutType, 'Description', 'xrowquestionnaire.ini')}</option>
                {/foreach}
            </select>
            {/if}
            <input type="hidden" class="questionPosition" name="{$attribute_base}_xrowquestionnaire[{$attrID}][questions][{literal}{$id}{/literal}][position]" value="{literal}{$position}{/literal}" />
            
            <label for="question">{'Frage:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <textarea name="{$attribute_base}_xrowquestionnaire[{$attrID}][questions][{literal}{$id}{/literal}][text]" rows="3" cols="24"></textarea>
            {*
            <label for="points" title="{'Diese Punktzahl wird vergeben, sobald alle Antworten richtig beantwortet wurden.'|i18n('xrowquestionnaire/datatype/edit')}">{'Punktzahl:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <input type="text" name="{$attribute_base}_xrowquestionnaire[{$attrID}][questions][{literal}{$id}{/literal}][points]" />
            *}
            <label for="object_relation">{'Bildverknüpfung:'|i18n('xrowquestionnaire/datatype/edit')}</label>
            <input type="text" id="xrowquestionnaire_{$contentobject_id}_{$version}_images_{$attrID}_{literal}{$id}{/literal}_relation" name="{$attribute_base}_xrowquestionnaire[{$attrID}][questions][{literal}{$id}{/literal}][object_relation]" value="" />
            <br />
            <br />
            <button class="button uploadImage" type="button" name="{$attribute_base}_xrowquestionnaire[{$attrID}][questions][{literal}{$id}{/literal}][image]" id="xrowquestionnaire_{$contentobject_id}_{$version}_images_{$attrID}_{literal}{$id}{/literal}">Bild hinzufügen</button>
            <input type="hidden" id="xrowquestionnaire_{$contentobject_id}_{$version}_images_{$attrID}_{literal}{$id}{/literal}_url" value={concat( 'questionnaire/upload/', $contentobject_id, '/', $version, '/images' )|ezurl()} />
            <button class="button" type="button" onclick="remove_question('{literal}{$id}{/literal}');">Frage entfernen</button>
        </div>
        <div id="tabs-2_{$id}">
            <ul id="sortable_{literal}{$id}{/literal}" class="ui-helper-reset">
            {*ANSWER CONTAINER*}
            </ul>
         <p>
            <button class="button" onclick="addAnswer({literal}{$id}{/literal},'{$attribute_base}','{$attrID}','{$contentobject_id}','{$version}')" type="button">{'Antwort hinzufügen'|i18n( 'xrowquestionnaire/datatype/edit' )}</button>
        </p>
        </div>
    </div>