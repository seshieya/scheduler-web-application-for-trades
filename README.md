# Scheduler Web Application for Trades/Contractors
My current job involves manually creating work schedules in Microsoft Word. Application will make schedule creation easy and more automated. Schedules can be automatically emailed to trades/contractors at a time specified by the user.

## Current Highlights:
- Designed front-end and back-end architecture based on Model-View-Control concepts.
- Developed regular expressions and functions with PHP to generate dates that fall on Monday to Friday (excludes weekends). Dates generation is based on a start date that the user provides.
-	Built function to insert or remove more rows of inputs when user clicks add or delete buttons, and send these additional inputs to be stored to a database.

## Other main features for the project that I have not yet implemented:
- Each employee will have a username and login. Schedules can only be created by verified users. Will use Zend Framework's Bcrypt library to store the usernames and passwords.
- Upon emailing a schedule to a trade, there will be a confirmation button in the email. If the trade clicks on the button, it will open up a browser window and redirect the trade to the Scheduler app, where it will trigger the server to send an email to the employee that created the schedule. The email will indicate that the trade has viewed and confirmed that they will be attending as per the date listed on the schedule.
