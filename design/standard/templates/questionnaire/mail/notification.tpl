Guten Tag {$user.contentobject.name},

ein neues Quiz "{$node.object.name}" steht Ihnen zur Teilnahme auf unserer Seite http://{$hostname}{$node.url_alias|ezurl(no)} bereit.


Wenn Sie diese Benachrichtigung nicht mehr erhalten möchten, klicken Sie bitte diesen Link: http://{$hostname}{concat( 'questionnaire/optout/', $hash)|ezurl(no)}

{set-block scope=root variable=subject}Neues Quiz{/set-block}
