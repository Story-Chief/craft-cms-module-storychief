# craft-cms-module-storychief
A Craft plugin for integrating Story Chief into Craft CMS.

## Features
- Sync your Story Chief articles to a craft entry type
- Define your field mapping as you see fit
- Craft 2.5+ compatible

## Installation
- Upload the plugin to your plugin directory and activate it.
- Save your Story Chief key and choose your entry type
- Map your fields

## Csrf Protection
If you have CSRF checks enabled, Craft needs the csrf token to be present on all inbound post requests to controllers. Because Story Chief is posting to the site externally, and can't include that token as part of their request, Craft will reject the inbound calls.

Because the cause of the issue is part of the Craft core, and there's no direct way to override the checks at the controller level, instead we have to fix it at the config level.

### Fix
You must create an exception for the route in your general config. In general.php, where you're setting the enableCsrfProtection, update it to the the following:
```
'enableCsrfProtection' => (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] != '/storychief/webhook'),
```

