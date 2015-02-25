<?php
namespace FormKit;

/**
 * フィールドセットクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.3
 * @version 1.0.0
 */
class FieldSet implements \Iterator
{
    /** @var Field[] フィールド要素 */
    protected $fields = array();

    /**
     * @param self|Field[]|Field|string[]|string $field
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
     * フィールドをセットします
     *
     * <pre>
     * Usage:
     * add($field_instance);
     * add(array(
     *   $field_instance,
     *   'name1',
     *   'name2' => 'name2type',
     * ));
     * add('name3');
     * </pre>
     * @param Field|Field[]|string|string[] $fields
     * @return static
     * @throws \InvalidArgumentException
     */
    public function add($fields)
    {
        is_array($fields) or ($fields = array($fields));
        foreach ($fields as $key => $val) {
            if (is_object($val) and $val instanceof Field) {
                // case Field instance array
                $field = $val;
            } elseif (is_string($key)) {
                // case [field name => field structure] array
                $field = new Field($key, $val);
            } elseif (is_int($key) and is_string($val)) {
                // case filed name
                $field = new Field($val, 'text');
            } else {
                throw new \InvalidArgumentException;
            }
            $this->addFieldObject($field);
        }
        return $this;
    }

    /**
     * フィールドインスタンスを追加します
     * @param Field $field
     * @return static
     */
    public function addFieldObject(Field $field)
    {
        $this->fields[$field->name()] = $field;
        return $this;
    }

    /**
     * 指定されたフィールド名のフィールドがフィールドリスト内に存在するか判定します
     * @param string $fieldName 判定するフィールド名
     * @return bool 存在する場合TRUE、しない場合FALSEを返します。
     */
    public function exists($fieldName)
    {
        return is_string($fieldName) && array_key_exists($fieldName, $this->fields);
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
     * フィールドリストからフィールドを取得します
     * @param string[]|string|int $fieldName 取得するフィールドのフィールド名、またはフィールド名のリスト。
     * @return static|Field|Field[] 引数がフィールド名の場合は指定したフィールドを返します。
     * 引数がフィールド名リストの場合はフィールド名をキーにしたフィールドの配列、
     * 引数がない、またはNULLの場合は全フィールドの配列を返します。
     * 指定したフィールド名のフィールドが存在しない場合はNULLを返します。
     * @throws
     */
    public function get($fieldName = null)
    {
        if (is_string($fieldName)) {
            return $this->exists($fieldName) ? $this->fields[$fieldName] : null;
        } elseif (is_int($fieldName)) {
            return $this->getByIndex($fieldName);
        } elseif (is_array($fieldName)) {
            $map = array();
            foreach ($fieldName as $aFieldName) {
                $field = $this->get($aFieldName);
                $field and ($map[$aFieldName] = $field);
            }
            return $map;
        } elseif (is_null($fieldName)) {
            return $this->toMap();
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @param int $index
     * @return Field
     */
    public function getByIndex($index)
    {
        is_int($index) or $index = intval($index);
        if ($this->count() > $index) {
            foreach ($this->fields as $field) {
                if (0 == $index--) {
                    return $field;
                }
            }
        }
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
            return $this->removeAll();
        } elseif (is_array($fieldName)) {
            foreach ($fieldName as $aFieldName) {
                $this->remove($aFieldName);
            }
        } elseif (is_string($fieldName) and $this->exists($fieldName)) {
            unset($this->fields[$fieldName]);
        }
        return $this;
    }

    /**
     * @return static
     */
    public function removeAll()
    {
        $this->fields = array();
        return $this;
    }

    public static function _test_add()
    {
        var_dump(function () {
            echo '1';
        });
        $fieldset = new static;
        echo '<p>init fieldset</p>';
        print_r($fieldset);

        echo '<p>add fields</p>';
        $fieldset->add(FormKit::Field('name1'));
        $fieldset->add('name2');
        $fieldset->add(array(
            FormKit::Field('name3'),
            'name4',
            'name5' => 'name5type',
            'name6' => array('6type'),
            'name7' => array('7type', '7label'),
            'name8' => array(
                'type' => '8type',
                'label' => '8label',
                'default' => '8default',
                'id' => '8id',
                'value' => '8value',
                'options' => array('8option1' => '8option1label', '8option2' => '8option2label'),
                'rule' => 'required',
                'filter' => 'in_options',
            ),
        ));
        print_r($fieldset);

        echo '<p>add other fieldset</p>';
        $newFieldset = new static;
        $newFieldset->add('nameOther');
        $fieldset->add($newFieldset);
        print_r($fieldset);
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