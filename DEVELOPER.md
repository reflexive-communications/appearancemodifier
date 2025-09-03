# Developer Notes

## Hooks

This extension provides hooks, to be able to register further changes on the forms.
You can register handlers or presets.

### `hook_civicrm_appearancemodifierProfileSettings`

This hook is fired when the customize profile admin form is building.
The registered handlers will be appeared on the selector menu.
The handler class has to extend the `CRM_Appearancemodifier_AbstractLayout` class.
The registered preset will be available on the form. Example hook implementation:

```php
function hook_civicrm_appearancemodifierProfileSettings(&$handlers)
{
    $handlers['handlers']['My_Profile_Handler_Class_Name'] = 'Profile Handler label';
    $handlers['presets']['My_Profile_Preset_Provider_Class_Name'] = 'Fancy Profile label';
}
```

### `hook_civicrm_appearancemodifierPetitionSettings`

This hook is fired when the customize petition admin form is building.
The registered handlers will be appeared on the selector menu.
The handler class has to extend the `CRM_Appearancemodifier_AbstractLayout` class.
The registered preset will be available on the form. Example hook implementation:

```php
function hook_civicrm_appearancemodifierPetitionSettings(&$handlers)
{
    $handlers['handlers']['My_Petition_Handler_Class_Name'] = 'Petition Handler label';
    $handlers['presets']['My_Petition_Preset_Provider_Class_Name'] = 'Fancy Petition label';
}
```

### `hook_civicrm_appearancemodifierEventSettings`

This hook is fired when the customize event admin form is building.
The registered handlers will be appeared on the selector menu.
The handler class has to extend the `CRM_Appearancemodifier_AbstractLayout` class.
The registered preset will be available on the form. Example hook implementation:

```php
function hook_civicrm_appearancemodifierEventSettings(&$handlers)
{
    $handlers['handlers']['My_Event_Handler_Class_Name'] = 'Event Handler label';
    $handlers['presets']['My_Event_Preset_Provider_Class_Name'] = 'Fancy Event label';
}
```

## Forms

The form customization is based on additional settings that could be reached from a new menu link. The additional settings are stored in managed entities.

Additionally, customization can be enabled/disabled globally for each form type. For this use the following constant settings in `civicrm.settings.php`:

```php
// Disable Appearance Modifier for petitions but enable for profiles and events
// Note: the default value is true for all form types
define('CIVICRM_RC_APPEARANCEMODIFIER_ENABLED', [
    'profile' => true,
    'petition' => false,
    'event' => true,
]);

// Enabled types can be omitted, as it defaults to true, so the above is equivalent to:
define('CIVICRM_RC_APPEARANCEMODIFIER_ENABLED', [
    'petition' => false,
]);
```

### General settings

- Is Active - Kill switch for the entity. This is off by default, you have to enable it to apply the settings on the form.
- Form Layout - It is the option for extending the template resources and implementing the alterContent for creating further custom changes on the forms.
- Background Color - If the Original Background Color is unchecked, this value will be used as background color on the form. If the Original Background Color is unchecked and the Transparent Background Color is checked, then transparent background will be applied on the form (included the text inputs, their background color will be set to white and color to black on case of focus state).
- Font Color - If the Original Font Color is unchecked, this value will be used as font color on the form.
- Consent Field Behaviour - It provides 3 options. Default (opt-out consent fields), invert (opt-in consent fields), submit implied (set do_not_phone and is_opt_out to false after form submit).
- Add placeholders - If checked, the text inputs will contain placeholder attributes. The value of the placeholder will be the same as the label of the text input.
- Hide text input labels - Only applied when the Add placeholder also applied. If checked the labels of the text inputs will be hidden.
- Hide form title - If this flag is set, the titles will be hidden on the forms and pages.
- Send size to the parent window - When the form is embedded, with this option the size of the form will be sent with javascript method to the parent window.
- Send size to this parent window - When the form is embedded, with this option the size of the form will be sent with javascript method to this parent window. (default: `*`)
- Add check all checkbox - When this feature is set, an additional checkbox will be added to the form right before the first checkbox. When it is checked or unchecked, every other checkbox will be set to the same state as this checkbox.
- Check all checkbox label - The visible label of this checkbox.

### Consent activity extension

If the consentactivity extension is also installed, an additional feature also available on the admin forms.
You can trigger activities if a pseudo privacy field is checked on the form.
The pseudo privacy field - activity map settings is visible if the following conditions met:

- The consentactivity extension is installed and enabled.
- At least one pseudo privacy field is configured on the consentactivity settings form.
- The current form contains at least one pseudo privacy field on the connected profiles.

The pseudo consent fields are displayed as the privacy fields on the forms.

### Profile

The AppearancemodifierProfile entity stores the settings for a profile.

- Additional Note Text - This text will be displayed below the submit button.
- Base target is the parent - With this setting, the target attribute of the base tag will be set to `_parent`. It updates the target attribute of the links and form submissions to the parent window.

### Petition

The AppearancemodifierPetition entity stores the settings for a petition.

- Additional Note Text - This text will be displayed below the submit button.
- Petition Message - This text will be added as default text in the petition message field in the activity profile.
- Disable petition message edit - This prevents the contacts to edit the petition message input field. In this case the petition message has to be optional in the form.
- Target number of signers - This value is used in the progressbar as maximum.
- Display signers block - How to display the current number of signers (just the number, or progressbar when the target number is also set) and where (top, bottom, don't display).
- Custom social box - The sharing options will be replaced with a custom one, that only contains twitter and facebook share option.
- External url to share - This url will be shared from the social boxes. Only applied when the custom social box also applied.

### Event

The AppearancemodifierEvent entity stores the settings for an event.

- Custom social box - The sharing options will be replaced with a custom one, that only contains twitter and facebook share option.
- External url to share - This url will be shared from the social boxes. Only applied when the custom social box also applied.

## Layouts

This extension could be extended with further custom layouts.
The Layout class has to extend the `CRM_Appearancemodifier_AbstractLayout` class.

### Layout implementations

- Extend your class from the `CRM_Appearancemodifier_AbstractLayout` class.
- Implement the setStyleSheets method. It can be empty if you don't need additional css rules to be applied.
- Implement the alterContent method. Manipulate the DOM as you want. The phpQuery is a handy tool for it. If you have custom css rules, don't forget to apply the uniq classname on the main element (#crm-container).
