<?php

use Phinx\Migration\AbstractMigration;

class Init extends AbstractMigration
{
    public function up()
    {
        $phoneNumbers = $this->table('phone_number');
        $phoneNumbers
              ->addColumn('first_name', 'string', ['limit' => 64])
              ->addColumn('last_name', 'string', ['limit' => 64], ['null' => true])
              ->addColumn('country_code', 'string', ['limit' => 2], ['null' => true])
              ->addColumn('timezone', 'string', ['limit' => 32], ['null' => true])
              ->addColumn('value', 'string', ['limit' => 20])
              ->addColumn('inserted_on', 'datetime')
              ->addColumn('updated_on', 'datetime')
              ->addIndex(['value'], ['unique' => true])
              ->create();
    }

    public function down()
    {
        $this->table('phone_number')->drop()->save();
    }
}
