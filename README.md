# YOURLS-PUNS
Plugin update notificaiton system for YOURLS

This plugin will check for plugin updates for any plugin that
-  Is hosted on GitHub
-  Has the GitHub URL in the plugin header
-  Uses properly tagged releases on GitHub (semantic versioning or otherwise).

This plugin provides and API action intended for use with a cron job, and can optionally send an email to notify admin of updates.

#### Requires php-curl
