<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_users_domain extends MY_Migration {

    public function up()
    {
        $data = [
            [
                'create_date' => now(),
                'name' => 'محمد فتحى',
                'email' => 'mohamed@netmechanics.net',
                'pic' => '',
                'phone' => '00201100661165',
                'gender' => 'male',
                'username' => 'admin',
                'password' => hash('ripemd160', 'changeMePlease' .$this->config->item('pw-salt')),
                'active' => '1',
                'epanel' => '2',
                'created_by' => '0'
            ],
            [
                'create_date' => now(),
                'name' => 'ندا احمد',
                'email' => 'nada@netmechanics.net',
                'pic' => '',
                'phone' => '00201100661165',
                'gender' => 'male',
                'username' => 'user',
                'password' => hash('ripemd160', 'changeMePlease' .$this->config->item('pw-salt')),
                'active' => '1',
                'epanel' => '4',
                'created_by' => '0'
            ],
        ];
        $this->db->insert_batch('users', $data);

        $data = [
            'name' => 'general',
            'active' => '1'
        ];
        $this->db->insert('notifi_domains', $data);

    }

    public function down()
    {
        $this->db->where('id !=', 1);
        $this->db->delete('users');
        $this->db->truncate('notifi_domains');
    }
}