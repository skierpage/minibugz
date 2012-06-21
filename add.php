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
$notice = 'MY NOTICE HERE';
$formButtonText = array (
    'add' => 'Add new bug',
    'modify' => 'Update'
);

$bug = null;


/**
 * Action routing:
 * @param ?action
 * default is showaddform
 * ?action=add insert bug details.
 * if ?id=NNN then retrieve bug details and if successful show form for editing.
 * 
 * TODO Reuse same form to do a search?
 */
$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'showaddform';
if ($action == 'showaddform') {
    $formAction = 'add';
    // no params, so create a dummy bug;
    $bug = new Bug();
}


$bug_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : null;
// Retrieve bug information
if ($formAction != 'modify' and $bug_id > 0) {
    try {
        $bug = new Bug( array('bug_id' => $bug_id) );        
        $retrievedBug = true;
    } catch  (Exception $e) {
        $notice .= "ERROR, could not retrieve bug $bug_id (" . $e->getMessage() .")";
        $retrievedBug = false;
    }
    if ($retrievedBug) {
        $formAction='modify';
    } else {
        $notice = "No bug $bug_id found";
        $bug_id = null;
    }
}

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
<div class="notice"><?= $notice ?></div>
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
        TODO!!
        if status_id not null and status_id not in table, show it.
               value="<?= $bug->status_id ?>
        else show select of status values, SELECTED for match.
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <input type="submit" name="submit" value="<?= $formButtonText[$formAction] ?>">
      </td>
    </tr>
  </table>

</form>
