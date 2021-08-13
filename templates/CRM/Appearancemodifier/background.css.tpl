{if $backgroundColor == 'transparent' }
{literal}
<style>
*:not(select, option) {
    color: white;
}
::placeholder {
    color: white;
}
/* overwrite the color of the custom field checkboxes */
.crm-container td.labels label {
    color: white;
}
.crm-container textarea.crm-form-textarea,
.crm-container input.crm-form-text {
    background: transparent;
    background-image: none;
    color: white;
}
.crm-container textarea.crm-form-textarea:focus,
.crm-container input.crm-form-text:focus {
    background: white;
    transition: all .5s;
    color: black;
}
/* The help block has background color that has to be overwritten. Also its border needs to be removed */
div#crm-profile-block.crm-container > div.messages.help {
    background: transparent;
    border: none;
}
</style>
{/literal}
{else}
{literal}
<style>
header.content-header,
.adminimal header.content-header,
div#branding,
div#page,
body,
body.path-civicrm,
body.page-civicrm.page-civicrm-profile.page-civicrm-profile-create,
div.crm-section fieldset,
div.crm-group fieldset,
[class*="appearancemodifier-"] .messages.status.no-popup {
    background-color: {/literal}{$backgroundColor}{literal};
}
</style>
{/literal}
{/if}
