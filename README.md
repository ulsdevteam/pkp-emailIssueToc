# Email Issue ToC plugin for OJS/OMP

Plugin for OJS, sends the table of content in the notification email

## Requirements

* OJS/OMP 3.1.2 or later

## Configuration

Install this as a "generic" plugin in OJS.  The preferred installation method is through the Plugin Gallery.  To install manually via the filesystem, extract the contents of this archive to a "emailIssueToc" directory under "plugins/generic" in your OJS root.  To install via Git submodule, target that same directory path: `git submodule add https://github.com/shanu17/pkp-emailIssueToc plugins/generic/emailIssueToc` and `git submodule update --init --recursive plugins/generic/emailIssueToc`. Run the installation script to register this plugin, e.g.: `php lib/pkp/tools/installPluginVersion.php plugins/generic/emailIssueToc/version.xml`.

Login as a Journal Manager and navigate to any context.  Enable the plugin via Login -> Settings -> Website -> Plugins -> Email Issue TOC -> Enable.

## Author / License

Written by Sudheendra Kusume for the [University of Pittsburgh](http://www.pitt.edu).  Copyright (c) University of Pittsburgh.

Released under a license of GPL v2 or later.
