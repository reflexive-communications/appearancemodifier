# appearancemodifier

[![CI](https://github.com/reflexive-communications/appearancemodifier/actions/workflows/main.yml/badge.svg)](https://github.com/reflexive-communications/appearancemodifier/actions/workflows/main.yml)

This extension provides an administration interface and functionality to overwrite the layout and basic styles of profile, petition and event forms.
These forms can be embedded on third party pages (e.g. your homepage). The default style might be different from your site.
This tool modifies the HTML of the forms and adds further `css` files to be loaded, so forms will have the same appearance as your site and integrate seamlessly into.
Possible to use preset configurations and apply them on the forms.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

-   PHP v7.3+
-   CiviCRM v5.38+
-   rc-base

## Installation

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/reflexive-communications/appearancemodifier.git
cv en appearancemodifier
```

## Getting started

For profiles, petitions and events there is a new button available: `Customize` where you can config potential customizations.
For details check the [Developer Notes](DEVELOPER.md).

## Known Issues

If someone creates a profile, petition or event during the install process, it is possible thai it will be skipped from the update process.
In this case the appearance modified entity entry has to be created manually (eg: api explorer).
