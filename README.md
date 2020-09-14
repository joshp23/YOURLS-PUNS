# YOURLS-PUNS
**P**lugin **U**pdate **N**otification **S**ystem for YOURLS

This plugin will check for plugin updates for any plugin that

-  Is hosted on a supported host
-  Has a supported host URI in the plugin header
-  Uses properly tagged releases (semantic versioning or otherwise) in the plugin's header and on the supported host.

Supported hosts:

- GitHub (compatible with GitHub Pages URLs)
- BitBicket
- GitLab (gitlab.com, gitgud.io, framagit.org, git.gnu.io)

This plugin provides and API action intended for use with a cron job, and can optionally send an email to notify admin of updates. See the plugin admin page in YOURLS after activation for more details

#### Notes:
- To have this plugin send email using [PHPMailer](https://github.com/PHPMailer/PHPMailer), install [YOURLS-SMTP-contact](https://github.com/joshp23/YOURLS-SMTP-contact)
- Requires php-curl
