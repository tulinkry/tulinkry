<?php

namespace Tulinkry\Tester;

use Tester;

class DoctrineTestCase extends Tester\TestCase
{
    protected $container;
    
    use TDoctrineSetup;
    
    public function __construct($container) {
        $this->container = $container;
    }
    
    public function setUp() {
        parent::setUp();
        Tester\Environment::lock('database', dirname(TEMP_DIR));
        $this->prepareDb();
        $this->loadDb( APP_DIR . "/../tests/fixtures");
    }
    
    public function tearDown() {
        $this->destroyDb();
        parent::tearDown();
    }
}