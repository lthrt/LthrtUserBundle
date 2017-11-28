<?php

namespace Lthrt\UserBundle\Tests\Controller;

use Lthrt\UserBundle\Entity\Role;
use Lthrt\UserBundle\Tests\Entity\TestUser;
use Lthrt\UserBundle\Tests\TestWithFixtures;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

class EntityTest extends TestWithFixtures
{
    public function tearDown()
    {
        // parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();
        $this->login = static::$kernel->getContainer()->get('router')->generate('login');
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => 'Admin',
            '_password' => 'Admin',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
    }

    public function testUserEqualTo()
    {
        $user = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:User')
            ->findOneByUsername('User');

        $admin = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:User')
            ->findOneByUsername('Admin');

        $this->assertFalse($admin->isEqualTo($user));

        $user = static::$kernel->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertTrue($admin->isEqualTo($user));

        $user           = clone $admin;
        $user->username = 'change';
        $this->assertFalse($admin->isEqualTo($user));

        $user           = clone $admin;
        $user->password = 'change';
        $this->assertFalse($admin->isEqualTo($user));

        $user       = clone $admin;
        $user->salt = 'change';
        $this->assertFalse($admin->isEqualTo($user));

        $other = new TestUser();
        $this->assertFalse($admin->isEqualTo($other));
    }

    public function testRoleSerialize()
    {
        $role = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:Role')
            ->findOneByRole('ROLE_USER');
        $this->assertEquals('a:2:{i:0;s:9:"ROLE_USER";i:1;i:2;}', $role->serialize());

        $role = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:Role')
            ->findOneByRole('ROLE_ADMIN');
        $this->assertEquals('a:2:{i:0;s:10:"ROLE_ADMIN";i:1;i:1;}', $role->serialize());

        $unserialized = new Role();
        $unserialized->unserialize('a:2:{i:0;s:11:"ROLE_SERIAL";i:1;i:11;}');
        $this->assertEquals("ROLE_SERIAL", $unserialized->role);
        $this->assertEquals(11, $unserialized->id);
        $this->assertNull($unserialized->description);
    }

    public function testGroup()
    {
        $red = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:Group')
            ->findOneByName('Red');
        $green = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:Group')
            ->findOneByName('Green');
        $white = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:Group')
            ->findOneByName('White');
        $purple = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:Group')
            ->findOneByName('Purple');
        $blue = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:Group')
            ->findOneByName('Blue');

        $green->addSuperGroup($red);
        $this->assertContains($green, $red->subGroup->toArray());
        $this->assertContains($red, $green->superGroup->toArray());

        $green->addSubGroup($white);
        $this->assertContains($green, $white->superGroup->toArray());
        $this->assertContains($white, $green->subGroup->toArray());
        $this->assertInternalType('string', $white->__toString());
        $this->assertEquals('White', $white->__toString());

        $this->assertContains($white, $purple->getAllSubGroups()->toArray());
        $this->assertContains($green, $purple->getAllSubGroups()->toArray());
        $this->assertContains($blue, $purple->getAllSubGroups()->toArray());
        $this->assertContains($red, $purple->getAllSubGroups()->toArray());
        $this->assertContains($green, $red->getAllSubGroups()->toArray());
        $this->assertContains($white, $red->getAllSubGroups()->toArray());
        $this->assertContains($white, $green->getAllSubGroups()->toArray());
        $this->assertEmpty($blue->getAllSubGroups()->toArray());
        $this->assertEmpty($white->getAllSubGroups()->toArray());

        $this->assertContains($purple, $white->getAllSuperGroups()->toArray());
        $this->assertContains($green, $white->getAllSuperGroups()->toArray());
        $this->assertContains($red, $white->getAllSuperGroups()->toArray());
        $this->assertContains($purple, $green->getAllSuperGroups()->toArray());
        $this->assertContains($red, $green->getAllSuperGroups()->toArray());
        $this->assertContains($purple, $red->getAllSuperGroups()->toArray());
        $this->assertContains($purple, $blue->getAllSuperGroups()->toArray());
        $this->assertEmpty($purple->getAllSuperGroups()->toArray());
    }

    public function testLoginData()
    {
        $admin = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:User')
            ->findOneByUsername('Admin');
        $loginData = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:LoginData')
            ->findOneByUser($admin);
        $this->assertInternalType('string', $loginData->__toString());
        $this->assertInternalType('string', $admin->__toString());
        $this->assertEquals('Admin', $admin->__toString());
    }

    public function testEncodePassword()
    {
        $inactive = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:User')
            ->findOneByUsername('Inactive');

        $encoder     = new BCryptPasswordEncoder(static::$kernel->getContainer()->getParameter('bcrypt_cost'));
        $initialHash = $inactive->password;
        $inactive->encodePassword($encoder);
        $this->assertEquals($inactive->password, $initialHash);

        $inactive->plainPassword = 'new_plain_password';
        $this->assertNotNull($inactive->plainPassword);
        $inactive->encodePassword($encoder);
        $this->assertNotEquals($inactive->password, $initialHash);
        $this->assertNull($inactive->plainPassword);
    }
}
