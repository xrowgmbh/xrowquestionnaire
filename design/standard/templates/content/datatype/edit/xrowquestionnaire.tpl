<div class="context-information">
    <p class="left class-description">{'Administrative Aktionen'|i18n( 'xrowquestionnaire/datatype/edit' )}</p>
    <div class="break"></div>
    <p>
        {def $has_data=questionnaire_has_data( $attribute.id )}
        {if or(is_set($attribute.content.persistent.winner), is_set($attribute.content.settings.lottery)|not(), questionnaire_can_win( $attribute.id )|not() )}
            <button type="button" disabled="disabled">{'Gewinner ermitteln'|i18n( 'xrowquestionnaire/datatype/edit' )}</button>
        {else}
            <input class="button" type="submit" name="CustomActionButton[{$attribute.id}_winner]" value="{'Gewinner ermitteln'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
        {/if}
        {if is_set($attribute.content.persistent.closed)}
            <input class="button" type="submit" name="CustomActionButton[{$attribute.id}_open]" value="{'Voting öffnen'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
        {else}
            <input class="button" type="submit" name="CustomActionButton[{$attribute.id}_close]" value="{'Voting schließen'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
        {/if}
        {if questionnaire_has_data( $attribute.id )}
            <input class="button" type="submit" name="CustomActionButton[{$attribute.id}_reset]" value="{'Ergebnisse zurücksetzen'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
		{else}
			<button type="button" disabled="disabled">{'Ergebnisse zurücksetzen'|i18n( 'xrowquestionnaire/datatype/edit' )}</button>
		{/if}
    </p>
    {if is_set($attribute.content.persistent.winner)}
        <p>{'Gewinner'|i18n( 'xrowquestionnaire/datatype/edit' )}</p>
        {content_view_gui content_object=fetch( 'content', 'object', hash( 'object_id', $attribute.content.persistent.winner ) ) view='line'}
    {/if}
</div>
<div class="context-information">
    <h3 class="left class-description">{'Allgemeine Einstellungen'|i18n( 'xrowquestionnaire/datatype/edit' )}</h3>
    <br />
    <div class="break"></div>
           <label>
               <input type="checkbox" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][user_loggedin]" {if is_set($attribute.content.settings.user_loggedin)}checked="true"{/if} />
            {'Nur für eingeloggte User'|i18n( 'xrowquestionnaire/datatype/edit' )}
        </label>
       
         <label title="{"Beim aktiveren dieser option müssen die Teilnehmer einloggen."|i18n( 'xrowquestionnaire/datatype/edit' )}">
              <input type="checkbox" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][play_once]" {if is_set($attribute.content.settings.play_once)}checked="true"{/if} />
             {'Doppeltes Abstimmen nicht möglich'|i18n( 'xrowquestionnaire/datatype/edit' )}
        </label>
        <i>{"Beim aktiveren dieser option müssen die Teilnehmer einloggen."|i18n( 'xrowquestionnaire/datatype/edit' )}</i>
    
         <label>
            <input type="checkbox" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][captcha]" {if is_set($attribute.content.settings.captcha)}checked="true"{/if} /> 
            {'CAPTCHA aktivieren'|i18n( 'xrowquestionnaire/datatype/edit' )}
        </label>
    
        <label>
            <input type="checkbox" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][lottery]" {if is_set($attribute.content.settings.lottery)}checked="true"{/if} />
            {'Gewinnspiel'|i18n( 'xrowquestionnaire/datatype/edit' )}
        </label>
        <label>
             {'Ergebnisanzeige'|i18n( 'xrowquestionnaire/datatype/edit' )}
        
           
            <select name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][results]">
                <option {if $attribute.content.settings.results|eq('no')}selected{/if} value="no">{'keine'|i18n( 'xrowquestionnaire/datatype/edit' )}</option>
                <option {if $attribute.content.settings.results|eq('my')}selected{/if} value="my">{'eigene'|i18n( 'xrowquestionnaire/datatype/edit' )}</option>
                <option {if $attribute.content.settings.results|eq('all')}selected{/if} value="all">{'alle'|i18n( 'xrowquestionnaire/datatype/edit' )}</option>
            </select>
        </label>
        
        <label title="{"Sind nur sichtbar sobald die Ergebnisanzeige auf \"eigene\" steht."|i18n( 'xrowquestionnaire/datatype/edit' )}">
             {'Punktspannen'|i18n( 'xrowquestionnaire/datatype/edit' )}
         </label>
         <i>{"Sind nur sichtbar sobald die Ergebnisanzeige auf \"eigene\" steht."|i18n( 'xrowquestionnaire/datatype/edit' )}</i>
        <div id="pointsRange">
            <ul>
            {if is_set( $attribute.content.settings.ranges)}
                {foreach $attribute.content.settings.ranges as $range}
                    {include range=$range uri="design:questionnaire/parts/range.tpl"}
                {/foreach}
            </ul>
            {/if}
        </div>
    <div class="float-break"></div>
    <button class="button" onclick="addRange('{$attribute_base}','{$attribute.id}')" type="button">{'Punktspanne hinzufügen'|i18n( 'xrowquestionnaire/datatype/edit' )}</button>
    <h3 class="left class-description">{'Benötigte Userattribute'|i18n( 'xrowquestionnaire/datatype/edit' )}</h3>
    <br />
    {def $class_attributes = fetch( 'class', 'attribute_list', hash( 'class_id', ezini('UserSettings', 'UserClassID' ) ) )}
    {foreach $class_attributes as $attr}
         <label>
             <input value="{$attr.id}" type="checkbox" name="{$attribute_base}_xrowquestionnaire[{$attribute.id}][settings][user_attributes][{$attr.id}]" {if and(is_set($attribute.content.settings.user_attributes),$attribute.content.settings.user_attributes|contains($attr.id))}checked="true"{/if} />
             {$attr.temporary_object_attribute.contentclass_attribute_name|wash}
         </label>
    {/foreach}
    {undef $class_attributes}
</div>
<div class="context-information">
    <h3 class="left class-description">{'Fragen und Antworten'|i18n( 'xrowquestionnaire/datatype/edit' )}</h3>
    <div class="break"></div>
<div id="tabsView" class="tabsView">
            {if is_set( $attribute.content.questions )}
                {foreach $attribute.content.questions as $question}
                    {include question=$question uri="design:questionnaire/parts/tab.tpl"}
                {/foreach}
            {/if}
{*TABSVIEW*}
</div>

    <p>
        <button class="button" onclick="add('{$attribute_base}', '{$attribute.id}', '{$attribute.contentobject_id}', '{$attribute.version}')" type="button">{'Frage hinzufügen'|i18n( 'xrowquestionnaire/datatype/edit' )}</button>
    </p>
</div>