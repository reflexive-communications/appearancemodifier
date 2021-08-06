# appearancemodifier

This extension provides an administration interface and functionality to overwrite the layout and the basic styles of the public profile forms, the petition forms, the event forms and the pages that are connected to the forms. The forms could be used as embedded forms on third party pages. The default form layout might be different from the third party site. This extension modifies the html of the forms and adds further css files to be loaded. This tool provides an option for adding formatted and longer default value to a textarea input if it is attached to an activity profile. It could be used as default value for an email like petition message.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.3+
* CiviCRM v5.37
* [RC-Base](https://github.com/reflexive-communications/rc-base) v0.8.2+

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/reflexive-communications/appearancemodifier.git
cv en appearancemodifier
```

## Hooks

This extension provides hooks, to be able to register further changes on the forms.

**`hook_civicrm_appearancemodifierProfileLayoutHandlers`**

This hook is fired when the customize profile admin form is building. The registered handlers will be appeared on the selector menu. The handler class has to extend the `CRM_Appearancemodifier_AbstractLayout` class. Example hook implementation:

```php
function myextension_civicrm_appearancemodifierProfileLayoutHandlers(&$handlers)
{
    $handlers['My_Profile_Handler_Class_Name'] = 'Profile Handler label';
}
```

**`hook_civicrm_appearancemodifierPetitionLayoutHandlers`**

This hook is fired when the customize petition admin form is building. The registered handlers will be appeared on the selector menu. The handler class has to extend the `CRM_Appearancemodifier_AbstractLayout` class. Example hook implementation:

```php
function myextension_civicrm_appearancemodifierPetitionLayoutHandlers(&$handlers)
{
    $handlers['My_Petition_Handler_Class_Name'] = 'Petition Handler label';
}
```

**`hook_civicrm_appearancemodifierEventLayoutHandlers`**

This hook is fired when the customize event admin form is building. The registered handlers will be appeared on the selector menu. The handler class has to extend the `CRM_Appearancemodifier_AbstractLayout` class. Example hook implementation:

```php
function myextension_civicrm_appearancemodifierEventLayoutHandlers(&$handlers)
{
    $handlers['My_Event_Handler_Class_Name'] = 'Event Handler label';
}
```

## Forms

The form customization is based on additional settings that could be reached from a new menu link. The additional settings are stored in managed entities.

### Common settings

- Form Layout - It is the option for extending the template resources and implementing the alterContent for creating further custom changes on the forms.
- Background Color - If the Original Background Color is unchecked, this value will be used as background color on the form.
- Invert Consent Fields - If it is checked, the consent fields (`do_not_email`, `do_not_phone`, `is_opt_out`) will behave as opt in fields.
- Add placeholders - If checked, the text inputs will contain placeholder attributes. The value of the placeholder will be the same as the label of the text input.
- Hide text input labels - Only applied when the Add placeholder also applied. If checked the labels of the text inputs will be hidden.

### Profile

The AppearancemodifierProfile entity stores the settings for a profile.

**Profile Customization Menu**
![Profile Customization Menu](./assets/docs/profile-admin-link.png)

**Profile Customization Form**
![Profile Customization Form](./assets/docs/profile-admin-form.png)

- Outro Text - This text will be displayed below the submit button.

**Profile Settings Example**
![Profile Settings Example](./assets/docs/profile-admin-example.png)

**Profile Form Example**
![Profile Form Example](./assets/docs/profile-form-example.png)

### Petition

The AppearancemodifierPetition entity stores the settings for a petition.

**Petition Customization Menu**
![Petition Customization Menu](./assets/docs/petition-admin-link.png)

**Petition Customization Form**
![Petition Customization Form](./assets/docs/petition-admin-form.png)

- Outro Text - This text will be displayed below the submit button.
- Petition Message - This text will be added as default text in the petition message field in the activity profile.
- Target number of signers - Currently unused. Needed in the progressbar feature.
- Custom social box - The sharing options will be replaced with a custom one, that only contans twitter and facebook share option.
- External url to share - Currently unused. Needed in the custom social box share external page feature.

**Petition Settings Example**
![Petition Settings Example](./assets/docs/petition-admin-example.png)

**Petition Form Example**
![Petition Form Example](./assets/docs/petition-form-example.png)

### Event

The AppearancemodifierEvent entity stores the settings for an event.

**Event Customization Menu**
![Event Customization Menu](./assets/docs/event-admin-link.png)

**Event Customization Form**
![Event Customization Form](./assets/docs/event-admin-form.png)

- Custom social box - The sharing options will be replaced with a custom one, that only contans twitter and facebook share option.
- External url to share - Currently unused. Needed in the custom social box share external page feature.

**Event Settings Example**
![Event Settings Example](./assets/docs/event-admin-example.png)

**Event Form Example**
![Event Form Example](./assets/docs/event-form-example.png)

## Layouts

The functionality of this extension could be extended with further custom layouts. The Layout class has to extend the `CRM_Appearancemodifier_AbstractLayout` class.

### Abstract layout

The setStyleSheets method is called at that points when the pages could be extended with further css resources.
The alterContent method is called after the SSR process of a template and ready for the manipulation.
The className method is a handy helper class that provides you a classname prefix, that could be used for writing css rules.

### Layout implementations

- Extend your class from the `CRM_Appearancemodifier_AbstractLayout` class.
- Implement the setStyleSheets method. It can be empty if you don't need additional css rules to be applied.
- Implement the alterContent method. Manipulate the DOM as you want. The phpQuery is a handy tool for it. If you have custom css rules, don't forget to apply the uniq classname on the main element (#crm-container).
