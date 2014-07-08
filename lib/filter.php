<?php
/**
 * フォームフィルタクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.1
 * @version 1.0.0
 */
class FormKit_Filter {
	/**
	 * クエリリストを使ってフィルタリングします
	 * @param mixed $value
	 * @param array $filters
	 * @param Form_Field $field
	 * @return mixed
	 */
	public function filtering($value, $filters, $field)
	{
		foreach ($filters as $filter)
		{
			$value = $this->call($filter['name'], $value, $filter['args'], $field);
		}
		return $value;
	}

	/**
	 *
	 * @param string $func_name
	 * @param mixed $value
	 * @param array $args
	 * @param Form_Field $field
	 * @return int|string
	 */
	public function call($func_name, $value, $args, $field)
	{
		array_unshift($args, $value);
		if ($func_name == 'in_options')
		{
			return ($field->options() && str_in_array($value, array_keys($field->options())))  ? $value : '';
		}
		elseif (method_exists($this, $func_name))
		{
			return call_user_func_array(array($this, $func_name), $args);
		}
		return $value;
	}

	/*************************************************************
	 * フィルタメソッド
	 ************************************************************/
	/** トリムします */
	public function trim($value, $charlist = null) { return trim($value, $charlist); }
	/** トリムします */
	public function rtrim($value, $charlist = null) { return rtrim($value, $charlist); }
	/** トリムします */
	public function ltrim($value, $charlist = null) { return ltrim($value, $charlist); }
	/** 文字列置換します */
	public function replace($value, $search, $replace, &$count = null) { return str_replace($search, $replace, $value, $count); }
	/** 空文字をNULLへ変換します */
	public function empty_is_null($value) { return ($value === '') ? null : $value; }
	/** 最初に現れた正規表現に合う文字列のみを抜き出します */
	public function regex($value, $pattern) { return preg_match($pattern, $value, $matches) ? $matches[1] : ''; }
	/** 最初に現れたカナ文字列のみを抜き出します */
	public function kana($value) { return $this->regex($value, '/([ァ-ヶー　０-９]*)/u'); }
	/** Integer形式にします */
	public function int($value) { return ($value !== '' && $value !== NULL) ? (int)$value : $value; }
	/** 大文字英字にします */
	public function upper($value) { return mb_strtoupper($value); }
	/** 小文字英字にします */
	public function lower($value) { return mb_strtolower($value); }
	/** 配列内の要素に一致しない場合は空文字にします */
	public function in_array($value, $array, /** @param string $delimiter 区切り文字 */$delimiter = ',')
	{
		if ( ! is_array($array))
		{
			$array = explode($delimiter, $array);
		}
		return str_in_array($value, $array) ? $value : '';
	}
	/** 日付形式にします */
	public function date($value)
	{
		if ( ! empty($value))
		{
			if (ctype_digit($value))
			{
				return date('Y/m/d', $value);
			}
			elseif (preg_match('@^([0-9]{4}(-|/)(0?[1-9]|1[012])(-|/)(0?[1-9]|[12][0-9]|3[01]))\w*@x', $value, $matches))
			{
				return date('Y/m/d', strtotime($value));
			}
		}
		return '';
	}
	/** 日付時分形式にします */
	public function datehm($value)
	{
		if ( ! empty($value))
		{
			if (ctype_digit($value))
			{
				return date('Y/m/d H:i', $value);
			}
			elseif (preg_match('@^([0-9]{4}(-|/)(0?[1-9]|1[012])(-|/)(0?[1-9]|[12][0-9]|3[01]))\w*(0?[1-9]|1[0-9]|2[0-3]):(0?[1-9]|[1-5][0-9])@x', $value, $matches))
			{
				return date('Y/m/d H:i', strtotime($value));
			}
		}
		return '';
	}
	/** 日時形式にします */
	public function datetime($value)
	{
		if ( ! empty($value))
		{
			if (ctype_digit($value))
			{
				return date('Y/m/d H:i:s', $value);
			}
			elseif (preg_match('@^([0-9]{4}(-|/)(0?[1-9]|1[012])(-|/)(0?[1-9]|[12][0-9]|3[01]))\w*(0?[1-9]|1[0-9]|2[0-3]):(0?[1-9]|[1-5][0-9]):(0?[1-9]|[1-5][0-9])@x', $value, $matches))
			{
				return date('Y/m/d H:i:s', strtotime($value));
			}
		}
		return '';
	}
}
