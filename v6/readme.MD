## Sirportly & WHMCS Integration v6 (BETA)

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

#### Customer Data Frame

Data frames allow you to easily display additional information about your customers directly in Sirportly.  You can configure these frames to automatically grab HTML from WHMCS using this module.

To setup the Data Frame firstly open your config.php file located at ```WHMCS_ROOT/includes/sirportly/config.php``` and set the `$sirportlyFrameKey` variable to a **secure** token, this will be used by Sirportly to authenticate itself with your installation.

Next, within Sirportly head to the Data Frames page (Admin > Workflow > Data Frames), hit **Add New Data Frame** and enter the following;

* `Name` - Enter a memorable name for the data frame
* `URL` - Here you need to enter the base URL of WHMCS plus /modules/support/sirportly/frame.php For example ```http://www.example.com/modules/support/sirportly/frame.php```
* `Auth Key` - Enter the same value as you entered in your Sirportly config.php file

> Please note that the Sirportly template for WHMCS has been created to be compatible with the 'Six' WHMCS template. You may need to adjust the template files if you are using any other template.

## Single Sign On
'SSO' allows you to integrate your support centres with WHMCS, all you need to do is navigate to your Sirportly administration area > Ticket Submission > Support Centres > *click support centre name* and enter;

`Single sign-on (SSO) URL` This is the URL to your WHMCS installation and the following path `/modules/support/sirportly/sso.php`

Finally, open your config.php file located at ```WHMCS_ROOT/includes/sirportly/config.php``` and set the `$sirportlySSOAdminId` variable to an ID of one of your WHMCS admin accounts, this is used internally by WHMCS to use the internal API for authenticating.
