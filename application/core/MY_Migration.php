<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Migration extends CI_Migration
{
    function createTable($tableName, $columnArray, $extraKeys = []){
        $this->dbforge->add_field(array_merge(
            [
                'id'          => [
                    'type'           => 'BIGINT',
                    'constraint'     => 11,
                    'unsigned'       => TRUE,
                    'auto_increment' => TRUE
                ]
            ],
            $columnArray
        ));
        $this->dbforge->add_key('id', TRUE);
        foreach($extraKeys as $key){
            $this->dbforge->add_key($key);
        }
        $this->dbforge->create_table($tableName);
    }


//   fields function to get field array ///////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function int($unsigned = false, $default = 0){
        return  [
            'type'           => 'INT',
            'constraint'     => 11,
            'default'        => $default,
            'unsigned'       => $unsigned
        ];
    }

    function tinyInt($unsigned = false, $default = 0){
        return  [
            'type'           => 'TINYINT',
            'constraint'     => 4,
            'default'        => $default,
            'unsigned'       => $unsigned
        ];
    }

    function bigInt($default = 0, $unsigned = false){
        return  [
            'type'           => 'BINGINT',
            'constraint'     => 4,
            'default'        => $default,
            'unsigned'       => $unsigned
        ];
    }

    function float($length = 12, $precision = 2, $default = 0.0, $unsigned = false){
        return  [
            'type'          => 'float',
            'constraint'    => $length.','.$precision,
            'default'       => $default,
            'unsigned'      => $unsigned
        ];
    }

    function decimal($length = 12, $precision = 2, $default = 0.0, $unsigned = false){
        return  [
            'type'          => 'DECIMAL',
            'constraint'    => $length.','.$precision,
            'default'       => $default,
            'unsigned'      => $unsigned
        ];
    }

    function varChar($length = 250, $default = '', $unique = false){
        return  [
            'type'           => 'VARCHAR',
            'constraint'     => $length,
            'null'           => is_null($default),
            'default'        => $default,
            'unique'         => $unique
        ];
    }

    function text($default = '', $nullable = false){
        return  [
            'type'           => 'TEXT',
            'default'        => $default,
            'null'           => $nullable,
        ];
    }

    function enum(array $values, $default = null){
        return [
            'type'           => 'ENUM',
            'constraint'     => $values,
            'default'        => isset($default) && in_array($default, $values)? $default : reset($values),
        ];
    }

    function foreignKey(){
        return [
            'type'           => 'BIGINT',
            'constraint'     => 11,
            'unsigned'       => TRUE,
            'default'        => 0,
            'null'           => false
        ];
    }

}