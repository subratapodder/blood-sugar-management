# blood-sugar-management
A custom module for managing users blood sugar information
There will be two user types - admin and user. 

User Flow:

-	Users should be able to register to the system - Email, Full Name. No password is set at this point. Once submitted, it will send them an email validation link. Clicking on that link will take them to a page with just password fields. Once done, the user logs in.
-	Once logged in, users should land on a page called “My Space” as Local menu task which is basically a dashboard, containing the following sections:
  - A simple form to enter blood sugar record, text field that allows values between 0 - 10, decimals are possible
  - A table below showing the already entered data by the current user (SL No, BS Level, Date & Time will be the columns)
  - A table showing the prescriptions (files uploaded so far), simple text field to search by file name, and a button to add a file. Columns will be SL No, File Name, Description, File Size, Date. On clicking the button, a simple modal (bootstrap) form to open up with a file field and a description	 field, where user can upload the file and add some description.


Admin Flow:
-	Admins can be created by other admins
-	Once admin logs in, admin goes to a dashboard - page called “Admin Dashboard”, where he can get the following:
- A table of all users, with ability to filter by email
- A table showing all blood sugar entries, of all users. Same table as user, just with the addition of the email column.
- A table showing all prescription entries, of all users. Same table as user, just with the addition of the email column.
- Action button to create admin, and an action button to go to settings page to set the minimum refresh time of the BS level data through the form for user. More explanation on this below.

Restrictions:
1.	Users can enter any values between 0 - 10. Decimal numbers are allowed. 
2.	(this should be settable by admin from a module settings form). Till this time is passed, no data entry will be possible and the form will disappear giving a message to let user know how many more minutes are remaining. 


 
