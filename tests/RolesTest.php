<?php

require_once '../src/Roles.inc';
require_once '../src/Users.inc';
require_once '../src/Groups.inc';
require_once '../src/GalaxyInstance.inc';
require_once './testConfig.inc';



class RolesTest extends PHPUnit_Framework_TestCase {

  /**
   * Intializes the Galaxy object for all of the tests.
   *
   * This function provides the $galaxy object to all other tests as they
   * are dependent on this one.
   */
  function testInitGalaxy() {
    global $config;

    // Connect to Galaxy.
    $galaxy = new GalaxyInstance($config['host'], $config['port'], FALSE);
    $success = $galaxy->authenticate($config['email'], $config['pass']);
    $this->assertTrue($success, $galaxy->getErrorMessage());

    return $galaxy;
  }


  /**
   * Tests the index() function.
   *
   * The index function retrieves a list of roles.
   *
   * @depends testInitGalaxy
   */
  function testIndex($galaxy) {

    $roles = new Roles($galaxy);

    // Case 1:  Are we getting an array?
    $roles_list = $roles->index();
    $this->assertTrue(is_array($roles_list), $roles->getErrorMessage());

    return $roles_list;
  }

  /**
   * Test the show() function of the Histories class.
   *
   * @depends testInitGalaxy
   * @depends testIndex
   *
   */
  public function testShow($galaxy, $roles_list) {

    $roles = new Roles($galaxy);

    // Use the history ID of the first history in the list to test the
    // show() function.
    $role_id = $roles_list[0]['id'];

    // Case 1:  Are we getting an array?  If so, that's all we need to
    // test. We don't need to do unit testing for galaxy. We assume the
    // array is correct.
    $role = $roles->show($role_id);
    $this->assertTrue(is_array($role), $roles->getErrorMessage());
  }

  /**
   * Tests the create() function of the Histories class.
   *
   * @depends testInitGalaxy
   * @depends testShow
   */
  public function testCreate($galaxy) {

    $roles = new Roles($galaxy);
    $users = new Users($galaxy);

    // First get the list of users that we'll add to our test role.
    $user_list = $users->index();
    $user_ids = array();
    foreach ($user_list as $user) {
      $user_ids[] = $user['id'];
    }

    // Case 1: Create a role without any users.
    $role_name = uniqid('galaxy-php-test-create1-');
    $role = $roles->create($role_name, 'Test role #1');
    $this->assertTrue(is_array($role), $roles->getErrorMessage());

    // Case 2: Try recreating the role with the same name. We should
    // recieve a FALSE return value.
    $role = $roles->create($role_name, 'Test role #1');
    $this->assertFalse($role, 'If the role already exists the create() function should return FALSE: ' . print_r($role, TRUE));

    // Case 3: Create another role and add all the users to it.
    $role_name = uniqid('galaxy-php-test-create2-');
    $role = $roles->create($role_name, 'Test role #2', $user_ids);
    $this->assertTrue(is_array($role), $roles->getErrorMessage());
    // TODO: need a way to determine if all of the users were added to the role?

    // Case 4: Create another role and add a set of groups to it.  But, both
    // the Roles and the Groups class create() functions can both
    // accept groups and roles respectively, we have to repeat some of the
    // unit testing for each here, because we can't add groups to a role
    // if we don't have any groups in the database and we can't add groups
    // if we don't first test they can be added.
    $groups = new Groups($galaxy);
    $group_ids = array();

    // Create two groups without any users then use their IDs for a new role
    $group_name = uniqid('galaxy-php-test-role-group1-');
    $group = $groups->create($group_name);
    $this->assertTrue(is_array($group), $groups->getErrorMessage());
    $group_ids[] = $group['id'];

    $group_name = uniqid('galaxy-php-test-role-group2-');
    $group = $groups->create($group_name);
    $this->assertTrue(is_array($group), $groups->getErrorMessage());
    $group_ids[] = $group['id'];

    $role_name = uniqid('galaxy-php-test-create3-');
    $role = $roles->create($role_name, 'Test role #3', array(), $group_ids);
    $this->assertTrue(is_array($role), $roles->getErrorMessage());
    // TODO: need a way to determine if the groups were added to the role?

    // Case 5: Create another user and add both users and groups.
    $role_name = uniqid('galaxy-php-test-create4-');
    $role = $roles->create($role_name, 'Test role #4', $user_ids, $group_ids);
    $this->assertTrue(is_array($role), $roles->getErrorMessage());
    // TODO: need a way to determine if the users and groups were added to the role?

  }
}