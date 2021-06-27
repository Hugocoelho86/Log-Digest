# logdigest #

TODO Describe the plugin shortly here.
https://www.mraffaele.com/labs/php-date-format-generator/
https://dev.mysql.com/doc/refman/8.0/en/error-log-format.html


TODO Provide more detailed description here.

TO DO:

Add delete function to controller, validating input, model should only receive the request with epoch. Done
Reverse array to insert in same order as file. Done
Create controller and model functions to retrieve last line read. Done.
https://www.codekru.com/java/apache-log-file-parsing
Insert MySQL logs. Done.
Create local_logdigest_config to save db retention time, cycle time.
Implement task api to cycle on moodle. Basic logging.
Create relatorio



## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/logdigest

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2021 Tiago Nunes

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
