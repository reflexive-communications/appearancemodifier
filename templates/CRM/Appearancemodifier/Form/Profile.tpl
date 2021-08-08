<div class="crm-block crm-form-block">
    <table class="form-layout">
        <tr>
            <td class="label">{$form.preset_handler.label}</td>
            <td class="content">{$form.preset_handler.html}<br/>
                <span class="description">{ts}Use the saved settings or customize form scratch.{/ts}</span>
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
                <span class="description">{ts}Background color manipulation.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.additional_note.label}</td>
            <td class="content">{$form.additional_note.html}<br/>
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
            <td class="label">{$form.add_placeholder.label}</td>
            <td class="content">{$form.add_placeholder.html}<br/>
                <span class="description">{ts}Add placeholders to the text inputs.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.hide_form_labels.label}</td>
            <td class="content">{$form.hide_form_labels.html}<br/>
                <span class="description">{ts}Hide the labels of the text inputs on the profile form. Applied only if the placeholders are added.{/ts}</span>
            </td>
        </tr>
    </table>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
