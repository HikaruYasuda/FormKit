<?php
require '../fk/Form.php';
require 'form/bookmark/search.php';
$form = new Form_Bookmark_Search();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<form action="" method="get">
    <table>
        <tr><th><?php echo $form->title->label()?></th></tr>
    </table>
    <p><?php echo $form->label('title')?> <?php echo $form->html('title')?></p>
    <p><?php echo $form->label('remarks')?> <?php echo $form->html('remarks')?></p>
    <p><?php echo $form->label('category')?> <?php echo $form->html('category[]')?></p>
    <p><?php echo $form->label('folder')?> <?php echo $form->html('folder')?></p>
    <p><?php echo $form->label('secret')?> <?php echo $form->html('secret')?></p>
    <p><?php echo $form->label('ad')?> <?php echo $form->html('ad[name]')?></p>
    <p><?php echo $form->label('submit')?> <?php echo $form->html('submit')?></p>
</form>
</body>
</html>
