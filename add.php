<?php
require_once('includes/util.php');
require_once('includes/db_login.php');
## Provide selection require_once('../includes/utils.php');

/**
 * ?action=add insert bug details.
 * ?? Reuse same form to do a search?
 * ?sort=
 */

// extract values from request
// TODO wantvalues() that pulls these out of $_REQUEST
if (isset($_REQUEST)) {
  if (isset($_REQUEST['title'])) {
    $title = $_REQUEST['title'];
  }
  if (isset($_REQUEST['description'])) {
    $description = $_REQUEST['description'];
  }
  if (isset($_REQUEST['status_id'])) {
    $title = $_REQUEST['status_id'];
  }
}


?>
<form method="POST">
  <table>
    <tr>
      <th>Title</th>
      <td>
        <input type="text" name="title"
               size="40" maxlength="255"
               required
               value="<?=ifexists('title') ?>"
        >
      </td>
    </tr>
    <tr>
      <th>Description</th>
      <td>
        <input type="textarea" name="description"
               rows="5" cols="40" maxlength="1000"
               required
               value="<?=ifexists('title') ?>"
        >
      </td>
    </tr>
    <tr>
      <th>Status</th>
      <td>
        TODO!!
               value="<?=ifexists('status_id') ?>
      </td>
    </tr>
  </table>

</form>
