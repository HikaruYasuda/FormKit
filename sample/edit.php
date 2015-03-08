<?php
require '../lib/formkit.php';
FormKit\FormKit::init(array(

));
require 'entity/bookmark.php';

class Form_Edit extends \FormKit\Form
{
    /** @var Entity_Bookmark */
    public $bookmark;

    public function __construct(Entity_Bookmark $bookmark)
    {
        $this->bookmark = $bookmark;
        $folders = array(
            '' => '-- 選択 --',
            1 => '仕事',
            2 => '趣味',
        );
        $categories = array(
            1 => 'PHP',
            2 => 'Java',
            3 => 'Ruby',
            4 => 'C++'
        );

        $this->add(array(
            FK::Field('id',         'hidden',   'ID'),
            FK::Field('title',      'text',     'タイトル'),
            FK::Field('remarks',    'textarea', 'メモ'),
            FK::Field('category',   'checkbox', 'カテゴリ')->options($categories)->rule('in_options'),
            FK::Field('folder',     'select',   'フォルダ')->options($folders)->rule('in_options'),
            FK::Field('secret',     'bool',     '秘匿'),
            FK::Field('ad',         '',         ''),
            FK::Field('submit',     'submit',   '送信')
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