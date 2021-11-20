{crmAPI var="modifiedPetition" entity="AppearancemodifierPetition" action="get" version="3" survey_id=$survey_id}

{include file="CRM/Appearancemodifier/commonmodifiedsettings.tpl" modifiedSetting=$modifiedPetition}

{include file="CRM/Campaign/Page/Petition/ThankYou.tpl"}
