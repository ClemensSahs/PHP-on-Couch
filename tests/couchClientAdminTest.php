<?php

// error_reporting(E_STRICT);
error_reporting(E_ALL);

class couchClientAdminTest extends PHPUnit_Framework_TestCase
{

	private $admin = array("login"=>"admin2", "password"=>"sometest");
	private $aclient = null;


    public function setUp()
    {
        $config = require './tests/_files/config.php';
        $client_config = $config['databases']['client_test1'];
        $admin_config = $config['databases']['client_admin'];
        $this->client = new couchClient($client_config['uri'],$client_config['dbname']);

        $this->aclient = new couchClient($admin_config['uri'],$admin_config['dbname']);
        try {
            $this->aclient->deleteDatabase();
        } catch (Exception $e) {
        }
        $this->aclient->createDatabase();
    }

	public function tearDown()
    {
        $this->client = null;
		$this->aclient = null;
    }

    /**
     * @dataProvider dataProviderTestCreateAdmin
     *
     * @param unknown $login
     * @param unknown $password
     * @param array $roles
     */
    public function testCreateAdmin ($login,$password,$roles,$exception) {
        if ( is_array($exception) && $exception[0] !== null) {
            $this->setExpectedException($exception[0],$exception[1],$exception[2]);
        }

        $adm = new couchAdmin($this->aclient);
        $adm->createAdmin($this->admin["login"],$this->admin["password"]);
    }

    public function dataProviderTestCreateAdmin () {
        return array(
            array($this->admin["login"], $this->admin["password"],null,null),
            array($this->admin["login"], $this->admin["password"], array("fooReader","barWriter"),null),
            array("test", null, null,array('InvalidArgumentException',null,null)),
            array(null,"test", null,array('InvalidArgumentException',null,null)),
            array(null, null, null,array('InvalidArgumentException',null,null)),
        );
    }

    public function testAdminIsSet () {
        $this->setExpectedException('couchException','',412);

        $this->aclient->createDatabase();
    }

	public function testAdminCanAdmin () {
		$this->aclient->deleteDatabase();

		$ok = $this->aclient->createDatabase();
		$this->assertInternalType("object", $ok);
		$this->assertObjectHasAttribute("ok",$ok);
		$this->assertEquals($ok->ok,true);
		$ok = $this->aclient->deleteDatabase();
		$this->assertInternalType("object", $ok);
		$this->assertObjectHasAttribute("ok",$ok);
		$this->assertEquals($ok->ok,true);
// 		print_r($ok);
	}

	public function testUserAccount () {
		$adm = new couchAdmin($this->aclient);
		$ok = $adm->createUser("joe","dalton");
		$this->assertInternalType("object", $ok);
		$this->assertObjectHasAttribute("ok",$ok);
		$this->assertEquals($ok->ok,true);
// 		$ok = $adm->deleteUser("joe");
// 		print_r($ok);
	}

	public function testAllUsers () {
		$adm = new couchAdmin($this->aclient);
		$ok = $adm->getAllUsers(true);
		$this->assertInternalType("array", $ok);
		$this->assertEquals(count($ok),2);
	}

	public function testGetUser () {
		$adm = new couchAdmin($this->aclient);
		$ok = $adm->getUser("joe");
		$this->assertInternalType("object", $ok);
		$this->assertObjectHasAttribute("_id",$ok);
	}

	public function testUserAccountWithRole () {
		$roles = array("badboys","jailbreakers");
		$adm = new couchAdmin($this->aclient);
		$ok = $adm->createUser("jack","dalton",$roles);
		$this->assertInternalType("object", $ok);
		$this->assertObjectHasAttribute("ok",$ok);
		$this->assertEquals($ok->ok,true);
		$user = $adm->getUser("jack");
		$this->assertInternalType("object", $user);
		$this->assertObjectHasAttribute("_id",$user);
		$this->assertObjectHasAttribute("roles",$user);
		$this->assertInternalType("array", $user->roles);
		$this->assertEquals(count($user->roles),2);
		foreach ( $user->roles as $role ) {
			$this->assertEquals(in_array($role,$roles),true);
		}
	}

	public function testGetSecurity () {
// 		$this->aclient->createDatabase();
		$adm = new couchAdmin($this->aclient);
		$security = $adm->getSecurity();
		$this->assertObjectHasAttribute("admins",$security);
		$this->assertObjectHasAttribute("readers",$security);
		$this->assertObjectHasAttribute("names",$security->admins);
		$this->assertObjectHasAttribute("roles",$security->admins);
		$this->assertObjectHasAttribute("names",$security->readers);
		$this->assertObjectHasAttribute("roles",$security->readers);
// 		print_r($security);
	}

	public function testSetSecurity () {
// 		$this->aclient->createDatabase();
		$adm = new couchAdmin($this->aclient);
		$security = $adm->getSecurity();
		$security->admins->names[] = "joe";
		$security->readers->names[] = "jack";
		$ok = $adm->setSecurity($security);
		$this->assertInternalType("object", $ok);
		$this->assertObjectHasAttribute("ok",$ok);
		$this->assertEquals($ok->ok,true);

		$security = $adm->getSecurity();
		$this->assertEquals(count($security->readers->names),1);
		$this->assertEquals(reset($security->readers->names),"jack");
		$this->assertEquals(count($security->admins->names),1);
		$this->assertEquals(reset($security->admins->names),"joe");
	}

	public function testDatabaseAdminUser () {
		$adm = new couchAdmin($this->aclient);
		$ok = $adm->removeDatabaseAdminUser("joe");
		$this->assertInternalType("boolean", $ok);
		$this->assertEquals($ok,true);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->admins->names),0);
		$ok = $adm->addDatabaseAdminUser("joe");
		$this->assertInternalType("boolean", $ok);
		$this->assertEquals($ok,true);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->admins->names),1);
		$this->assertEquals(reset($security->admins->names),"joe");
	}

	public function testDatabaseReaderUser () {
		$adm = new couchAdmin($this->aclient);
		$ok = $adm->removeDatabaseReaderUser("jack");
		$this->assertInternalType("boolean", $ok);
		$this->assertEquals($ok,true);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->readers->names),0);
		$ok = $adm->addDatabaseReaderUser("jack");
		$this->assertInternalType("boolean", $ok);
		$this->assertEquals($ok,true);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->readers->names),1);
		$this->assertEquals(reset($security->readers->names),"jack");
	}

    public function testGetDatabaseAdminUsers () {
        $this->markTestIncomplete();
        $adm = new couchAdmin($this->aclient);
        $users = $adm->getDatabaseAdminUsers();
        $this->assertInternalType("array", $users);
        $this->assertGreaterThanOrEqual(1, count($users));
        $this->assertContains($this->admin['login'], $user);
    }

    public function testGetDatabaseReaderUsers () {
        $this->markTestIncomplete();
        $adm = new couchAdmin($this->aclient);
        $users = $adm->getDatabaseReaderUsers();
        $this->assertInternalType("array", $users);
        $this->assertGreaterThanOrEqual(1, count($users));
        $this->assertContains($this->admin['login'], $user);
    }

// roles

	public function testDatabaseAdminRole () {
		$adm = new couchAdmin($this->aclient);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->admins->roles),0);
		$ok = $adm->addDatabaseAdminRole("cowboy");
		$this->assertInternalType("boolean", $ok);
		$this->assertEquals($ok,true);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->admins->roles),1);
		$this->assertEquals(reset($security->admins->roles),"cowboy");
		$ok = $adm->removeDatabaseAdminRole("cowboy");
		$this->assertInternalType("boolean", $ok);
		$this->assertEquals($ok,true);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->admins->roles),0);
	}

	public function testDatabaseReaderRole () {
		$adm = new couchAdmin($this->aclient);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->readers->roles),0);
		$ok = $adm->addDatabaseReaderRole("cowboy");
		$this->assertInternalType("boolean", $ok);
		$this->assertEquals($ok,true);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->readers->roles),1);
		$this->assertEquals(reset($security->readers->roles),"cowboy");
		$ok = $adm->removeDatabaseReaderRole("cowboy");
		$this->assertInternalType("boolean", $ok);
		$this->assertEquals($ok,true);
		$security = $adm->getSecurity();
		$this->assertEquals(count($security->readers->roles),0);
	}

	public function testGetDatabaseAdminRoles () {
		$adm = new couchAdmin($this->aclient);
		$users = $adm->getDatabaseAdminRoles();
		$this->assertInternalType("array", $users);
		$this->assertEquals(0,count($users));
// 		$this->assertEquals("joe",reset($users));
	}

	public function testGetDatabaseReaderRoles () {
		$adm = new couchAdmin($this->aclient);
		$users = $adm->getDatabaseReaderRoles();
		$this->assertInternalType("array", $users);
		$this->assertEquals(0,count($users));
// 		$this->assertEquals("jack",reset($users));
	}

// /roles



	public function testUserRoles () {
		$adm = new couchAdmin($this->aclient);
		$user = $adm->getUser("joe");
		$this->assertInternalType("object", $user);
		$this->assertObjectHasAttribute("_id",$user);
		$this->assertObjectHasAttribute("roles",$user);
		$this->assertInternalType("array", $user->roles);
		$this->assertEquals(0,count($user->roles));
		$adm->addRoleToUser($user,"cowboy");
		$user = $adm->getUser("joe");
		$this->assertInternalType("object", $user);
		$this->assertObjectHasAttribute("_id",$user);
		$this->assertObjectHasAttribute("roles",$user);
		$this->assertInternalType("array", $user->roles);
		$this->assertEquals(1,count($user->roles));
		$this->assertEquals("cowboy",reset($user->roles));
		$adm->addRoleToUser("joe","trainstopper");
		$user = $adm->getUser("joe");
		$this->assertInternalType("object", $user);
		$this->assertObjectHasAttribute("_id",$user);
		$this->assertObjectHasAttribute("roles",$user);
		$this->assertInternalType("array", $user->roles);
		$this->assertEquals(2,count($user->roles));
		$this->assertEquals("cowboy",reset($user->roles));
		$this->assertEquals("trainstopper",end($user->roles));
		$adm->removeRoleFromUser($user,"cowboy");
		$user = $adm->getUser("joe");
		$this->assertInternalType("object", $user);
		$this->assertObjectHasAttribute("_id",$user);
		$this->assertObjectHasAttribute("roles",$user);
		$this->assertInternalType("array", $user->roles);
		$this->assertEquals(1,count($user->roles));
		$this->assertEquals("trainstopper",reset($user->roles));
		$adm->removeRoleFromUser("joe","trainstopper");
		$user = $adm->getUser("joe");
		$this->assertInternalType("object", $user);
		$this->assertObjectHasAttribute("_id",$user);
		$this->assertObjectHasAttribute("roles",$user);
		$this->assertInternalType("array", $user->roles);
		$this->assertEquals(0,count($user->roles));
	}



	public function testDeleteUser() {
		$adm = new couchAdmin($this->aclient);
		$ok = $adm->deleteUser("joe");
		$this->assertInternalType("object", $ok);
		$this->assertObjectHasAttribute("ok",$ok);
		$this->assertEquals($ok->ok,true);
		$ok = $adm->getAllUsers(true);
		$this->assertInternalType("array", $ok);
		$this->assertEquals(count($ok),2);
	}

    public function testDeleteAdmin() {
        $adm = new couchAdmin($this->aclient);
        $adm->createAdmin("secondAdmin","password");
        $adm->deleteAdmin("secondAdmin");
        $adm->createAdmin("secondAdmin","password");
    }

    public function testDeleteAdminWithNoLogin() {
        $this->setExpectedException("InvalidArgumentException");

        $adm = new couchAdmin($this->aclient);
        $adm->deleteAdmin("");
    }

	public function testUsersDatabaseName () {
		$adm = new couchAdmin($this->aclient,array("users_database"=>"test"));
		$this->assertEquals("test",$adm->getUsersDatabase());
		$adm = new couchAdmin($this->aclient);
		$this->assertEquals("_users",$adm->getUsersDatabase());
		$adm->setUsersDatabase("test");
		$this->assertEquals("test",$adm->getUsersDatabase());
	}


}
