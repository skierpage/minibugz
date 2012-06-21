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
$formButtonText = array (
    'add' => 'Add new bug',
    'modify' => 'Update'
);

$bug = null;


/**
 * Action routing:
 * @param ?action
 * default is add a bug
 * ?action=add insert bug details.
 * if ?id=NNN then retrieve bug details and if successful show form for editing.
 * 
 * TODO Reuse same form to do a search?
 */
$pageAction = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'add';
$formAction = '';
if ($pageAction == 'add') {
    $formAction = 'add';
    // no params, so create a dummy bug;
    $bug = new Bug();
}


$bug_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : null;
// Retrieve bug information
if ($pageAction != 'modify' and $bug_id > 0) {
    try {
        $bug = new Bug( array('bug_id' => $bug_id) );        
        $retrievedBug = true;
    } catch  (Exception $e) {
        $notice .= "ERROR, could not retrieve bug $bug_id (" . $e->getMessage() .")";
        $retrievedBug = false;
    }
    if ($retrievedBug) {
        $pageAction='modify';
        $formAction='modify';
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
  <form action="<?= $_SERVER['SCRIPT_NAME'] ?>"?action=search>
    <? if ($pageAction != 'add' and $pageAction != 'modify') { ?>
    <a href="<?= $_SERVER['SCRIPT_NAME'] ?>?action=add">Add a bug</a>
    <? } ?>
    Search
    <input type="hidden" name="action" value="search">
    <input type="text" name="id"
           size="40" maxlength="255"
           placeholder="enter bug ID or term"
    >
  </form>
</nav>
<br style="clear: both;">
<div class="notice"><?= $notice ?></div>
<? if ($formAction) { ?>
<form method="POST">
  <input type="hidden" name="action" value="<?=$formAction ?>">
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
        <?= $bug->status_id ?>
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
