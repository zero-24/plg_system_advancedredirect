# AdvancedRedirect Plugin

This plugin is based on the Joomla Core Redirect Plugin and acts as a so-called drop in replacement for the Core Plugin. In addition to the Joomla Core Plugin, it allows you to define your own derivation rules.

## Sponsoring and Donation

You use this extension in an commercial context and / or want to support me and give something back?

There are two ways to support me right now:
- This extension is part of [Github Sponsors](https://github.com/sponsors/zero-24/) by sponsoring me, you help me continue my oss work for the [Joomla! Project](https://volunteers.joomla.org/joomlers/248-tobias-zulauf), write bug fixes, improving features and maintain my extensions.
- You just want to send me an one-time donation? Great you can do this via [PayPal.me/zero24](https://www.paypal.me/zero24).

Thanks for your support!

## Configuration

### Initial setup the plugin

- Download the latest version of the plugin: https://github.com/zero-24/plg_system_advancedredirect/releases/latest
- Install the plugin using Upload & Install
- Enable the plugin form the plugin manager

Now the inital setup is completed and you can start configure the plugin.

### AdvancedRedirect Options

#### Plugin Mode

This plugin has three plugin modes:

| Plugin Mode       |     Description     |
|-------------------|---------------------|
| `Automatic`       | The plugin will try to get an URL to the category overview |
| `URL Hopping`     | The plugin will try to remove the latest part of the URL. |
| `Static redirect` | The plugin will use a static redirect url |

##### Static redirect URL

When the plugin mode is in `Static redirect` mode this field holds the static redirect url.

### Suggest redirection

With this option you can enable whether the plugin should suggest redirection to the 404

##### Status of the suggestion

With this option you can decide whether the suggested redirect is published or not.

### Update Server

Please note that my update server only supports the latest version running the latest version of Joomla and atleast PHP 7.0.
Any other plugin version I may have added to the download section don't get updates using the update server.

## Issues / Translations

You have found an Issue, you have done a translation or have a question / suggestion regarding the plugin?
[Open an issue in this repo](https://github.com/zero-24/plg_system_advancedredirect/issues/new) or submit a pull request with the proposed changes.

## Joomla! Extensions Directory (JED)

This plugin can also been found in the Joomla! Extensions Directory: [AdvancedRedirect by zero24](https://extensions.joomla.org/)

## Release steps

- `build/build.sh`
- `git commit -am 'prepare release AdvancedRedirect 1.0.x'`
- `git tag -s '1.0.x' -m 'AdvancedRedirect 1.0.x'`
- `git push origin --tags`
- create the release on GitHub
- `git push origin master`
