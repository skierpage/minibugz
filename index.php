<?php
require_once('includes/util.php');
require_once('includes/db_login.php');
require_once('includes/Bug.php');

if (isDebugMode()) {
    echo '<div style="background: #fdd;">';
    echo "_SERVER is ";
    print_r($_SERVER);
    echo "_REQUEST is ";
    var_dump($_REQUEST);
}

/****** initialization ******/
$notice = '';
$error = new Error;

/**
 * map formAction to text button
 */
$formButtonText = array (
    'add' => 'Add new bug',
    'update' => 'Update'
);

// XXX should I initialize to new Bug?  default or lazy-init?
$bug = null;


/**
 * Action routing:
 * @param ?action : from query string
 * if missing, default is to list recent bugs unless param ?id set.
 * action=query list recent bugs
 *       =new : show bug form with add formAction
 *       =add : insert bug details in DB, on fail show user input
 *       =update : update bug matching id in DB, on fail show user input
 *
 * @param ?id : from query string
 * if ?id=NNN then retrieve bug details
 *    and if successful
 *    show bug in bug form with update formAction
 *
 * Result: set pageAction and possibly formAction
 * TODO Could reuse same form to do a search?
 */

// Get the same-page URL: remember if we're in debug mode.
// and ensure we can always append &key=val to query string.
$samePageURL = $_SERVER['SCRIPT_NAME'] . ( isDebugMode() ? '?debug=1' : '?' );

// TODO filter against valid actions.
$pageAction = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'list';
$formAction = '';

/**
 * Process update or add bug actions.
 * if successful then show notice and list bugs
 * else show error notice and redisplay form.
 */
if ($pageAction === 'add' or $pageAction === 'update') {
    $notice = "Need to $pageAction ...";
    try {
        // First create Bug object (no DB interaction)
        $bug = new Bug( $_POST );
        if (! $bug->validate( $error ) ) {
            $dbUpdated = false;
        } else {
            if ($pageAction === 'add') {
                $bug->insert();
            } else if ($pageAction === 'update') {
                // Should I retrieve bug then UPDATE it, or a static function, or ???
                $bug->update();
            }
            // If we get here then successful 
            $notice .= "... success!";
            $dbUpdated = true;
        }
    } catch (Exception $e) {
        $error->err( "ERROR, could not " . $pageAction . " (" . $e->getMessage() .")" );
        $dbUpdated = false;
    }
    if ( $dbUpdated ) {
        $pageAction = 'list';
    } else {
        // change pageAction back to new/modify, but XXX don't throw away user input.
        // XXX or maybe better to leave pageAction, but have a failed validation state.
        if ($pageAction === 'add') {
            $pageAction = 'new retry';
            $formAction = 'add';
        } elseif ($pageAction === 'update') {
            $pageAction = 'update retry';
            $formAction = 'update';
        }
    }
}

if ($pageAction === 'new') {
    $formAction = 'add';
    // no params, so create a dummy bug;
    $bug = new Bug();
}


$bug_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : null;
// Retrieve bug information
// XXX but not if we failed to update a bug.
if ($pageAction != 'insert' and $bug_id > 0) {
    try {
      $bug = new Bug();        
      $bug->retrieve( (int) $bug_id );
      // If bug retrieved from DB is bad, inform but don't fail,
      // user will have to fix on update.
      $bug->validate( $error );
      $retrievedBug = true;
    } catch  (Exception $e) {
        $error->err( "ERROR, could not retrieve bug $bug_id (" . $e->getMessage() .")" );
        $retrievedBug = false;
    }
    if ($retrievedBug) {
        $pageAction='modify';
        $formAction='update';
    } else {
        $bug_id = null;
    }
}

$pageTitle = "Minibugz - " . $pageAction;

// extract bug values from request
// TODO initialize a Bug object from these.
// TODO add a wantvalues() that pulls these out of $_REQUEST?
$title = isset( $_REQUEST['title'] ) ? $_REQUEST['title'] : null;
$description = isset( $_REQUEST['description'] ) ? $_REQUEST['description'] : null;
$status_id = isset( $_REQUEST['status_id'] ) ? $_REQUEST['status_id'] : null;

if (isDebugMode()) {
    echo '<br><br></div>';
}
?>
<html>
<head>
<title><?= $pageTitle ?></title>
<link rel="stylesheet" media="screen" href="css/style.css?v=2">
</head>
<body>
<nav>
  <? if ($formAction != 'add' ) { ?>
  <form id="addbutton" method="get" action="<?= $samePageURL ?>&action=new">
    <input type="hidden" name="action" value="new">
    <input type="submit" value="Add new bug">
  </form>
  <? } ?>
  <form id="search" action="<?= $samePageURL ?>">
    Search:
    <input type="hidden" name="action" value="search">
    <input type="text" name="id"
           size="20" maxlength="255"
           placeholder="enter bug ID or term"
    >
  </form>
</nav>
<br style="clear: both;">
<div class="notice"><?= $notice ?></div>
<?
$outStr = $error->renderHTML();
if ($outStr !== '') {
?>
<div class="error"><?= $outStr ?></div>
<? } ?>

<?
if ($pageAction === 'list') {
    require_once('includes/Buglist.php');
    $buglist = new Buglist( $_REQUEST );
    $err = $buglist->renderHTML();
    // Collect the error, if any, from rendering.
    // Note the error object has already dumped out,
    // and for performance we want to stream out a big bug listing
    // rather than capturing it.
    if ($err != '') {
        print '<div class="error">'; print htmlspecialchars($err); print '</div>';
    }
}
?>
<? if ($formAction) { ?>
<form method="POST" action="<?= $samePageURL . '&action=' . $formAction ?>">
  <input type="hidden" name="bug_id" value="<?= $bug->bug_id ?>">
  <table>
    <tr>
      <th><?= $bug->bug_id ? "showing $bug->bug_id" : "New bug" ?></th>
    </tr>
    <tr>
      <th>Title</th>
      <td>
        <input type="text" name="title"
               size="40" maxlength="255"
               required
               value="<?=$bug->title ?>"
               id="title"
        >
      </td>
    </tr>
    <tr>
      <th valign="top">Description</th>
      <td>
        <textarea name="description"
               rows="5" cols="40" maxlength="1000"
               required
               id="description"
        ><?=$bug->description ?></textarea>
      </td>
    </tr>
    <tr>
      <th>Status</th>
      <td>
        <?= makeSelect($bug->status_id) ?>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <input type="submit" name="submit" value="<?= $formButtonText[$formAction] ?>">
      </td>
    </tr>
  <? if ( $formAction === 'update' ) {
     require_once('includes/Bughistory.php'); ?>
    <tr>
      <td>Status last modified</td>
      <td id="status_last_modified">
        <?= $bug->status_last_modified ?>
        <? Bughistory::renderHTML ( $bug->bug_id, true ) ?>
      </td>
    </tr>
  <? } ?>
  </table>

</form>
<? } ?>
</body>
</html>
