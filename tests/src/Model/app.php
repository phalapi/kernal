<?php

namespace App\Model;

use PhalApi\Model\NotORMTest;

class Tmp extends NotORMTest {

    public function getTableName($id = NULL) {
        return 'tmp2';
    }
}

class Test extends NotORMTest {

    public function getTableName($id) {
        return parent::getTableName($id);
    }
}

class DefaultTbl extends NotORMTest {
}

class UserFriends extends NotORMTest {
}

