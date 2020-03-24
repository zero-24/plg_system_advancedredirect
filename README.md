# AdvancedRedirect Plugin

This plugin is based on the Joomla Core Redirect Plugin and acts as a so-called drop in replacement for the Core Plugin. In addition to the Joomla Core Plugin, it allows you to define your own derivation rules.

## Sponsoring and Donation

You use this extension in an commercial context and / or want to support me and give something back?

There are two ways to support me right now:
- This extension is part of [Github Sponsors](https://github.com/sponsors/zero-24/) by sponsoring me, you help me continue my oss work for the [Joomla! Project](https://volunteers.joomla.org/joomlers/248-tobias-zulauf), write bug fixes, improving features and maintain my extensions.
- You just want to send me an one-time donation? Great you can do this via [PayPal.me/zero24](https://www.paypal.me/zero24).

Thanks for your support!

## Release steps

- `build/build.sh`
- `git commit -am 'prepare release AdvancedRedirect 1.0.x'`
- `git tag -s '1.0.x' -m 'AdvancedRedirect 1.0.x'`
- `git push origin --tags`
- create the release on GitHub
- `git push origin master`