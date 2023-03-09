{crmScope extensionKey='appearancemodifier'}
    <div class="crm-block crm-form-block">
        {include file="CRM/Appearancemodifier/Form/CommonFormItems.tpl"}
        <h3>{ts}Event Settings{/ts}</h3>
        <table class="form-layout">
            <tr>
                <td class="label">{$form.custom_social_box.label}</td>
                <td class="content">{$form.custom_social_box.html}<br/>
                    <span class="description">{ts}Customized box for sharing the event on social network.{/ts}</span>
                </td>
            </tr>
            <tr>
                <td class="label">{$form.external_share_url.label}</td>
                <td class="content">{$form.external_share_url.html}<br/>
                    <span class="description">{ts}The external url that will be shared from the custom social box.{/ts}</span>
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
