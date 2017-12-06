# UCF Alert Theme

Theme that is built to fetch alert data from Roam Secure (or some other external source), create editable post data in WordPress, and generate a new feed for use by ucf.edu.

## Required Plugins
* Redirection

## Installation Requirements
* For automatic feed retrieval from Roam Secure, set up a cron job to run `jobs/check-roam-secure.php` at a regular interval.  The "Enable automated retrieval of alerts" theme option will not work without this cron job set up.

## Deployment

This theme relies on Twitter's Bootstrap framework. The Bootstrap project (http://github.com/twitter/bootstrap) is added as submodule in static/bootstrap. To compile Bootstrap:

1. If this is a brand new clone, run `git submodule update --init static/bootstrap`
2. cd to the static/bootstrap directory and checkout the branch 'base'
