{crmAPI var="modifiedEvent" entity="AppearancemodifierEvent" action="get" version="3" event_id=$event.id}

{include file="CRM/Appearancemodifier/commonmodifiedsettings.tpl" modifiedSetting=$modifiedEvent}

{include file="CRM/Event/Form/Registration/Confirm.tpl"}
