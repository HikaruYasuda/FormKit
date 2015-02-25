<?php
require '../../lib/formkit.php';
FormKit\FormKit::init();
require '../entity/bookmark.php';

class Form_Edit extends \FormKit\Form
{
    /** @var Entity_Bookmark */
    public $bookmark;

    public function __construct(Entity_Bookmark $bookmark)
    {
        $this->bookmark = $bookmark;

        $this->add(array(
            FK::Field('id',       'hidden',   'ID'),
            FK::Field('title',    'text',     'タイトル'),
            FK::Field('remarks',  'textarea', 'メモ'),
            FK::Field('category', 'checkbox', 'カテゴリ')->options($bookmark->categories())->rule('in_options'),
            FK::Field('secret',   'bool',     '秘匿'),
            FK::Field('ad[name]',  '')
        ));
    }
}

$bookmark = new Entity_Bookmark();

$form = new Form_Edit($bookmark);
$form->input();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<form action="" method="get">
    <p><?php echo $form->toHTML('title')?></p>
    <p><?php echo $form->toHTML('remarks')?></p>
    <p><?php echo $form->toHTML('category')?></p>
    <p><?php echo $form->toHTML('secret')?></p>
    <p><?php echo $form->toHTML('ad[name]')?></p>
    <button>送信</button>
</form>
</body>
</html>