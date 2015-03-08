<?php
namespace FormKit;

/**
 * フィールドセットクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.3
 * @version 1.0.0
 */
class FieldSet implements \Traversable, \Countable
{
    /** @var Field[] フィールド要素 */
    protected $fields = array();

    /**
     * @param Field[]|Field $field
     */
    public function __construct($field = null)
    {
        $field and $this->add($field);
    }

    /**
     * @return Field[]
     */
    public function toMap()
    {
        return $this->fields;
    }

    /**
     * @return Field[]
     */
    public function toArray()
    {
        return array_values($this->fields);
    }

    /**
     * @return string[]
     */
    public function fieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * フィールドを追加します
     *
     * @param Field|Field[] $field
     * @return static
     */
    public function add($field)
    {
        $fields = is_array($field) ? $field : array($field);
        foreach ($fields as $field) {
            if ($field instanceof Field) {
                $this->fields[$field->name()] = $field;
                $field->changeParent($this);
            } else {
                FormKit::$strict and trigger_error('parameter 1 must be a Field object');
            }
        }
        return $this;
    }

    /**
     * 指定されたフィールド名のフィールドがフィールドリスト内に存在するか判定します
     * @param string $fieldName 判定するフィールド名
     * @return bool 存在する場合TRUE、しない場合FALSEを返します。
     */
    public function exists($fieldName)
    {
        return is_string($fieldName) and isset($this->fields[$fieldName]);
    }

    /**
     * フィールド数を取得します
     * @return int フィールド数
     */
    public function count()
    {
        return count($this->fields);
    }

    /**
     * フィールドオブジェクトを取得します
     *
     * @param string|string[] $fieldName 取得するフィールドオブジェクトのフィールド名、またはフィールド名のリスト。
     * @return Field|Field[] 引数がフィールド名の場合は指定したフィールドオブジェクトを返します。存在しない場合はNULLを返します。
     * <br>引数がフィールド名リストの場合はフィールド名をキーにした存在するフィールドオブジェクトの連想配列、
     * 引数がNULLの場合は全フィールドの連想配列を返します。
     */
    public function get($fieldName)
    {
        if (is_string($fieldName)) {
            return $this->exists($fieldName) ? $this->fields[$fieldName] : null;
        } elseif (is_array($fieldName)) {
            $map = array();
            foreach ($fieldName as $aFieldName) {
                $field = $this->get($aFieldName);
                $field and ($map[$aFieldName] = $field);
            }
            return $map;
        }
        FormKit::$strict and trigger_error('parameter 1 must be a string or an array');
        return null;
    }

    /**
     * フィールドリストからフィールドを削除します
     * @param string[]|string $fieldName 削除するフィールドのキー名、またはキー名の配列。指定しなかった場合はすべてのフィールドを削除します。
     * @return static
     */
    public function remove($fieldName = null)
    {
        if (is_null($fieldName)) {
            $fieldNames = $this->fieldNames();
        } elseif (is_string($fieldName)) {
            $fieldNames = array($fieldName);
        } else {
            $fieldNames = $fieldName;
        }
        foreach ($fieldNames as $fieldName) {
            if ($this->exists($fieldName)) {
                unset($this->fields[$fieldName]);
            }
        }
        return $this;
    }

    //---------------------------
    // implements for Iterator
    //---------------------------

    public function current()
    {
        return current($this->fields);
    }

    public function next()
    {
        next($this->fields);
    }

    public function key()
    {
        return key($this->fields);
    }

    public function valid()
    {
        $key = $this->key();
        return ($key !== NULL && $key !== FALSE);
    }

    public function rewind()
    {
        reset($this->fields);
    }
}