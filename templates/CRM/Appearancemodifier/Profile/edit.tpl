{crmAPI var="modifiedProfile" entity="AppearancemodifierProfile" action="get" version="3" uf_group_id=$groupId}
{if $modifiedProfile.count eq '1' && isset($modifiedProfile.values[0].background_color)}
    {assign var=backgroundColor value=$modifiedProfile.values[0].background_color}
    {include file="CRM/Appearancemodifier/background.css.tpl"}
{/if}

{include file="CRM/Profile/Form/Edit.tpl"}

{if $modifiedProfile.count eq '1' && isset($modifiedProfile.values[0].outro)}
    <div class="appearancemodifier-outro">{$modifiedProfile.values[0].outro}</div>
{/if}
