{literal}
<style>
*:not(select, option),
::placeholder,
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
</style>
{/literal}
