# AdvancedRedirect Plugin

This plugin is based on the Joomla Core Redirect Plugin and acts as a so-called drop in replacement for the Core Plugin. In addition to the Joomla Core Plugin, it allows you to define your own derivation rules.

## Configuration

### Initial setup the plugin

- [Download the latest version of the plugin](https://github.com/zero-24/plg_system_advancedredirect/releases/latest)
- Install the plugin using `Upload & Install`
- Disable the core `System - Redirect` plugin from the plugin manager
- Enable this plugin `System - AdvancedRedirect` from the plugin manager

Now the initial setup is completed and you can start configure the plugin.

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

## Issues / Pull Requests

You have found an Issue, have a question or you would like to suggest changes regarding this extension?
[Open an issue in this repo](https://github.com/zero-24/plg_system_advancedredirect/issues/new) or submit a pull request with the proposed changes.

## Translations

You want to translate this extension to your own language? Check out my [Crowdin Page for my Extensions](https://joomla.crowdin.com/zero-24) for more details. Feel free to [open an issue here](https://github.com/zero-24/plg_system_advancedredirect/issues/new) on any question that comes up.

## Joomla! Extensions Directory (JED)

This plugin can also been found in the Joomla! Extensions Directory: [AdvancedRedirect by zero24](https://extensions.joomla.org/extension/site-management/url-redirection/advancedredirect/)

## Release steps

- `build/build.sh`
- `git commit -am 'prepare release AdvancedRedirect 1.0.x'`
- `git tag -s '1.0.x' -m 'AdvancedRedirect 1.0.x'`
- `git push origin --tags`
- create the release on GitHub
- `git push origin master`

## Crowdin

### Upload new strings

`crowdin upload sources`

### Download translations

`crowdin download --skip-untranslated-files --ignore-match`
