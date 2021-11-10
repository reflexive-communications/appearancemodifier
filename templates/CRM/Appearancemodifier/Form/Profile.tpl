{crmScope extensionKey='appearancemodifier'}
<div class="crm-block crm-form-block">
    {include file="CRM/Appearancemodifier/Form/CommonFormItems.tpl"}
    <h3>{ts}Profile Settings{/ts}</h3>
    <table class="form-layout">
        <tr>
            <td class="label">{$form.additional_note.label}</td>
            <td class="content">{$form.additional_note.html}<br/>
                <span class="description">{ts}The text after the submit button.{/ts}</span>
            </td>
        </tr>
    </table>
{if $consentActivityFieldNames|@count gt 0}
    {include file="CRM/Appearancemodifier/Form/ConsentActivityItems.tpl"}
{/if}
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
{/crmScope}
