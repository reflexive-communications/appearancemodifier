<div class="crm-block crm-form-block">
    <table class="form-layout">
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
                <span class="description">{ts}Background color manipulation.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.outro.label}</td>
            <td class="content">{$form.outro.html}<br/>
                <span class="description">{ts}The text after the submit button.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.invert_consent_fields.label}</td>
            <td class="content">{$form.invert_consent_fields.html}<br/>
                <span class="description">{ts}Invert the behaviour of consent fields. (opt-out vs opt in){/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.hide_form_labels.label}</td>
            <td class="content">{$form.hide_form_labels.html}<br/>
                <span class="description">{ts}Hide the labels of the text inputs on the petition form.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.petition_message.label}</td>
            <td class="content">{$form.petition_message.html}<br/>
                <span class="description">{ts}The petition message that will displayed in the message input field.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.target_number_of_signers.label}</td>
            <td class="content">{$form.target_number_of_signers.html}<br/>
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
                <span class="description">{ts}The external url that will bw shared from the custom social box.{/ts}</span>
            </td>
        </tr>
    </table>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
