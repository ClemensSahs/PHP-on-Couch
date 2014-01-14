<?php


class couchTest extends PHPUnit_Framework_TestCase
{
    public $client;
    public $config;


    public function setUp()
    {
        $this->config = require './tests/_files/config.php';
        $config = $this->config['databases']['client_test1'];
        $this->client = new couchClient($config['uri'],$config['dbname']);
    }

    public function tearDown()
    {
        $this->client = null;
    }

    /**
     * @dataProvider dataProviderTestCreateSomeSimpleObject
     */
    public function testCreateSomeSimpleObject($dsn,$expected)
    {
        $couch = new couch( $dsn );

        $dsnParts = $couch->dsn_part();

        foreach ( $expected as $key => $value ) {
            if ( $value === null ) {
                $this->assertArrayNotHasKey($key, $dsnParts);
                continue;
            }

            $this->assertArrayHasKey($key, $dsnParts);

            $this->assertEquals($value, $dsnParts[$key]);
        }
    }

    public function dataProviderTestCreateSomeSimpleObject()
    {
        return array(
            array('http://user:pass@server:1337/path?query=test',
                  array('scheme' => 'http',
                        'user'   => 'user',
                        'pass'   => 'pass',
                        'host'   => 'server',
                        'port'   => '1337',
                        'path'   => '/path',
                        'query'  => 'query=test'
                      )
                ),
            array('http://user:pass@server/path?query=test',
                  array('scheme' => 'http',
                        'user'   => 'user',
                        'pass'   => 'pass',
                        'host'   => 'server',
                        'port'   => 80,
                        'path'   => '/path',
                        'query'  => 'query=test'
                      )
                )
            );
    }

}
