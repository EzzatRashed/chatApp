# chatApp
This is a **demo** version of a live chat application using php and ajax, it's beginner friendly and can be used as a core for more advanced applications.

## Basic Features
* No registration is required.
* You can chat with anyone you want.
* It uses **zero** frameworks and libraries.

## How It Works?!
* When a user first logs in, the application creates a cookie on the user's browser, this cookie is used to identify the user and it expires after 3 days of idle use of the app or after logging out, but it does not expire as long as the user is still active.
* Every user that has been logged in before, has a row on the db users' table, if the timestamp of `user_last_active` column exceeds 3 days, the user row and all his conversations and messages will be deleted, and this Nickname would be usable again. 

## How to Use?
1. First you need to clone this repository into your computer, and put it to your public_html directory, or in my case in 'htdocs' inside 'xampp' files.
2. Then go to your phpmyadmin page and import 'chatapp.sql' to create the database.
3. Change the PDO parameters in 'db.php' file if needed.
4. Open your browser then go to the localhost and open 'chatapp'.
5. To try the application alone you need to open another browser and login with a different user name.
 

## About the API
The chat API responds to calls with HTML text instead of JSON format.
I intended to make it simple as possible without using any javascript libraries to convert JSON to HTML.
If you want to use JSON instead to reduce server traffic, you can rebuild the api and combine it with one of those libraries:
1. Mustache -- https://github.com/janl/mustache.js
2. JsRender -- https://github.com/BorisMoore/jsrender
3. Or you can use ES6's template literals, but still not supported in all browsers.
