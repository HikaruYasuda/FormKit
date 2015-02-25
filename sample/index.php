<?php require_once '../lib/formkit.php'?>
<?php
FormKit\FormKit::init();
$opFunctions = array(
    '=' => function($v, $e) { return $v == $e; },
    '!=' => function($v, $e) { return $v != $e; },
    '>' => function($v, $e) { return $v > $e; },
    '<' => function($v, $e) { return $v < $e; },
    '>=' => function($v, $e) { return $v >= $e; },
    '<=' => function($v, $e) { return $v <= $e; },
);
FK::defRule(array(
    'required' => function($val) {
        return !fk_blankOrNull($val);
    },
    'requiredIf' => function($val, $targetName, $op = '', $val2 = '') use ($opFunctions) {
        $target = FK::inCheckField()->form()->field($targetName);
        if (!$target) return true;
        if (isset($opFunctions[$op])) {
            if ($opFunctions[$op]($target->value(), $val2)) {
                return !fk_blankOrNull($val);
            }
        } elseif (fk_blankOrNull($target->value())) {
            return !fk_blankOrNull($val);
        }
        return true;
    },
));
FK::Rule('required')->checkBlankFlag(true);
FK::Rule('requiredIf')->checkBlankFlag(true);
FK::$lang = 'ja';
FK::defRuleMessage(array(
    'required' => '$0を入力してください。'
));

$form = FK::Form();
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<form>

</form>
</body>
</html>