<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_install_epanel extends MY_Migration {

    public function up()
    {
        $this->createTable('users',[
            'create_date' => $this->int(),
            'name' => $this->varChar(),
            'email' => $this->varChar(),
            'pic' => $this->varChar(),
            'username' => $this->varChar(),
            'password' => $this->varChar(),
            'phone' => $this->varChar(),
            'gender' => $this->enum(['male','female']),
            'active' => $this->tinyInt(),
            'epanel' => $this->foreignKey(),
            'created_by' => $this->foreignKey(),
        ]);

        $this->createTable('user_setting',[
            'user_id' => $this->foreignKey(),
            'name' => $this->varChar(),
            'intval' => $this->int(),
            'strval' => $this->varChar(),
            'note' => $this->text()
        ]);

        $this->createTable('epanel_breath',[
            'session' => $this->varChar(45),
            'user_id' => $this->foreignKey(),
            'ipaddress' => $this->varChar(40),
            'start_time' => $this->int(),
            'last_time' => $this->int()
        ]);

        $this->createTable('epanel_logins',[
            'username' => $this->varChar(),
            'ip' => $this->varChar(40),
            'count' => $this->int(),
            'lasttime' => $this->int()
        ]);

        $this->createTable('epanel_roles',[
            'name' => $this->varChar(),
            'type' => $this->int(),
            'default' => $this->tinyInt(),
            'core' => $this->tinyInt(),
            'active' => $this->tinyInt(),
            'created_date' => $this->int()
        ]);

        $this->createTable('epanel_permissions',[
            'name' => $this->varChar(),
            'display' => $this->enum(['none', 'link', 'subMenu', 'newMenu']),
            'sort' => $this->int(),
            'parent' => $this->int(),
            'lvl' => $this->int(),
            'home_page' => $this->varChar(),
            'icon' => $this->varChar(),
        ]);

        $this->createTable('epanel_areas',[
            'name' => $this->varChar(),
            'display' => $this->enum(['none', 'link', 'title', 'subMenu', 'newMenu', 'ajax']),
            'parent' => $this->foreignKey(),
            'lvl' => $this->int(),
            'home_page' => $this->varChar(),
            'icon' => $this->varChar(),
        ]);

        $this->createTable('role_permission',[
            'permission_id' => $this->foreignKey(),
            'role_id' => $this->foreignKey()
        ]);

        $this->createTable('role_area',[
            'area_id' => $this->foreignKey(),
            'role_id' => $this->foreignKey()
        ]);

        $this->createTable('permission_area',[
            'permission_id' => $this->foreignKey(),
            'area_id' => $this->foreignKey()
        ]);

        $this->createTable('epanel_actions',[
            'name' => $this->varChar(),
            'link' => $this->varChar(),
            'active' => $this->tinyInt(),
            'note' => $this->text(),
        ]);

        $this->createTable('action_container',[
            'container' => $this->enum(['area', 'permission']),
            'action_id' => $this->foreignKey(),
            'container_id' => $this->foreignKey()
        ]);

        $this->createTable('epanel_setting',[
            'name' => $this->varChar(),
            'intval' => $this->int(),
            'strval' => $this->varChar(),
            'note' => $this->text()
        ]);

        $this->createTable('epanel_policies',[
            'name' => $this->varChar(),
            'link' => $this->varChar(),
            'policy' => $this->enum(['higher','equal','lower','self']),
            'type' => $this->enum(['request role','applied role','request user','applied user']),
            'intval' => $this->int(),
            'result' => $this->tinyInt(),
            'priority' => $this->int(),
            'active' => $this->tinyInt(),
            'note' => $this->text()
        ]);

        $this->createTable('notifications',[
            'user_id' => $this->foreignKey(),
            'user_role' => $this->foreignKey(),
            'text' => $this->text(),
            'date' => $this->int(),
            'new' => $this->tinyInt(),
            'link' => $this->varChar(),
            'link_type' => $this->enum(['permission','area','core','none']),
            'link_id' => $this->foreignKey(),
            'domain' => $this->foreignKey(),
            'notifi_user' => $this->foreignKey()
        ]);

        $this->createTable('notifi_domains',[
            'name' => $this->varChar(),
            'active' => $this->tinyInt()
        ]);

        $this->createTable('notifi_register',[
            'type' => $this->enum(['role','type','user']),
            'domain' => $this->foreignKey(),
            'type_id' => $this->foreignKey(),
        ]);

        $this->createTable('log',[
            'user_id' => $this->foreignKey(),
            'date' => $this->int(),
            'text' => $this->text(),
            'link' => $this->varChar(),
            'link_type' => $this->enum(['permission','area','core','table']),
            'link_id' => $this->foreignKey(),
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_table('users');
        $this->dbforge->drop_table('user_setting');
        $this->dbforge->drop_table('epanel_breath');
        $this->dbforge->drop_table('epanel_logins');
        $this->dbforge->drop_table('epanel_roles');
        $this->dbforge->drop_table('epanel_permissions');
        $this->dbforge->drop_table('epanel_areas');
        $this->dbforge->drop_table('role_permission');
        $this->dbforge->drop_table('role_area');
        $this->dbforge->drop_table('permission_area');
        $this->dbforge->drop_table('epanel_actions');
        $this->dbforge->drop_table('action_container');
        $this->dbforge->drop_table('epanel_setting');
        $this->dbforge->drop_table('epanel_policies');
        $this->dbforge->drop_table('notifications');
        $this->dbforge->drop_table('notifi_domains');
        $this->dbforge->drop_table('notifi_register');
        $this->dbforge->drop_table('log');
    }
}