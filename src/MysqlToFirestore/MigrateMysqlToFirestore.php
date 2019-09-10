<?php

namespace MysqlToFirestore;

class MigrateMysqlToFirestore
{
    public static function write()
    {
        $docRef = $db->collection('users')->document('lovelace2');
        $docRef->set([
            'first' => 'Ada2',
            'last' => 'Lovelace2',
            'born' => 18152
        ]);
        printf('Added data to the lovelace2 document in the users collection.' . $NEW_LINE . $NEW_LINE);
    }
}