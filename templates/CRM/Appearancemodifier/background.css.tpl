{if $backgroundColor == 'transparent' }
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
div.crm-group fieldset {
    background: transparent;
}
.crm-container textarea.crm-form-textarea,
.crm-container input.crm-form-text {
    background: transparent;
    background-image: none;
}
/* Overwrite the default behaviour of the trs */
tbody tr:hover,
tbody tr:focus,
/* Overwrite the default behaviour of the text input fields */
.crm-container textarea.crm-form-textarea:focus,
.crm-container input.crm-form-text:focus {
    background: white;
    transition: all .5s;
    color: black;
}
/* Overwrite the default color of the labels inside the hovered tr. */
tbody tr:hover td.labels label,
tbody tr:focus td.labels label {
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
