{crmScope extensionKey='appearancemodifier'}
<div class="crm-block crm-form-block">
    <table class="form-layout">
        <tr>
            <td class="label">{$form.preset_handler.label}</td>
            <td class="content">{$form.preset_handler.html}<br/>
                <span class="description">{ts}Use the saved settings or customize from scratch.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.layout_handler.label}</td>
            <td class="content">{$form.layout_handler.html}<br/>
                <span class="description">{ts}Layout manipulation.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.background_color.label}</td>
            <td class="content">{$form.background_color.html}<br/>
                <span class="label">{$form.original_color.label}{$form.original_color.html}</span><br/>
                <span class="label">{$form.transparent_background.label}{$form.transparent_background.html}</span><br/>
                <span class="description">{ts}Background color manipulation.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.font_color.label}</td>
            <td class="content">{$form.font_color.html}<br/>
                <span class="label">{$form.original_font_color.label}{$form.original_font_color.html}</span><br/>
                <span class="description">{ts}Font color manipulation.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.additional_note.label}</td>
            <td class="content">{$form.additional_note.html}<br/>
                <span class="description">{ts}The text after the submit button.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.consent_field_behaviour.label}</td>
            <td class="content">{$form.consent_field_behaviour.html}<br/>
                <span class="description">{ts}Set the behaviour of consent fields. (opt-out vs opt in vs implied){/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.add_placeholder.label}</td>
            <td class="content">{$form.add_placeholder.html}<br/>
                <span class="description">{ts}Add placeholders to the text inputs.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.hide_form_labels.label}</td>
            <td class="content">{$form.hide_form_labels.html}<br/>
                <span class="description">{ts}Hide the labels of the text inputs on the petition form. Applied only if the placeholders are added.{/ts}</span>
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
{foreach from=$consentActivityFieldNames item=FieldName}
        <tr>
            <td class="label">{$form.$FieldName.label}</td>
            <td class="content">{$form.$FieldName.html}</td>
        </tr>
{/foreach}
    </table>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
{/crmScope}
