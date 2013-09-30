<div class="block">

{* User who functions as approver *}
<div class="block">
<fieldset>
<legend>{'Users who will be notified'|i18n( 'design/admin/workflow/eventtype/edit' )}</legend>
{section show=$event.approve_users}
    <table class="list" cellspacing="0">
    <tr>
        <th class="tight">&nbsp;</th>
        <th>{'User'|i18n( 'design/admin/workflow/eventtype/edit' )}</th>
    </tr>
    {section var=User loop=$event.approve_users sequence=array( bglight, bgdark )}
        <tr class="{$User.sequence}">
            <td><input type="checkbox" name="DeleteApproveUserIDArray_{$event.id}[]" value="{$User.item}" />
            <input type="hidden" name="WorkflowEvent_event_user_id_{$event.id}[]" value="{$User.item}" /></td>
            <td>{fetch(content, object, hash( object_id, $User.item)).name|wash}</td>
        </tr>
    {/section}
    </table>
{section-else}
    <p>{'No users selected.'|i18n( 'design/admin/workflow/eventtype/edit' )}</p>
{/section}

<input class="button" type="submit" name="CustomActionButton[{$event.id}_RemoveApproveUsers]" value="{'Remove selected'|i18n( 'design/admin/workflow/eventtype/edit' )}"
       {if $event.approve_users|not}disabled="disabled"{/if} />
<input class="button" type="submit" name="CustomActionButton[{$event.id}_AddApproveUsers]" value="{'Add users'|i18n( 'design/admin/workflow/eventtype/edit' )}"
       {if $event.approve_users}disabled="disabled"{/if} />

</fieldset>
</div>

<div class="block">
<fieldset>
<legend>{'Objects that will be monitored'|i18n( 'design/admin/workflow/eventtype/edit' )}</legend>
{section show=$event.approve_groups}
    <table class="list" cellspacing="0">
    <tr>
        <th class="tight">&nbsp;</th>
        <th>{'Group'|i18n( 'design/admin/workflow/eventtype/edit' )}</th>
    </tr>
    {section var=Group loop=$event.approve_groups sequence=array( bglight, bgdark )}
        <tr class="{$Group.sequence}">
            <td><input type="checkbox" name="DeleteApproveGroupIDArray_{$event.id}[]" value="{$Group.item}" />
            <input type="hidden" name="WorkflowEvent_event_user_id_{$event.id}[]" value="{$Group.item}" /></td>
            <td>{fetch(content, object, hash( object_id, $Group.item)).name|wash}</td>
        </tr>
    {/section}
    </table>
{section-else}
    <p>{'No groups selected.'|i18n( 'design/admin/workflow/eventtype/edit' )}</p>
{/section}

<input class="button" type="submit" name="CustomActionButton[{$event.id}_RemoveObjects]" value="{'Remove selected'|i18n( 'design/admin/workflow/eventtype/edit' )}"
       {if $event.approve_groups|not}disabled="disabled"{/if} />
<input class="button" type="submit" name="CustomActionButton[{$event.id}_AddObjects]" value="{'Add Objects'|i18n( 'design/admin/workflow/eventtype/edit' )}"
       {if $event.approve_groups}disabled="disabled"{/if} />

</fieldset>
</div>

</div>
