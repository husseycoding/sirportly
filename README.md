Sirportly
===============
Manage all of your Sirportly ticketing from Magento admin.

Description
-----------
Sirportly is a helpdesk tool which greatly improves your abiity to provide customers with quick and efficient support.

This extension gives you direct Magento to Sirportly integration allowing you to create and update tickets from the admin order page, create tickets from requests sent through the standard Magento contact form, and also create tickets when a checkout payment fails.  Additionally it provides a dataframe URL path to display order information in Sirportly.

It also allows you to grant permissions to Magento admin users so that you can define how they are able to interact with the ticketing.

Usage
-----
Admin settings can be found under System -> Configuration -> Hussey Coding -> Sirportly and are as follows:

**API Credentials**
Enter the API credentials for the Sirportly account you wish to integrate with

**General Settings**
Enter your Sirportly domain for proper URL creation and configure the dataframe

**Frontend Ticket Defaults**
Set ticket defaults for status, priority, department and team

**Frontend Contact Form Integration**
Enable contact form ticketing and optionally set ticket defaults used if not using Frontend Ticket Defaults

**Frontend Payment Failed Integration**
Enable failed payment ticketing and optionally set ticket defaults used if not using Frontend Ticket Defaults

**Admin Order Screen Ticket Defaults**
Optionally set the ticket default for creating a new ticket when viewing orders in admin

User restriction settings are found when editing each user under System -> Permissions -> Users in the Sirportly tab, and they are as follows:

**User Permissions**
Full read/write permissions on tickets, read only, or no permissions at all (cannot see any tickets)

**Sirportly User**
Create and update tickets as a specific Sirporlty user, or any Sirportly user

**View Tickets**
User can view tickets just assigned to them, or all tickets assigned to their team

Support
-------
If you have any problems with this extension, open an issue on GitHub

Contribution
------------
Contributions are welcomed, just open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Jonathan Hussey
[http://www.husseycoding.co.uk](http://www.husseycoding.co.uk)
[@husseycoding](https://twitter.com/husseycoding)

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2015 Hussey Coding
