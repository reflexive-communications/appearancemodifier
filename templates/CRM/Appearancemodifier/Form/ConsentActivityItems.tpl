{crmScope extensionKey='appearancemodifier'}
<h3>{ts}Consent Activity Settings{/ts}</h3>
<table class="form-layout">
{foreach from=$consentActivityFieldNames item=FieldName}
    <tr>
        <td class="label">{$form.$FieldName.label}</td>
        <td class="content">{$form.$FieldName.html}</td>
    </tr>
{/foreach}
</table>
{/crmScope}
