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
 *       =new : show empty bug form with add formAction
 *       =add : insert bug details in DB
 *       =update : update bug matching id in DB
 *
 * @param ?id : from query string
 * if ?id=NNN then retrieve bug details
 *    and if successful
 *    show bug in bug form with update formAction
 *
 * Result: set pageAction and possibly formAction
 * TODO Could reuse same form to do a search?
 */
// TODO: filter against valid actions.
$pageAction = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'list';
$formAction = '';
if ($pageAction === 'new') {
    $formAction = 'add';
    // no params, so create a dummy bug;
    $bug = new Bug();
}

/**
 * Process update or add bug actions.
 * if successful then show notice and list bugs
 * else show error notice and redisplay form.
 */
if ($pageAction === 'add' or $pageAction === 'update') {
    $notice = "Need to $pageAction ...";
    try {
        // First create Bug object (no DB interaction)
        $bug = new Bug($_POST);
        echo "bug created\n";
        var_dump($bug);

        if ($pageAction === 'add') {
            $bug->insert();
        } else if ($pageAction === 'update') {
            // Should I retrieve bug then UPDATE it, or a static function, or ???
            echo "about to update\n";
            var_dump($bug);
            $bug->update();
        }
        // If we get here then successful 
        $notice .= "... success!";
    } catch  (Exception $e) {
        $notice .= "ERROR, could not " . $pageAction . " (" . $e->getMessage() .")";
        // change pageAction back to new/modify, but XXX don't throw away user input.
        if ($pageAction === 'add') {
            $pageAction = 'new';
            $formAction = 'add';
        } elseif ($pageAction === 'update') {
            $pageAction = 'modify';
            $formAction = 'update';
        }
    }
}


$bug_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : null;
// Retrieve bug information
// XXX but not if we failed to update a bug.
if ($pageAction != 'insert' and $bug_id > 0) {
    try {
        $bug = new Bug( array('bug_id' => $bug_id) );        
        $retrievedBug = true;
    } catch  (Exception $e) {
        $notice .= "ERROR, could not retrieve bug $bug_id (" . $e->getMessage() .")";
        $retrievedBug = false;
    }
    if ($retrievedBug) {
        $pageAction='modify';
        $formAction='update';
    } else {
        $notice = "No bug $bug_id found";
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
  <? if ($pageAction != 'add' ) { ?>
  <a href="<?= $_SERVER['SCRIPT_NAME'] ?>?action=new">Add new bug</a>
  <? } ?>
  <form id="search" action="<?= $_SERVER['SCRIPT_NAME'] . '?action=' . $formAction ?>">
    Search
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
if ($pageAction === 'list') {
    require_once('includes/Buglist.php');
    Buglist::renderHTML( $_SERVER );
}
?>
<? if ($formAction) { ?>
<form method="POST" action="<?= $_SERVER['SCRIPT_NAME'] . '?action=' . $formAction ?>">
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
        >
      </td>
    </tr>
    <tr>
      <th valign="top">Description</th>
      <td>
        <textarea name="description"
               rows="5" cols="40" maxlength="1000"
               required
        ><?=$bug->description ?></textarea>
      </td>
    </tr>
    <tr>
      <th>Status</th>
      <td>
        <input type="text" name="status_id"
               size="2" maxlength="3"
               required
               value="<?=$bug->status_id ?>"
        >
        TODO!!
        if status_id not null and status_id not in table, show it.
        else show select of status values, SELECTED for match.
      </td>
    </tr>
    <? if ($bug->status_last_modified) { ?>
    <tr>
      <td>Status last modified</td>
      <td>
        <?= $bug->status_last_modified ?>
      </td>
    </tr>
    <? } ?>
    <tr>
      <td></td>
      <td>
        <input type="submit" name="submit" value="<?= $formButtonText[$formAction] ?>">
      </td>
    </tr>
  </table>

</form>
<? } ?>
</body>
</html>
