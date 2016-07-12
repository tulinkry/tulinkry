<?php

namespace Tulinkry\Tester;

use Symfony;
use Nette;

trait TDoctrineSetup {
    /**
     * @var string database name
     */
    protected $dbname;
    
    /**
     *
     * @var array
     */
    protected $tables;
    
    /**
     * @var Kdyby\Doctrine\Connection
     */
    protected $db;

    public function prepareDb() {

        $app = $this->container->getByType('Kdyby\Console\Application');
        $db = $this->container->getByType('Doctrine\DBAL\Connection');
        $this->db = $this->container->getByType('Kdyby\Doctrine\Connection');
        
        $dbname = $db->getDatabase();
        $this->dbname = $dbname;
        
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = $db->getParams();
        unset($connectionParams['dbname']);
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        $conn->exec("DROP DATABASE IF EXISTS `" . $this->dbname . "`");
        $conn->exec("CREATE DATABASE IF NOT EXISTS `" . $this->dbname . "`");
        $conn->exec("USE `" . $this->dbname . "`");

        $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'orm:schema-tool:create']);
        $output = new Symfony\Component\Console\Output\NullOutput(); 
        
        $status = $app->run($input, $output);
    }
    
    public function loadDb($fixturesDir) {
        $db = $this->db;
        $db->transactional(function($conn) use ($fixturesDir) {
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            foreach ( Nette\Utils\Finder::findFiles("*.sql")->in($fixturesDir) as $key => $file ) {
                $sql = @file_get_contents($key);
                $conn->exec($sql);
            }
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        });
    }
    
    /*
     * public function __call($name, $arguments) {
       $db = $this->container->getByType('Kdyby\Doctrine\Connection');
       $sm = $db->getSchemaManager();
       if( in_array($name, $sm->listTables() )) {
           $db->getEntityManager()->find($this->tables[$name]);
       } else {
           throw new \Nette\InvalidArgumentException('Expects existing table. %s given', $name );
       }
    }*/
    
    
    public function destroyDb() {
        
        $db = $this->db;
        //$db->exec("DROP DATABASE IF EXISTS `" . $this->dbname . "`");
    }

}
 