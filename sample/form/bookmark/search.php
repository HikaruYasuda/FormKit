<?php

/**
 * Class Form_Bookmark_Search
 *
 * @property FK_Field title タイトル
 * @property FK_Field remarks メモ
 * @property FK_Field category カテゴリ
 * @property FK_Field folder フォルダ
 * @property FK_Field secret 秘匿
 * @property FK_Field submit 送信
 */
class Form_Bookmark_Search extends FK_SearchForm
{
    public function initialize()
    {
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

        $this->title->type('text')->label('タイトル')->options($categories)->rule('in_options');
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