# chatApp
This is a **demo** version of a live chat application using php and ajax, it's beginner friendly and can be used as a core for more advanced applications.

## Basic Features
* No registration is required.
* You can chat with anyone you want.
* It uses **zero** frameworks and libraries. 

## How It Works?!
* When a user first logs in, the application creates a cookie on the user's browser, this cookie is used to identify the user and it expires after 3 days of idle use of the app or logging out, and it does not expire as long as the user is still active.
* Every user that has been logged in before, has a row on the db users' table, if the timestamp of `user_last_active` column exceeds 3 days, the user won't be visible anymore on the app, and this Nickname would be usable again. 
