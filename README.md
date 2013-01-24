# UCF Alert Theme

Theme that is built to fetch alert data from Roam Secure (or some other external source), create editable post data in WordPress, and generate a new feed for use by ucf.edu.

## Required Plugins
(none)

## Installation Requirements
* Set up a cron job to run functions/check-roam-secure.php.  This should be enabled even if we have automatic feed grabbing from Roam Secure turned off within the theme. 

## Deployment

This theme relies on Twitter's Bootstrap framework. The Bootstrap project (http://github.com/twitter/bootstrap) is added as submodule in static/bootstrap. To compile Bootstrap:

1. If this is a brand new clone, run `git submodule update --init static/bootstrap`
2. cd to the static/bootstrap directory and checkout the branch 'base'