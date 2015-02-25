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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<form>
    <?=$form->toHTML()?>
</form>
</body>
</html>