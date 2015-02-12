<?php
/**
 * フォームバリデータクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.1
 * @version 1.0.0
 */
class FormKit_Validator {
	/** @var array  */
	protected $_last_error_message = array();

	/** @return void */
	public function set_parameters() {}
	/** @return self */
	public function validator() { return $this; }

	/**
	 * フィールドを追加します
	 * @param FormKit_Field|array $field
	 * @return FormKit_Field
	 */
	public function add($field)
	{
		return parent::add($field);
	}

	/**
	 * フィールドを取得します
	 * @param array|string|null $field_name
	 * @return Form_Field|array|null
	 */
	public function field($field_name)
	{
		return parent::field($field_name);
	}

	/**
	 * バリデーションを実行します
	 * @return bool
	 */
	public function run()
	{
		$this->_last_error_message = array();

		foreach ($this->_field_list as $field)
		{
			$this->check_valid($field);
		}

		return count($this->_last_error_message) == 0;
	}

	/**
	 * バリデーション関数と合わせてバリデーションを実行します
	 * @param callable $function
	 * @return bool
	 */
	public function run_with($function)
	{
		$this->run();

		if (is_array($function) || is_object($function))
		{
			if (count($function) == 2 && method_exists($function[0], $function[1]))
			{
				call_user_func_array($function, array(&$this));
			}
		}
		elseif (($pos = strpos($function, '::')) !== FALSE)
		{
			if (method_exists(substr($function, 0, $pos), substr($function, $pos+2)))
			{
				call_user_func_array($function, array(&$this));
			}
		}
		elseif (function_exists($function))
		{
			call_user_func_array($function, array(&$this));
		}

		return count($this->_last_error_message) == 0;
	}

	/**
	 * フィールドの値の有効性をチェックします
	 * @param Form_Field $field
	 * @return bool|string
	 */
	public function check_valid(Form_Field $field)
	{
		$field_name = $field->name();
		if ($field->rule() && count($field->rule()) > 0)
		{
			foreach ($field->rule() as $rule_name => $args)
			{
				$result = TRUE;
				$values = is_array($field->value()) ? $field->value() : array($field->value());
				if ($rule_name == 'required' && is_array($field->value()))
				{
					$result = FALSE;
					foreach ($values as $value)
					{
						if ( ! ($value === '' || is_null($value) || $value === FALSE))
						{
							$result = TRUE;
						}
					}
					$values = array();
				}
				foreach ($values as $value)
				{
					if ($result !== TRUE)
					{
						break;
					}

					$blank_or_null = ($value === '' || is_null($value) || $value === FALSE);

					if ($rule_name == 'required')
					{
						$result = ! $blank_or_null;
					}
					elseif ( ! $blank_or_null && $rule_name == 'in_options')
					{
						$result = $field->options() && str_in_array($value, array_keys($field->options()));
					}
					elseif (method_exists($this, $rule_name))
					{
						if ( ! $blank_or_null)
						{
							$func_args = array_merge(array($value), $args);
							$result = call_user_func_array(array($this, $rule_name), $func_args);
						}
					}
					else
					{
						$result = $this->call_method($rule_name, $field, $args);
						break;
					}
				}

				if (is_string($result))
				{
					$this->set_error_msg($field_name, $result);
					break;
				}
				elseif ($result == FALSE)
				{
					if ($rule_name == 'required' && in_array($field->type(), array('select', 'checkbox', 'radio')))
					{
						$rule_name = 'required_select';
					}
					$message = $this->make_error_message($rule_name, $field->label(), $args);
					$this->set_error_msg($field_name, $message);
					break;
				}
			}
		}
	}

	/**
	 * エラーメッセージを取得します
	 * @param string|null $field_name
	 * @return array|string
	 */
	public function last_error_message($field_name = null)
	{
		if (is_null($field_name))
		{
			return $this->_last_error_message;
		}
		elseif (array_key_exists($field_name, $this->_last_error_message))
		{
			return $this->_last_error_message[$field_name];
		}

		return '';
	}

	/**
	 * エラーメッセージを追加します
	 * @param $field_name
	 * @param $message
	 * @return Form_Validator
	 */
	public function set_error_msg($field_name, $message)
	{
		$this->_last_error_message[$field_name] = $message;
		return $this;
	}

	/**
	 * ロジック名-メソッド名:[引数[:...]]でロジックのチェックメソッドを呼び出す<br>
	 * 呼び出す時の引数は (Form_Validator &$validator, Form_Field $field, array $args)
	 * @param string $rule_name
	 * @param Form_Field $field
	 * @param array $args
	 * @return bool|string TRUE:成功,FALSE:失敗,文字列を返すとエラーメッセージとして利用されます
	 */
	protected function call_method($rule_name, Form_Field &$field, $args)
	{
		$func_args = array(&$this, $field) + $args;
		if (method_exists($this->CI, $rule_name))
		{
			return call_user_func_array(array($this->CI, $rule_name), $func_args);
		}
		if (count($array = explode('-', $rule_name, 2)) < 2)
		{
			error_log('Form_Validator::method() メソッドが指定されてない');
			return TRUE;
		}
		$logic_name = $array[0];
		$method_name = $array[1];
		if (empty($logic_name) || empty($method_name))
		{
			error_log('Form_Validator::method() メソッドが指定されてない');
			return TRUE;
		}
		$logic = $this->CI->get_logic($logic_name);
		if (is_null($logic))
		{
			error_log('Form_Validator::method() ロジックの取得失敗.class='.$logic_name.'_logic');
			return TRUE;
		}
		if ( ! method_exists($logic, $method_name))
		{
			error_log('Form_Validator::method() メソッドの呼び出し失敗.class='.$logic_name.'_dto, method='.$method_name);
			return TRUE;
		}
		return call_user_func_array(array($logic, $method_name), $func_args);
	}

	/**
	 * 日時系入力値の型を取得します
	 * @param string|int $value 入力値
	 * @return string 次のどれか(timestamp|datetime|datehm|date|unknown)
	 */
	private function getTimePattern($value)
	{
		if (is_numeric($value)) return 'timestamp';
		if ($this->datetime($value)) return 'datetime';
		if ($this->datehm($value)) return 'datehm';
		if ($this->date($value)) return 'date';
		return 'unknown';
	}

	/**
	 * 時間を比較します
	 * @param mixed $value
	 * @param string $pattern (timestamp|datetime|datehm|date)
	 * @param int $now 比較する時刻のタイムスタンプ.デフォルトは現在時刻
	 * @return int 現在時刻(または$now)より$valueが過去だったら-1、未来だったら+1、等しかったら0
	 */
	public function compareTime($value, $pattern, $now = NULL)
	{
		is_null($now) AND ($now = time());

		switch ($pattern)
		{
			case 'auto':
				return $this->compareTime($value, $this->getTimePattern($value), $now);
			case 'timestamp':
				$val = $value;
				break;
			case 'datetime':
				$val = strtotime($value);
				$now = strtotime(date('Y/m/d H:i:s', $now));
				break;
			case 'datehm':
				$val = strtotime($value.':0');
				$now = strtotime(date('Y/m/d H:i:0', $now));
				break;
			case 'date':
				$val = strtotime($value.' 0:0:0');
				$now = strtotime(date('Y/m/d 0:0:0', $now));
				break;
			default:
				return 0;
		}
		$ret = $val - $now;
		return ($ret <= 0) ? ($ret == 0) ? 0 : -1 : 1;
	}

	/*************************************************************
	 * エラーメッセージ
	 ************************************************************/
	/** @var array メッセージフォーマットマップ.$0がフィールドのラベル名.$1以降が引数の値 */
	public $messages = array(
		'' => '$0の値が不正です。',// default error message. (require)
		'required' => '$0を入力してください。',
		'required_select' => '$0を選択してください。',
		'minlength' => '$0は$1文字以上で入力してください。',
		'maxlength' => '$0は$1文字以下で入力してください。',
		'kana' => '$0は全角カナで入力してください。',
		'int' => '$0は数値で入力してください。',
		'natural' => '$0は数値で入力してください。',
		'decimal' => '$0は数値で入力してください。',
		'alpha' => '$0は半角英字のみで入力してください。',
		'alpha_num' => '$0は半角英数字のみで入力してください。',
		'alpha_dash' => '$0は半角英数字とハイフン(-)、アンダースコア(_)のみで入力してください。',
		'url' => '$0はURLを入力してください。',
		'email' => '$0はメールアドレスを入力してください。',
		'min' => '$0は$1以上の数値を入力してください。',
		'max' => '$0は$1以下の数値を入力してください。',
		'match' => '$0が異なります。',
		'unique' => 'この$0は既に使われています。',
		'past' => '$0は現在以前の日付を入力してください。',
		'future' => '$0は現在以降の日付を入力してください。',
	);

	/**
	 * @param string $rule_name
	 * @param string $label
	 * @param array $args
	 * @return mixed|string
	 */
	public function make_error_message($rule_name, $label, $args = array())
	{
		$message = $this->messages[''];
		if (array_key_exists($rule_name, $this->messages))
		{
			$message = $this->messages[$rule_name];
		}
		$message = str_replace('$0', $label, $message);
		for ($i = count($args); $i > 0; $i--)
		{
			$message = str_replace('$'.$i, $args[$i-1], $message);
		}
		return $message;
	}

	/*************************************************************
	 * チェックメソッド
	 ************************************************************/
	/** 数値上限下限チェック */
	public function between($value, $min, $max) { return ((int)$min <= (int)$value && (int)$value <= (int)$max); }

	/** 数値下限チェック */
	public function min($value, $min) { return (int)$value >= (int)$min; }

	/** 数値上限チェック */
	public function max($value, $max) { return (int)$value <= (int)$max; }

	/** 文字数上限下限チェック */
	public function length($value, $min, $max) { $mb_len = mb_strlen($value); return ((int)$min <= $mb_len && $mb_len <= (int)$max); }

	/** 文字数上限チェック */
	public function minlength($value, $min) { $mb_len = mb_strlen($value); return $mb_len >= (int)$min; }

	/** 文字数上限チェック */
	public function maxlength($value, $max) { $mb_len = mb_strlen($value); return $mb_len <= (int)$max; }

	/** 正規表現チェック */
	public function regex($value, $pattern) {
		$args = func_get_args();
		array_shift($args);
		return (bool)preg_match(implode(':', $args), $value);
	}

	/** 整数チェック */
	public function int($value) { return $this->regex($value, '/^[\-+]?[0-9]+$/'); }

	/** 自然数チェック */
	public function natural($value) { return $this->regex($value, '/^[0-9]+$/'); }

	/** 浮動小数点チェック */
	//  public function decimal($value) { return $this->regex($value, '/^[\-+]?[0-9]+\.[0-9]+$/'); }
	public function decimal($value) { return $this->regex($value, '/^[\-+]?([0-9]+(\.[0-9]*)?|\.[0-9]+)?$/'); }

	/** 半角英字チェック */
	public function alpha($value) { return $this->regex($value, '/^[a-z]+$/i'); }

	/** 半角英数字チェック */
	public function alpha_num($value) { return $this->regex($value, '/^[a-z0-9]+$/i'); }

	/** 半角英数字+アンダースコア+ハイフンチェック */
	public function alpha_dash($value) { return $this->regex($value, '/^([-a-z0-9_-])+$/i'); }

	/** 全角カナチェック */
	public function kana($value) { return $this->regex($value, '/^[ァ-ヶー　０-９]*$/u'); }

	//** URLチェック */
	public function url($value) { return $this->regex($value, '/([\w*%#!()~\'-]+\.)+[\w*%#!()~\'-]+(\/[\w*%#!()~\'-.]+)*/u'); }

	/** メールアドレスチェック */
	public function email($value) { return $this->regex($value, '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix'); }

	/** 日付形式チェック yyyy/mm/dd */
	public function date($value) { return $this->regex($value, '@^([0-9]{4}(-|/)(0?[1-9]|1[012])(-|/)(0?[1-9]|[12][0-9]|3[01]))\w*@'); }

	/** 日付時分形式チェック yyyy/mm/dd HH:ii */
	public function datehm($value) { return $this->regex($value, '@^([0-9]{4}(-|/)(0?[1-9]|1[012])(-|/)(0?[1-9]|[12][0-9]|3[01]))\w*(0?[1-9]|1[0-9]|2[0-3]):(0?[1-9]|[1-5][0-9])$@x'); }

	/** 日時形式チェック yyyy/mm/dd HH:ii:ss */
	public function datetime($value) { return $this->regex($value, '@^([0-9]{4}(-|/)(0?[1-9]|1[012])(-|/)(0?[1-9]|[12][0-9]|3[01]))\w*(0?[1-9]|1[0-9]|2[0-3]):(0?[1-9]|[1-5][0-9]):(0?[1-9]|[1-5][0-9])$@x'); }

	/** 過去日時チェック(現在を含む) */
	public function past($value, $pattern = 'auto', $now = NULL)
	{
		$pattern == 'auto' AND ($pattern = $this->getTimePattern($value));
		if ($pattern == 'unknown')
		{
			return TRUE;
		}
		return $this->compareTime($value, $pattern, $now) <= 0;
	}

	/** 未来日時チェック(現在を含む) */
	public function future($value, $pattern = 'auto', $now = NULL)
	{
		$pattern == 'auto' AND ($pattern = $this->getTimePattern($value));
		if ($pattern == 'unknown')
		{
			return TRUE;
		}
		return $this->compareTime($value, $pattern, $now) >= 0;
	}

	/** テーブル内でユニークな値か<br>$field ... テーブル名.フィールド名で指定 */
	public function unique($value, $field)
	{
		list($table, $field)=explode('.', $field);
		/** @var CI_DB_result $rs */
		$rs = $this->CI->db->limit(1)->get_where($table, array($field => $value));
		return $rs->num_rows() === 0;
	}

	/** 他のフィールドに一致するか */
	public function match($value, $form_field, /** @param string $delimiter 区切り文字 */$delimiter = ',')
	{
		if ( ! is_array($form_field))
		{
			$form_field = explode($delimiter, $form_field);
		}
		foreach ($form_field as $field_name)
		{
			if ( ($field = $this->field($field_name)) && $field->value() !== $value)
			{
				return FALSE;
			}
		}
		return TRUE;
	}

	/** 配列内の要素に一致するか */
	public function in_array($value, $array, /** @param string $delimiter 区切り文字 */$delimiter = ',')
	{
		if ( ! is_array($array))
		{
			$array = explode($delimiter, $array);
		}
		return str_in_array($value, $array);
	}
}

