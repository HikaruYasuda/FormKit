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
    public function names()
    {
        return array_keys($this->fields);
    }

    /**
     * フィールドをセットします
     *
     * <pre>
     * Usage:
     * add($fieldset);
     * add($field);
     * add(array(
     *   $field,
     *   'name1',
     *   'name2' => 'name2type',
     *   'name3' => array('name3type'),
     *   'name4' => array('name4type', 'name4label'),
     *   'name5' => array('type' => 'name5type', more attributes...),
     * ));
     * add('name5');
     * </pre>
     * @param Field|Field[]|string|string[]|FieldSet $fields
     * @return static
     * @throws \InvalidArgumentException
     */
    public function add($fields)
    {
        is_array($fields) or ($fields = array($fields));
        foreach ($fields as $key => $val) {
            // case Field instance array
            if (is_object($val)) {
                // case FieldSet instance
                if ($val instanceof FieldSet) {
                    $this->addFieldset($val);
                } // case Field instance
                elseif ($val instanceof Field) {
                    $this->addFieldObject($val);
                }
            } // case [field name => field structure] array
            elseif (is_string($key)) {
                $this->addFieldStructure($key, $val);
            } // case filed name
            elseif (is_int($key) and is_string($val)) {
                $this->addFieldStructure($val);
            } else {
                throw new \InvalidArgumentException;
            }
        }
        return $this;
    }

    /**
     * @param FieldSet $fieldset
     * @return static
     */
    public function addFieldset(FieldSet $fieldset)
    {
        foreach ($fieldset as $field) {
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
     * 連想配列でフィールドを追加します
     *
     * <pre>
     * Usage:
     * addFieldStructure('name1')
     * addFieldStructure('name2', 'name2type')
     * addFieldStructure('name3', array('name3type'))
     * addFieldStructure('name4', array('name4type', 'name4label'))
     * addFieldStructure('name5', array('type' => 'name5type', more attributes...))
     * </pre>
     * @param string $fieldName
     * @param array|string $structure
     * @return static
     */
    public function addFieldStructure($fieldName, $structure = array())
    {
        is_array($structure) or $structure = array($structure);
        $type = isset($structure[0]) ? $structure[0] : '';
        $label = isset($structure[1]) ? $structure[1] : '';

        $field = new Field($fieldName, $type, $label);
        foreach ($structure as $key => $val) {
            $field->_setAttribute($key, $val);
        }
        return $this->addFieldObject($field);
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