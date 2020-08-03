<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_seed_epanel extends MY_Migration {

    public function up()
    {
        $data = [
            'id' => 1,
            'name' => 'home',
            'display' => 'link',
            'parent' => '0',
            'lvl' => '0',
            'home_page' => 'epanel/page/home',
            'icon'=> 'icon-home2'
        ];

        $this->db->insert('epanel_areas', $data);

        $data = [
            ['id' => '1','name' => 'newNotification','link' => 'epanel/newnotifications','policy' => 'higher','type' => 'request role','intval' => '1','result' => '1','priority' => '1','active' => '1','note' => ''],
            ['id' => '2','name' => 'showUsers','link' => 'users/User_epanel/getUsers','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '3','name' => 'getUserProfile','link' => 'users/User_epanel/getUserProfile','policy' => 'self','type' => 'request user','intval' => '1','result' => '1','priority' => '1','active' => '1','note' => ''],
            ['id' => '4','name' => 'getUserProfile','link' => 'users/User_epanel/getUserProfile','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '2','active' => '1','note' => ''],
            ['id' => '5','name' => 'showRoles','link' => 'epanel/role/getRoles','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '6','name' => 'showLog','link' => 'epanel/page/showLog','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '7','name' => 'epanelSetting','link' => 'epanel/page/getEpanelSetting','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '8','name' => 'getUserSetting','link' => 'users/User_epanel/getUserSetting','policy' => 'self','type' => 'request user','intval' => '1','result' => '1','priority' => '1','active' => '1','note' => ''],
            ['id' => '9','name' => 'getUserSetting','link' => 'users/User_epanel/getUserSetting','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '2','active' => '1','note' => ''],
            ['id' => '12','name' => 'setUserSetting','link' => 'users/User_epanel/setUserSetting','policy' => 'self','type' => 'request user','intval' => '1','result' => '1','priority' => '1','active' => '1','note' => ''],
            ['id' => '13','name' => 'setUserSetting','link' => 'users/User_epanel/setUserSetting','policy' => 'lower','type' => 'request user','intval' => '6','result' => '0','priority' => '2','active' => '1','note' => ''],
            ['id' => '14','name' => 'setepanelSetting','link' => 'epanel/page/setEpanelSetting','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '15','name' => 'switchRole','link' => 'epanel/role/switchRole','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '16','name' => 'addRole','link' => 'epanel/role/addRole','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '17','name' => 'defaultRole','link' => 'epanel/role/defaultRole','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '18','name' => 'editRole','link' => 'epanel/role/editRole','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '19','name' => 'deleteRole','link' => 'epanel/role/deleteRole','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '20','name' => 'switchUser','link' => 'epanel/user_epanel/switchUser','policy' => 'higher','type' => 'applied role','intval' => '6','result' => '1','priority' => '1','active' => '1','note' => ''],
            ['id' => '21','name' => 'addUser','link' => 'epanel/user_epanel/addUser','policy' => 'lower','type' => 'request role','intval' => '6','result' => '0','priority' => '1','active' => '1','note' => ''],
            ['id' => '22','name' => 'editUser','link' => 'epanel/user_epanel/editUser','policy' => 'higher','type' => 'applied role','intval' => '6','result' => '1','priority' => '1','active' => '1','note' => ''],
            ['id' => '23','name' => 'deleteUser','link' => 'epanel/role/deleteUser','policy' => 'higher','type' => 'request role','intval' => '6','result' => '1','priority' => '1','active' => '1','note' => ''],
            ['id' => '24','name' => 'passwordUser','link' => 'epanel/user_epanel/changePassword','policy' => 'higher','type' => 'applied role','intval' => '6','result' => '1','priority' => '1','active' => '1','note' => '']
        ];

        $this->db->insert_batch('epanel_policies', $data);

        $data = [
            ['id' => '1','name' => 'CodeMechanic','type' => '10','default' => '0','core' => '1','active' => '1','created_date' => now()],
            ['id' => '2','name' => 'owner','type' => '7','default' => '0','core' => '1','active' => '1','created_date' => now()],
            ['id' => '3','name' => 'superAdmin','type' => '6','default' => '0','core' => '1','active' => '1','created_date' => now()],
            ['id' => '4','name' => 'noRole','type' => '1','default' => '1','core' => '1','active' => '1','created_date' => now()],
            ['id' => '5','name' => 'panned','type' => '0','default' => '0','core' => '1','active' => '1','created_date' => now()],
        ];

        $this->db->insert_batch('epanel_roles', $data);

        $data =[
            ['id' => '1','name' => 'defaultLanguage','intval' => '0','strval' => 'arabic','note' => ''],
            ['id' => '2','name' => 'maxLoginTry','intval' => '5','strval' => '','note' => ''],
            ['id' => '3','name' => 'siteAccount','intval' => '0','strval' => '','note' => ''],
            ['id' => '4','name' => 'multiarea','intval' => '0','strval' => '','note' => ''],
            ['id' => '5','name' => 'strictIPaddress','intval' => '1','strval' => '','note' => ''],
            ['id' => '6','name' => 'breathTimer','intval' => '50','strval' => '','note' => ''],
            ['id' => '7','name' => 'siteLink','intval' => '0','strval' => '','note' => ''],
            ['id' => '8','name' => 'siteBrand','intval' => '0','strval' => 'sign_reversed.png','note' => '']
        ];

        $this->db->insert_batch('epanel_setting', $data);

        $data = [
            'id' => '1',
            'create_date' => now(),
            'name' => 'محمود الغول',
            'email' => 'CodeMechanic@netmechanics.net',
            'pic' => 'CM-logo.png',
            'phone' => '0021100661165',
            'gender' => 'male',
            'username' => 'codemechanic',
            'password' => '1196aeee63206e3a2d6374d84538acbd90d93b9f',
            'active' => '1',
            'epanel' => '1',
            'created_by' => '0'
        ];
        $this->db->insert('users', $data);

        $data = [
            ['id' => '1','user_id' => '1','name' => 'language','intval' => '0','strval' => 'arabic','note' => ''],
            ['id' => '2','user_id' => '1','name' => 'notificationCount','intval' => '7','strval' => '','note' => ''],
            ['id' => '3','user_id' => '1','name' => 'themeColor','intval' => '0','strval' => 'green','note' => '']
        ];
        $this->db->insert_batch('user_setting', $data);


    }

    public function down()
    {
        $this->db->turncute('epanel_areas');
        $this->db->turncute('epanel_policies');
        $this->db->turncute('epanel_roles');
        $this->db->turncute('epanel_setting');
        $this->db->turncute('users');
        $this->db->turncute('user_setting');
    }
}