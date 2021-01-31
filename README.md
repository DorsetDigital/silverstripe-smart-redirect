# silverstripe-smart-redirect

This module provides an interface to create and manage URL redirects based on a number of criteria.

Initially designed to work as an easy method for generating and deploying QR codes, the module can find applications anywhere that conditional redirection may be required (such as URl shorteners, etc.)

Tools are included for redirection based on the browser language, and the user's geolocation (IP based using Maxmind).   A default redirection rule is also included.  Time-based redirects will be available in a future release.

# Requirements & Dependencies
* Silverstripe 4.x

(See composer.json for additional dependencies)


# Installation

Install with composer:

`composer require dorsetdigital/silverstripe-smart-redirect`


# Usage

This is an alpha-stage module.  Full usage and configuration instructions to follow.  Please do not use this in a production environment!   Beta testers welcomed, please get in touch.

A set of supported languages and countries is included with the module.  If you wish to use the geolocation functionality, you will need a Maxmind licence key for the free Geolite 2 countries database.  The module expects this to be set in the environment in: `MAXMIND_LICENCE_KEY`
Once configured, a build task is included to download and extract the more recent Maxmind database.  It is expected that this would be run via cron or using the QueuedJobs module periodically to keep the database current.

# ToDo

* Add internationalisation to all fields / content
* Complete time-based redirection rule
* Improve docs
* Add developer-configurable base URL for redirects
* Suppress rule confguration data in CMS