<?php

include __DIR__.'/formkit.php';

FormKit_FieldSet::_test_add();
exit;

$form = new FormKit;
$form->setConfig(array(
	'lang' => 'ja',
));
$form->defRule(
	$form::Rule('password', 'regex', '/[\w!-/:-@\[-`{-~]+/')
		->message('{label}は半角英数と記号で入力してください。'));
$form = FormKit::Form('entry');
$form->add_fields(
	array(
		FormKit::Field('name',             'text',     '名前')->rule('required|maxLength:100'),
		FormKit::Field('email',            'text',     'Email')->rule('required|email|maxLength:100|_unique_email'),
		FormKit::Field('password',         'password', 'パスワード')->rule('required|password'),
		FormKit::Field('password_confirm', 'password', 'パスワード(確認)')->rule('required|match:password'),
		FormKit::Field('birthday',         'text',     '誕生日')->filter('date')->rule('required')->default(),
	)
)->add_rule(
	FormKit::Rule('_unique_email', function(FormKit_Field $field) {
		return (! in_array($field->value(), array('111111')));
	})
		->message('この{label}は既に使われています。'));
$form->input($_POST);
if ($_POST)
{
	if ($form->validate())
	{
		print_r($form->value());
	}
	else
	{
		$form->error_msg();
	}
}

?>
<html>
<form method="post">
	<script type="text/javascript">
		(function() {
			<? echo FormKit::LiveValidation(''); ?>
		}());
	</script>
	<?=$form->label('name')?>: <?=$form->html('name')?><?=$form->error_msg('name')?>
	<?=$form->label('email')?>: <?=$form->html('email')?>
	<?=$form->label('password')?>: <?=$form->html('password')?>
	<?=$form->label('password_confirm')?>: <?=$form->html('password_confirm')?>
	<?=$form->label('birthday')?>: <?=$form->html('birthday')?>

	<code>名前: <input type="text" name="name"/></code>
</form>
</html>
