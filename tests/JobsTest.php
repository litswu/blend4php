<?php
require_once '../src/Jobs.inc';
require_once '../src/GalaxyInstance.inc';
require_once 'testConfig.inc';


class JobsTest extends PHPUnit_Framework_TestCase {


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
   * Tests the retreival of specified jobs for the given user
   *
   * @depends testInitGalaxy
   */
  public function testIndex($galaxy){

    $jobs = new Jobs($galaxy);

    // There will be 7 cases to cover the testing,
    // One test for each of the parameters, one for all the input params
    // entered, and on for all the input params being null

    // Case One: All parameters are null
    $default = $jobs->index();
    $this->assertTrue(is_array($default), $jobs->getErrorMessage());

    // Case Two: Looking for jobs that are in a queued state (the array may
    // be empty if you do not have any queued jobs)
    $index = $jobs->index('queued');
    $this->assertTrue(is_array($index), $jobs->getErrorMessage());

    // Case Three: Assuming that the tools test suite was ran before this suite
    // we will have at least 1 job id to search for when we run the index
    // with a given job id number to look for
    $index = $jobs->index(NULL, $default[0]['tool_id']);
    $this->assertTrue(is_array($index), $jobs->getErrorMessage());

    // Case Four: Limit the search of jobs updated AFTER the specified date
    // This date is arbitrarily chosen
    $index = $jobs->index(NULL, NULL, '2016-03-09');
    $this->assertTrue(is_array($index), $jobs->getErrorMessage());

    // Case Five: Limit the search of jobs updated BEFORE this date
    // This date is also arbitrarily chosen
    $index = $jobs->index(NULL, NULL, NULL, '2016-03-09');
    $this->assertTrue(is_array($index), $jobs->getErrorMessage());

    // Case Six: Similar to Case Three with the difference of the history
    // id instead of the tool_id
    // TODO: I'm not sure about the $history_id interaction with index
    // this should be addressed later
    $index = $jobs->index(NULL, NULL, NULL, NULL, $default[0]['id']);
    $this->assertTrue(is_array($index), $jobs->getErrorMessage());

    // Case Seven: Enable all of the input params, this will elicit a empty
    // array (at least it should)
    $index = $jobs->index('paused', $default[0]['tool_id'], '2016-03-09', '2016-03-09',
            $default[0]['id']);
    $this->assertTrue(is_array($index), $jobs->getErrorMessage());

    return $default;

  }


  /**
   * Tests whether the function will return a specified tool's input datasets.
   *
   * @depends testIndex
   * @depends testInitGalaxy
   *
   * The jobs function will rely on the tools working
   */
  public function testInputs($default, $galaxy){
    $jobs = new Jobs($galaxy);

    $inputs = $jobs->inputs($default[0]['id']);

    $this->assertTrue(is_array($inputs), $jobs->getErrorMessage());
  }

  /**
   * Tests whether the function will return a specified tool's output datasets.
   *
   * @depends testIndex
   * @depends testInitGalaxy
   *
   * The jobs function will rely on the tools working
   */
  public function testOutputs($default, $galaxy){
    $jobs = new Jobs($galaxy);

    $outputs = $jobs->outputs($default[0]['id']);

    $this->assertTrue(is_array($outputs), $jobs->getErrorMessage());
  }

  /**
   * Tests whether the function will return a specified tool's metadata.
   *
   * @depends testIndex
   * @depends testInitGalaxy
   *
   * The jobs function will rely on the tools working
   */
  public function testShow($default, $galaxy){
    $jobs = new Jobs($galaxy);

    $show = $jobs->show($default[0]['id']);

    $this->assertTrue(is_array($show), $jobs->getErrorMessage());
  }

  /**
   * Tests Job's buildForRerun function
   * Builds a job for rerun
   * This function is incomplete, please see our issues page on github for more information.
   *
   * @depends testIndex
   * @depends testInitGalaxy
   */
  public function testBuildForReRun($default, $galaxy){
    $jobs = new Jobs($galaxy);

    // Case 1: Successfully build for rerun given a correct job id
    $rerun_job = $jobs->buildForRerun($default[0]['id']);
    $this->assertFalse($rerun_job);
  }

  /**
   * TODO: Input params are not correct
   *  We need to fix them at some point
   *  https://github.com/spficklin/GalaxyPAPI/issues/7
   *
   *
   * @depends testIndex
   * @depends testInitGalaxy
   */
  public function testSearch($default, $galaxy){

    $jobs = new Jobs($galaxy);

    $job = $jobs->search(array(
      'tool_id' => 'wc_gnu',
      //'id' => '4ff6f47412c3e65e',
      'inputs' => array('id' => '03501d7626bd192f', 'dataset_id' => '03501d7626bd192f'),
//       'status' => 'ok',
    ));
    //print_r($job);
    //$this->assertTrue(is_array($job), $jobs->getErrorMessage());
    //$this->assertTrue(!empty($job), "Job search returned no results.");

    // This function, for now, should always return false.
    $this->assertFalse($job, "Jobs search should return false");
  }
}