{crmAPI var="modifiedEvent" entity="AppearancemodifierEvent" action="get" version="3" event_id=$event.id}
{if $modifiedEvent.count eq '1' && isset($modifiedEvent.values[0].background_color)}
    {assign var=backgroundColor value=$modifiedEvent.values[0].background_color}
    {include file="CRM/Appearancemodifier/background.css.tpl"}
{/if}

{include file="CRM/Event/Form/Registration/Confirm.tpl"}
