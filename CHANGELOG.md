## Changelog for 1.1

### Features

Feature | Description
------------- | -------------
DIAM-61 | fixed in DIAM-1031 
DIAM-65 | **Open**
DIAM-307 | **Open**
DIAM-408 | **Open**
DIAM-588 | **Open**
DIAM-722| When a ticket is created, the customer or administrator who created this ticket and the user responsible for the ticket workflow in the specified channel/branch receive email notifications confirming that the ticket has been successfully created. Previously, this email contained only ticket key and both customer and administrator had to look for this ticket in the system manually. Now, a direct link to the ticket has been added to the email of a customer and admin user, the portal and to the backend accordingly.
DIAM-740, 741, 742, 743, 744, 745 | In the new version of the DiamanteDesk application, the **Watchers** functionality has been added. A **Watcher** is a person who gets an email notification every time the status, priority or other information regarding this specific ticket changes, ensuring more control over the ticket workflow. To learn more about it, take a look at the corresponding section of the DiamanteDesk User Guide, covering information about [Tickets](http://docs.diamantedesk.com/en/latest/user-guide/tickets.html).
DIAM-769 | In the previous version of the DiamanteDesk we have added the branch tagging functionality which serves for quick search and classification of the branches in the system. In current version this functionality is also available for tickets. To learn more about tagging in DiamanteDesk, refer to this [link](http://docs.diamantedesk.com/en/latest/user-guide/tagging.html).
DIAM-783 | ??
DIAM-784, 785, 786, 787, 788, 789 | 
**DIAM-856** | Mass actions have been added to the **Branch** and **Ticket** menus, allowing to perform the same action to multiple branches or tickets at the same time. A mass deleting option has been added to the branches functionality and the following mass actions are available for tickets: _Change Status_, _Assign_, _Delete Tickets_, _Move_, _Watch_.
DIAM-953 | **Open**
DIAM-954 | **Open**
DIAM-955 | **Open**
DIAM-957 | **Open**
DIAM-958 | **Open**
DIAM-983 | DiamanteDesk may serve as a standalone application or as an extension to various CMS and CRM software. DiamanteDesk is currently integrated with the open-source OroCRM, providing its clients with easy customer support solution. The help desk functionality (**Branches**, **Tickets**, **Customers**, **Reports**, etc.) is available at the **Desk** top menu.
DIAM-984 | ???
DIAM-985 | 
DIAM-986 | In the Oro platform all the contacts related to any business activities are saved at Customers > Contacts. Please refer to the Oro documentation to learn more about contacts in OroCRM. Due to the DiamanteDesk integration with Oro, when a customer registers on the support portal to make a request or report an issue regarding the supported entity (online store, blog, etc.), the provided credentials are added both to the DaiamnteDesk and to the OroCRM contacts. To learn how to configure this option, refer to the Configuration section of this article. When a user registeres on the portal, the system automatically scans the contact database by the existing emails. If none of the emails match the provided credentials, a new contact is created based on the data provided by the user. If an account with the same email has been previously registered in the system, the following warning message is displayed: The identical procedure occurs when OroCRM administartor creates a new DiamanteDesk user from the admin panel at Customers > Contacts > Create Customer. This feature can be disabled at System > Configuration > DiamanteDesk.
DIAM-987 | The Data Audit functionality has been added to the current version of Diamantedesk application. Now, every time a customer or administrator perform any action in the system such as creating or removing users, updating ticket information, addig comments, etc., these changes are added to the read-only event-action history log at _System > Data Audit_. To learn more about Data Audit, please refer to the corresponding [section](http://docs.diamantedesk.com/en/latest/user-guide/data-audit.html) of the documentation.
DIAM-988 | **Open**
DIAM-1031 | **Open**
DIAM-1109 | **Open**
DIAM-1163 | **Open**

### Bugs / Improvements

Issue | Description
------------- | -------------
DIAM-324 | **Open**
DIAM-386 | **Open**
DIAM-584 | When a comment was added to any ticket, the name of a user was not represented as a link to the profile of the comment author. Currently, the name of a user is clickable and it leads to a profile page of the corresponding user, whether he is an administrator (with a profile at _System -> User Management -> required user_) or a customer (with a profile at _Customer > Contacts > required user_).
DIAM-589 | Open 
DIAM-720 | When a ticket is created via email processing, an email notification confirming successful ticket creation is sent to the customer who sent an email with a request or bug reported. This email notification was sent to the customer and administrator, so a customer could also see the email address of an admin user. This has been fixed.
DIAM-836 | **Open**
DIAM-840 | ???
DIAM-842 | When an attempt to add a watcher to the ticket was made, but no specific user was selected from the list in the required field and the user clicked **Add**, an error occured. Now, if no specific user was selected as a watcher, th **Add** button is unavailable.
DIAM-843 | When new watchers were being added to the ticket, the widget content loading failed. This has been fixed.
DIAM-845 |???
DIAM-857 | If a user created a ticket via the portal but did not specify his first and last names, this user was not added to the ticket as a watcher. Currently, if a user did not specify his first name and last name, the system adds the user to the watchers list based on the provided email address which serves as his identificator.
DIAM-858 | Previously, admin user could not remove watchers from the watchers list of any ticket. This issue has been fixed.
DIAM-860 | An issue with **Watchers** functionality occurred. When a user other than ticket creator was added to the watchers list, this user could not see this ticket on the web portal of DiamanteDesk.
DIAM-867 | **Open**
DIAM-868 | When a new ticket was created via email processing and the additional email was specified in CC, the email from the CC should have been added to the watchers list although this functionality did not work properly. This issue has been fixed.
DIAM-874 | ???
DIAM-876 | ???
DIAM-880 | An email template has been updated. A minor issue with extra paragraph in the **Description** field of the email template has been fixed.
DIAM-881 |
DIAM-895 | **Open**
DIAM-896 | **Open**
DIAM-897 | **Open**
DIAM-903 |
DIAM-908 |
DIAM-909 |
DIAM-914 |
DIAM-929 |
DIAM-1146 | Previously, when tickets were created via email processing, **Status** and **Priority** fields were left empty and were not populated with any value. Now, the system automatically sets medium priority and open status for any ticket created through the email processing. To learn more about email processing in DiamanteDesk, refer to the correspondong [section](http://docs.diamantedesk.com/en/latest/user-guide/channels/email-processing.html) of the documentation.

