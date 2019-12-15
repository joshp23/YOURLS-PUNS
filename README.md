# YOURLS-PUNS
Plugin update notificaiton system for YOURLS

This plugin will check for plugin updates for any plugin that
-  Is hosted on a supported host
-  Has a supported host URI in the plugin header
-  Uses properly tagged releases (semantic versioning or otherwise) in the plugin's header and on the supported host.

Supported hosts:
- GitHub (compatible with GitHub Pages URLs)
- BitBicket
- GitLab (gitlab.com, gitgud.io, framagit.org, git.gnu.io)

This plugin provides and API action intended for use with a cron job, and can optionally send an email to notify admin of updates.

### For berevity:

The PUNS plugin will "call home" to the URI listed in the plugin's header. For instance, PUNS will check itself against the following data:  

`Plugin URI: https://github.com/joshp23/YOURLS-PUNS`.  

It will check this repo's latest release tag against the installed version's `Version:` data found in the plugin's header.

To make your plugin PUNS compatible, go to your plugin's repo on a supported host and draft a new release with the same version tag already present as `Version:` in your plugin's header. Following, whenever `Version:` is updated in the plugin's header just make sure to draft a new release on your supported host with the new version number.

#### Requires php-curl

### Support Dev
All of my published code is developed and maintained in spare time, if you would like to support development of this, or any of my published code, I have set up a Liberpay account for just this purpose. Thank you.

<noscript><a href="https://liberapay.com/joshu42/donate"><img alt="Donate using Liberapay" src="https://liberapay.com/assets/widgets/donate.svg"></a></noscript>
