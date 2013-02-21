<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
<div id="voting-box-{$attribute.id}">
    <form id="form_{$attribute.id}" method="post" action="">
        <input type="hidden" name="attribute_id" value="{$attribute.id}" />
        <input type="hidden" name="contentobject_id" value="{$attribute.contentobject_id}" />
        <input type="hidden" name="language_code" value="{$attribute.language_code}" />
        <input type="hidden" name="version" value="{$attribute.version}" />
    </form>
</div>

{literal}
    <script type="text/javascript">
    <!--
    jQuery(document).ready((function() {
    	jQuery('#form_{/literal}{$attribute.id}{literal}').questionnaire( 'submit' );
    }));
    //-->
    </script>
{/literal}