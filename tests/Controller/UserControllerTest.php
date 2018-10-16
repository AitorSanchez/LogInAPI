<?php
/**
 * Created by PhpStorm.
 * User: aitor
 * Date: 16/10/18
 * Time: 10:54
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    //public constants used in tests
    const USER1 = "userTest1"; //FIXME: change this user to a non existing one in the DB
    const USER2 = "userTest2";
    const USER3 = "userTest3";
    const USER4 = "userTest4";
    const USER5 = "userTest5";
    const USER6 = "userTest6";
    const USER7 = "userTest7";
    const USER8 = "userTest8";

    const PASS1 = "1234";
    const PASS2 = "1234567abc!?*";
    const PASS3 = "12abc!?*c5h8";
    const PASS4 = "!fg?*j8_c5%h8&";
    const PASS5 = "qwerty";
    const PASS6 = "qwerty234567";
    const PASS7 = "qwerty!&%)";



    //-----------------------------------------------------------------------
    //---------------Test for check user exists method-----------------------
    //-----------------------------------------------------------------------
    /**
     * using a non existing user --> expected a 400 response
     */
    public function testGetExistsUserAction()
    {
        $client = static::createClient();
        $client->request('GET', '/existsUser/'.self::USER1);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * creating a user and asking later for him --> expected a 200 response
     */
    public function testGetExistsUserAction2()
    {
        $client = static::createClient();
        $client->request('POST', '/newUser', array('user' => self::USER2, 'pass' => self::PASS1));
        $client->request('GET', '/existsUser/'.self::USER2);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * sending a GET without user name --> expected a 404 response
     */
    public function testGetExistsUserAction3()
    {
        $client = static::createClient();
        $client->request('GET', '/existsUser/');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }




    //-----------------------------------------------------------------------
    //---------------Test for match user-pass method-----------------------
    //-----------------------------------------------------------------------

    /**
     * sending a GET with a non existing user name --> expected a 400 response
     */
    public function testGetMatchUserPassAction()
   {
       $client = static::createClient();
       $client->request('GET', '/matchUserPass/'.self::USER1.'/'.self::PASS1);
       $this->assertEquals(400, $client->getResponse()->getStatusCode());
   }

    /**
     * creating a new user and asking for him with an invalid password --> expected a 400 response
     */
    public function testGetMatchUserPassAction2()
    {
        $client = static::createClient();
        $client->request('POST', '/newUser', array('user' => self::USER3, 'pass' => self::PASS2));
        $client->request('GET', '/matchUserPass/'.self::USER3.'/'.self::PASS1);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * creating a new user and asking for him with an invalid user name --> expected a 400 response
     */
    public function testGetMatchUserPassAction3()
    {
        $client = static::createClient();
        $client->request('POST', '/newUser', array('user' => self::USER4, 'pass' => self::PASS3));
        $client->request('GET', '/matchUserPass/'.self::USER3.'/'.self::PASS3);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * asking for a user without parameters --> expected a 404 response
     */
    public function testGetMatchUserPassAction4()
    {
        $client = static::createClient();
        $client->request('GET', '/matchUserPass/');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * creating a new user and asking for him with correct params --> expected a 200 response
     */
    public function testGetMatchUserPassAction5()
    {
        $client = static::createClient();
        $client->request('POST', '/newUser', array('user' => self::USER5, 'pass' => self::PASS4));
        $client->request('GET', '/matchUserPass/'.self::USER5.'/'.self::PASS4);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }


    //-----------------------------------------------------------------------
    //---------------Test for update user-pass method-----------------------
    //-----------------------------------------------------------------------

    /**
     * Try to change password to a non existing user --> expected a 400 response
     */
    public function testPutUserPassAction() {
        $client = static::createClient();
        $client->request('GET', '/updateUserPass/'.self::USER1.'/'.self::PASS1.'/'.self::PASS2);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * Create a user and send an invalid password --> expected a 400 response
     */
    public function testPutUserPassAction2() {
        $client = static::createClient();
        $client->request('POST', '/newUser', array('user' => self::USER6, 'pass' => self::PASS5));
        $client->request('GET', '/updateUserPass/'.self::USER6.'/'.self::PASS1.'/'.self::PASS2);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * Create a user and send the new password equals to the old --> expected a 400 response
     */
    public function testPutUserPassAction3() {
        $client = static::createClient();
        $client->request('POST', '/newUser', array('user' => self::USER7, 'pass' => self::PASS6));
        $client->request('GET', '/updateUserPass/'.self::USER7.'/'.self::PASS6.'/'.self::PASS6);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * Asking for a user without parameters --> expected a 404 response
     */
    public function testPutUserPassAction4()
    {
        $client = static::createClient();
        $client->request('GET', '/updateUserPass/');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * Create a user and change the password with valid parameters --> expected a 200 response
     */
    public function testPutUserPassAction5() {
        $client = static::createClient();
        $client->request('POST', '/newUser', array('user' => self::USER8, 'pass' => self::PASS7));
        $client->request('GET', '/updateUserPass/'.self::USER8.'/'.self::PASS7.'/'.self::PASS1);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
