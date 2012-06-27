Database
========
people want to control order of status.
people change bug status codes, but instead of deleting old status, just add new ones.

Should a bug's modified change when title or description is changed?

Does PHP/mysql have a stored procedure to automatically update status_last_modified if status changes?

Don't waste effort building a UI for modifying status codes, use phpMyAdmin or a PHP framework.

Interesting aspects
===================
* Uses PDO database access layer, and in includes/Buglist.php prepared statements and PDO:FETCH_OBJ.

In hacking this stuff you inevitably develop a framework and start reinventing the wheel:
* Uses model-view "lite": index.php either retrieves a bug object or fills it with user input
* Action dispatch: the pageAction is partly driven by ?action={list/add/insert/modify/update} in the query string, but altered if actions like insert and update fail.
* Simplistic test mode where invoking an include on the command line (`% php includes/db_login.php`) does something


Design discussion
=================

Bug status
----------
The cheap approach would be to ignore all that Boyce-Codd database normalization buzzword
and simply store the textual bug status in each bug record.
This eliminates mapping status codes to status descriptions and greatly simplifies data management
as there's just one table and no validation.  In the UI you would just
  SELECT DISTINCT status_desc FROM bugs
, show this in a select or form completion UI, and allow the user to enter a new status.

The reason not to be simple is *not* the possibly irrelevant concerns about SQL purity and performance,
but because
* bug status has a certain order that you want to reflect in the UI, e.g. NEW, CONFIRMED, ASSIGNED, CLOSED, VERIFIED.
* over time bug trackers invariably change their status handling by deprecating, renaming, and reordering statuses.

OK, so where does status live?
------------------------------
So at the database level a bug record has a numeric status_id that maps to a description in an ordered set.
But at the UI level you only want to present the textual status.
When you retrieve a bug, should the bug object
* implicitly retrieve a status_desc
* make this an explicit getStatusDesc operation?
* use method operator overloading with __get and __set to hide bugs using a status_id altogether?
As always, with a proper MVC structure, the bug view and the bug model would be separate and you'd have a cleaner division assisting in the decision.

In all cases the bug object should cache the status mapping.
E.g. in the bug listing, the display of the first bug should look up all statuses, then nothing after that.

Note that in a bug listing, you can do a JOIN to get the bug status description at the same time as the bug.
This is required to sort bugs ORDER BY status_code.ordering
So now the information about status comes in two ways.

Object creation
---------------
"Create new bug from form values" seems like an object call -> new Bug ( $_POST )
The big issue is whether this should validate.
If it does validate and validation fails and throws an error, you can't redisplay the form showing bug contents, because the bug isn't there -- I don't think you can throw an error *and* return an object.

So either you make processing and error reporting more complicated,
or you have separate steps to create a bug object, validate it, and persist it.
But now you have multiple methods that all iterate through object fields and consider each one,
so you start violating DRY and want to drive this from a buzzword-compliant ORM meta description of bugs.
* do it right and separate the bug in the view from the bug in the data model

the Bug plus status object
--------------------------
When displaying the list of bugs, with a LEFT JOIN with status_code you get extra info.
If you use PDO:FETCH_CLASS or PDO:FETCH_INTO to return a bug instance,
Could just add extra info to a Bug object, could have a bug_list instance have these extra fields.

DRY (Don't Repeat Yourself)
---------------------------
The data design drives
* SQL table creation
* HTML constraints like text field length and HTML5 numeric, etc. data types
* Data model validation like field length and is_int().

Obviously best if a single data specification drove all these.
But the HTML turns into obfuscated $datamodel->renderHTML( 'title');
Maybe have a lot of class constants: Bug::TITLE_LENGTH , etc.
