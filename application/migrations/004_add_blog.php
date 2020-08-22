<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_blog extends MY_Migration {

    public function up()
    {

        $permissions = [
            'id' => 1,
            'name' => 'blogs',
            'display' => 'link',
            'sort' => '10',
            'parent' => '0',
            'lvl' => '0',
            'home_page' => 'blog/',
            'icon'=> 'icon-blog'
        ];

        $this->db->insert('epanel_permissions', $permissions);
    }

    public function down()
    {
        $this->db->where('permission_id', 1);
        $this->db->delete('epanel_permissions');
    }
}