<div class="questionnaire captcha">

{if ezini( 'RecaptchaSetting', 'PublicKey', 'xrowquestionnaire.ini' )|eq('')}
<div class="message-warning">
	{'reCAPTCHA API key missing.'|i18n( 'xrowquestionnaire/vote/form' )}
</div>
{else}
<form id="form_{$attribute.id}" method="post" action="">
		<input type="hidden" name="attribute_id" value="{$attribute.id}" />
		<input type="hidden" name="contentobject_id" value="{$attribute.contentobject_id}" />
		<input type="hidden" name="language_code" value="{$attribute.language_code}" />
		<input type="hidden" name="version" value="{$attribute.version}" />
		<input type="hidden" name="tabindex" value="{$attribute.version}" />
		<input type="hidden" id="captcha_lang" name="captcha_lang" value="{ezini( 'RecaptchaSetting', 'Language', 'xrowquestionnaire.ini' )}" />
		<input type="hidden" id="captcha_tabindex" name="captcha_tabindex" value="ezini( 'RecaptchaSetting', 'TabIndex', 'xrowquestionnaire.ini' )" />
		<input type="hidden" id="captcha_theme" name="captcha_theme" value="{ezini( 'RecaptchaSetting', 'Theme', 'xrowquestionnaire.ini' )}" />
		<input type="hidden" id="captcha_key" name="captcha_key" value="{ezini( 'RecaptchaSetting', 'PublicKey', 'xrowquestionnaire.ini' )}" />
		<input id="recaptcha-{$attribute.id}" type="hidden" name="recaptcha" />
		<div id="captcha_{$attribute.id}"></div>

		<input onclick="jQuery('#form_{$attribute.id}').questionnaire( 'submit' );" id="question_submit{$question.id}" class="question_submit" type="button" name="submit_vote{$question.id}" value="Weiter" title="{'Lösen Sie das Rätsel.'|i18n( 'xrowquestionnaire/datatype/edit' )}" />
</form>
{/if}
{if is_set($errors)}
    <div class="message-warning">
        {foreach $errors as $error}
            {$error}<br />
        {/foreach}
    </div>
{/if}
</div>