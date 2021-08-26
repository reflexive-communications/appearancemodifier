{literal}
<style>
*:not(select, option),
::placeholder,
/* overwrite the page-title color for d8 adminimal */
.adminimal h1.page-title,
/* overwrite the page-title color for d7 adminimal */
.adminimal-theme #branding h1.page-title,
/* overwrite the color of the custom field checkboxes */
.crm-container td.labels label,
/* The font color of the textareas has to be also updated */
.crm-container textarea.crm-form-textarea,
.crm-container input.crm-form-text {
    color: {/literal}{$fontColor}{literal};
}
/* Overwrite the datepicker classes. use the original color */
table.ui-datepicker-calendar th[scope="col"] > span {
    color: #222222;
}
div.ui-datepicker-title select {
    color: #222222;
}
</style>
{/literal}
