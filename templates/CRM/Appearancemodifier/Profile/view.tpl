{crmAPI var="modifiedProfile" entity="AppearancemodifierProfile" action="get" version="3" uf_group_id=$groupID}
{if $modifiedProfile.count eq '1' && isset($modifiedProfile.values[0].background_color)}
    {assign var=backgroundColor value=$modifiedProfile.values[0].background_color}
    {include file="CRM/Appearancemodifier/background.css.tpl"}
{/if}
{if $modifiedProfile.count eq '1' && isset($modifiedProfile.values[0].font_color)}
    {assign var=fontColor value=$modifiedProfile.values[0].font_color}
    {include file="CRM/Appearancemodifier/font.css.tpl"}
{/if}
{include file="CRM/Profile/Page/View.tpl"}
