<p>Ihr Benutzerprofil ist nicht vollständig.</p>
{if is_set($missing)}
<p>Folgende Elemente fehlen, um fortzufahren:</p>
<ul>
{foreach $missing as $miss}
<li>{$miss.name|wash} </li>
{/foreach}
</ul>

<p>Bitte Profil <a href={'user/edit'|ezurl()}>ändern</a>, um fortzufahren.</p>
{/if}