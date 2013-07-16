<p>Ihr user Profil ist nicht vollständig.</p>
{if is_set($missing)}
<ul>
{foreach $missing as $miss}
<li>{$miss.name|wash} </li>
{/foreach}
</ul>

<p>Bitte Profil <a href={'user/edit'|ezurl()}>ändern</a>, um fortzufahren.</p>
{/if}