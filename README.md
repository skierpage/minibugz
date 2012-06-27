<blockquote>&ldquo;Design and code a mini bug tracking system using PHP and MySQL (and Javascript if you like) which can accept a bug title, description, status and keeps a history of status changes. It also allows an user to view the lists in different orders - by status, by title and by date.&rdquo;</blockquote>
This is just here for interested potential employers to review my work.  I'm publishing it under the <a href="http://en.wikipedia.org/wiki/GPL#Version_2">GPL v2</a> license, but you would be insane to use this untested code for anything.

Running it
==========
Just don't.  OK, <a href="/skierpage/minibugz/blob/master/sql/setup.txt">Linux command-line instructions</a> to set up the database and tables.

Instead, imagine running it, <a href="https://github.com/skierpage/minibugz/raw/master/docs/minibugs_listbugs_screenshot.png">seeing a list of bugs</a>, then <a href="https://github.com/skierpage/minibugz/raw/master/docs/minibugs_add_screenshot.png">adding one</a>.  It's like 1997 and "Learn PHP in a Week"

Interesting aspects
===================
This doesn't use any server framework at all, it's straight PHP 5.3.  That's clearly a bad idea and short-sighted waste of time.

* Uses PDO database access layer, prepared statements, and PDO:FETCH_OBJ (in <a href="/skierpage/minibugz/blob/master/includes/Buglist.php">includes/Buglist.php</a>).
* SQL LEFT JOIN in <a href="/skierpage/minibugz/blob/master/includes/Buglist.php">Buglist</a> to glue Bug and Status_code together.

In hacking this stuff you inevitably develop bits of a framework and start reinventing the wheel. I didn't look at the code of existing PHP frameworks, I invented these myself while painfully aware it's been done better. Here are some obvious reinventions in the code:

* Uses model-view "lite": when not rendering a Buglist, index.php creates a Bug object, possibly retrieves it from the database, and creates or updates it with user input.
* Action dispatch: the pageAction is partly driven by ?action={list/add/insert/modify/update} in the URL, but altered if actions like insert and update fail.
* Error object that collects a bunch of form validation errors and links to offending form fields.
* Really basic HTML5 form validation ("required").
* Simplistic test mode where invoking a component file  on the command line (`% php includes/db_login.php`) does something.


Design discussion
=================

Bug status
----------
The cheap approach would be to ignore all that Boyce-Codd database normalization buzzword
and simply store the textual bug status in each bug record.  This eliminates the task of mapping between status codes and status descriptions and greatly simplifies data management as there's just one table and no validation.  In the UI you would just
```sql
SELECT DISTINCT status_desc FROM bugs
```, show this in a select or form completion UI, and allow the user to enter a new status.

The reason not to be simple is *not* the possibly irrelevant concerns about SQL purity and performance, but because:

* bug status has a certain order that you want to reflect in the UI, e.g. NEW -> CONFIRMED -> ASSIGNED -> CLOSED -> VERIFIED.
* over time bug trackers invariably change their status handling by deprecating, renaming, and reordering statuses.

OK, so where does status live?
------------------------------
So at the database level a bug record has a numeric status_id that maps to a description in an ordered set in a separate <a href="/skierpage/minibugz/blob/master/sql/status_code.sql">status_code table</a>.  But at the UI level you only want to present the textual status.  When you retrieve a bug, should the bug object

* implicitly retrieve a status_desc
* make this an explicit getStatusDesc operation?
* use method operator overloading with __get and __set to hide bugs using a status_id altogether?
As always, with a proper MVC structure, the bug view and the bug model would be separate and you'd have a cleaner division assisting in the decision.

In all cases the bug object should cache the status mapping... except in a bug listng, you can do a JOIN to get the bug status description at the same time as the bug.  It means doing one thing two different ways (usually _bad_), but it is required to sort buglist ```ORDER BY status_code.ordering```.

Object creation
---------------
"Create new bug from form values" seems like an object call, thus ```$bug = new Bug ( $_POST )```
The big issue is whether this should validate.
If it does validate and validation fails and throws an error, you can't redisplay the form showing bug contents, because the bug isn't there &mdash; I don't think you can throw an error *and* return an object.

So either you make processing and error reporting more complicated, or you have separate steps to create a bug object, validate it, and persist it.  The latter is where I ended up, but now you have multiple methods that all iterate through object fields and consider each one, which violates DRY unless you drive it from a buzzword-compliant ORM meta description of bugs which destroys "simple readable PHP".

the Bug plus status object
--------------------------
When displaying the list of bugs, with a LEFT JOIN with status_code you get extra info.
If you use PDO:FETCH_CLASS or PDO:FETCH_INTO to return a bug instance,
Could just add extra info to a Bug object, could have a bug_list instance have these extra fields.

TODO
====
Endless...

* Need to actually track bug history.
* Need to manage status codes
* jQuery to retrieve and append bug history on click.
* tabbed/mobile UI: the listing of bugs and add/modify one bug are two separate views, they could be presented that way


DRY (Don't Repeat Yourself)
---------------------------
The data design _should_ drive

* SQL table creation
* HTML constraints like text field length and HTML5 numeric, etc. data types
* Data model validation like field length and is_int().

Obviously best if a single data specification drove all these.  But then the HTML turns into obfuscated e.g. ```$datamodel->context->present( 'title' );``` As a compromise, maybe have a lot of class constants: Bug::TITLE_LENGTH , etc.
