## Sirportly & WHMCS Integration

This module is designed to allow clients to open, view and reply to tickets in Sirportly without having to leave WHMCS.

### Installation Instructions

#### Prerequisites

Please note that this module will only work with WHMCS v6+, if you're still using WHMCS 5 please follow the steps in the v5 directory.

#### Installation

1. Take a backup of both your WHMCS installation, both your files and its database
2. Copy all the files from the root of the v6 directory and upload them to your WHMCS installation root directory
3. Rename the file includes/sirportly/config.example.php to config.php
4. Open `<whmcs_root>/includes/sirportly/config.php` and change the values for each of the availble options
5. Import the `table.sql` file into your WHMCS database.
6. Login to your WHMCS admin area. Go to Setup -> General Settings -> Support and set "Sirportly" as the support module

Please note that the Sirportly template for WHMCS has been created to be compatible with the 'Six' WHMCS template. You may need to adjust the template files if you are using any other template.