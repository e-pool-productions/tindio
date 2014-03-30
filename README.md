### Tindio - the tiny studio.
Tindio is a production and workflow management platform for Film and VFX projects based on the codeigniter framework. It was build for small groups of people which want to organize their projects without using oversized programms designed primarily for a large amount of people. It's build on a 'Project', 'Scene', 'Shot', 'Task' hierarchy and therefor has some roles like 'Admin', 'Director', 'Supervisor' and 'Artist'.

### Features
After you logged in into your working space you will find the dashboard with all important information like 'New Projects', 'My Deadlines' or 'Notifications' and a global overview of the projects on Tindio.
You will have an area where you can see all your projects, assets and your current work to do. Moreover there are some useful tools which will help you to either simplify your work or keep track of your deadlines. With the 'Workflow Editor' it's fast and simple to predefine some tasks. Later, all you have to do is to add the workflow to a shot.

### What do I need?
Tindio is a very simple and easy to use platform. All you need is

1. a webhosting Service or Server where you can upload your copy to
2. a mySQL-Database

(To use all the functions of Tindio Javascript has to be enabled in your browser)

### How to install?
Download the .zip file and unpack it. Next go to application/config/constants.php and change the url of
```
define('URL', 'http://localhost/tindio/');
```
to whatever url you have. For example if your server runs under http://www.example.com then you will change it to
```
define('URL', 'http://www.example.com');
```

(NOTICE! SINCE YOU WILL DEAL WITH DIFFERENT ACCOUNTS IT'S HIGHLY RECOMMENDED TO USE SSL FOR MORE SECURITY! WE CAN'T PROVIDE THAT FOR YOU!)

Now go to application/config/database.php and fill the following with your information:
```
$db['default']['hostname'] = 'localhost';       // your database hostlocation (normally localhost)
$db['default']['username'] = 'username';        // your database username
$db['default']['password'] = 'password';        // your database password
$db['default']['database'] = 'tindio';          // the name of your database
```
Please keep the ' and just change the information inside after the equals.

At last login to your database management system (e.g. phpMyAdmin) and import tindio.sql.
After you have done that just upload all files and folders to your webhosting Service.
That's it!

### How do I start?
Since you need an account to work with Tindio there is an default account with administration rights.
```
Username: Admin
Password: administrator
```

PLEASE! AFTER YOU LOGGED IN THE FIRST TIME CHANGE THE PASSWORD FOR THE ADMINISTRATOR ACCOUNT!
