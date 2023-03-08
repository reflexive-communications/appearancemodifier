{if $modifiedSetting.count eq '1' && isset($modifiedSetting.values[0].background_color)}
    {assign var=backgroundColor value=$modifiedSetting.values[0].background_color}
    {include file="CRM/Appearancemodifier/background.css.tpl"}
{/if}
{if $modifiedSetting.count eq '1' && isset($modifiedSetting.values[0].font_color)}
    {assign var=fontColor value=$modifiedSetting.values[0].font_color}
    {include file="CRM/Appearancemodifier/font.css.tpl"}
{/if}
{if $modifiedSetting.count eq '1' && isset($modifiedSetting.values[0].custom_settings)}
    {assign var=customSettings value=$modifiedSetting.values[0].custom_settings|@unserialize}
    {if $customSettings.send_size_when_embedded eq 1}
    {literal}
        <script type="text/javascript">
            var allowedMessageReceiver = '{/literal}{$customSettings.send_size_to_when_embedded}{literal}';
        </script>
    {/literal}
    {/if}
{/if}
