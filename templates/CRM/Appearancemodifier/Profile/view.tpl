{crmAPI var="modifiedProfile" entity="AppearancemodifierProfile" action="get" version="3" uf_group_id=$groupID}

{include file="CRM/Appearancemodifier/commonmodifiedsettings.tpl" modifiedSetting=$modifiedProfile}

{include file="CRM/Profile/Page/View.tpl"}
