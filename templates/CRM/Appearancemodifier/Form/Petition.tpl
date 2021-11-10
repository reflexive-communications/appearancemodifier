{crmScope extensionKey='appearancemodifier'}
<div class="crm-block crm-form-block">
    {include file="CRM/Appearancemodifier/Form/CommonFormItems.tpl"}
    <h3>{ts}Petition Settings{/ts}</h3>
    <table class="form-layout">
        <tr>
            <td class="label">{$form.additional_note.label}</td>
            <td class="content">{$form.additional_note.html}<br/>
                <span class="description">{ts}The text after the submit button.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.petition_message.label}</td>
            <td class="content">{$form.petition_message.html}<br/>
                <span class="label">{$form.disable_petition_message_edit.label}{$form.disable_petition_message_edit.html}</span><br/>
                <span class="description">{ts}The petition message that will displayed in the message input field. If you disable the edit option, the petition message has to be an optional field.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.target_number_of_signers.label}</td>
            <td class="content">{$form.target_number_of_signers.html}<br/>
                <span class="label">{$form.signers_block_position.label}{$form.signers_block_position.html}</span><br/>
                <span class="description">{ts}The target number of the petition signers.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.custom_social_box.label}</td>
            <td class="content">{$form.custom_social_box.html}<br/>
                <span class="description">{ts}Customized box for sharing the petition on social network.{/ts}</span>
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
