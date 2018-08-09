# YOURLS-PUNS
Plugin update notificaiton system for YOURLS

Supported hosts:
- GitHub (compatible with GitHub Pages URLs)
- BitBicket
- GitLab (gitlab.com, gitgud.io, framagit.org, git.gnu.io)

This plugin will check for plugin updates for any plugin that
-  Is hosted on a supported host
-  Has a supported host URL in the plugin header
-  Uses properly tagged releases (semantic versioning or otherwise).

This plugin provides and API action intended for use with a cron job, and can optionally send an email to notify admin of updates.

#### Requires php-curl
