<?php

namespace Lthrt\UserBundle\Tests\Controller;

use Lthrt\UserBundle\Tests\TestWithFixtures;

class SecurityControllerTest extends TestWithFixtures
{
    use SecurityControllerTrait;

    private $login;
    private $logout;
    private $register;

    public function tearDown()
    {
        // parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();
        $this->login    = static::$kernel->getContainer()->get('router')->generate('login');
        $this->logout   = static::$kernel->getContainer()->get('router')->generate('logout');
        $this->register = static::$kernel->getContainer()->get('router')->generate('register');
    }

    public function testGetLoginPage()
    {
        $crawler = $this->client->request('GET', $this->login);
        $this->assertTrue($crawler->filter('form#login')->count() > 0);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetRegisterPage()
    {
        $crawler = $this->client->request('GET', $this->register);
        $this->assertTrue($crawler->filter('form#register')->count() > 0);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider badCredentialsProvider
     */
    public function testLoginBadCredentials(
        $username,
        $password
    ) {
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Bad credentials.', $this->client->getResponse()->getContent());
        $this->assertTrue($crawler->filter('form#login')->count() > 0);

    }

    public function testAnonymousAuthentication()
    {
        $crawler = $this->client->request('GET', $this->login);
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY'));
    }

    /**
     * @dataProvider goodAdminProvider
     */
    public function testLoginGoodAdmin(
        $username,
        $password
    ) {
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
        $user = static::$kernel->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertEquals('Admin', $user->username);
        $this->assertEquals('admin@timereich.org', $user->email);
        $this->assertContains('ROLE_ADMIN', $user->role);

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $now     = new \DateTime();
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
        $user = static::$kernel->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertEquals('Admin', $user->username);
        $this->assertEquals('admin@timereich.org', $user->email);
        $this->assertContains('ROLE_ADMIN', $user->role);

        $loginData = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:LoginData')
            ->findOneBy(['user' => $user->getId(), 'updated' => $now]);
        $this->assertNotNull($loginData);

    }

    /**
     * @dataProvider goodUserProvider
     */

    public function testLoginGoodUser(
        $username,
        $password
    ) {
        // var_dump(get_class_methods(static::$kernel->getContainer()));die;
        // ->getSession()->get('security'));die;

        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $user = static::$kernel->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
        $this->assertEquals('User', $user->username);
        $this->assertEquals('user@timereich.org', $user->email);
        $this->assertContains('ROLE_USER', $user->role);

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $now     = new \DateTime();
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
        $user = static::$kernel->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertEquals('User', $user->username);
        $this->assertEquals('user@timereich.org', $user->email);
        $this->assertContains('ROLE_USER', $user->role);

        $loginData = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:LoginData')
            ->findOneBy(['user' => $user->getId(), 'updated' => $now]);
        $this->assertNotNull($loginData);

    }

    /**
     * @dataProvider inactiveProvider
     */
    public function testLoginInactive(
        $username,
        $password
    ) {
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('User account is disabled.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertFalse(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
    }

    /**
     * @dataProvider badRoleProvider
     */
    public function testLoginBadRole(
        $username,
        $password
    ) {
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        // $this->assertContains('Account is disabled.', $this->client->getResponse()->getContent());
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
    }

    /**
     * @dataProvider noRoleProvider
     */
    public function testLoginNoRole(
        $username,
        $password
    ) {
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        // $this->assertContains('Account is disabled.', $this->client->getResponse()->getContent());
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
    }

    public function testRegisterSuccess()
    {
        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]'              => 'Friend',
            'registration[email]'                 => 'Friend@timereich.org',
            'registration[plainPassword][first]'  => 'Friend',
            'registration[plainPassword][second]' => 'Friend',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!!static::$kernel->getContainer()->get('doctrine')->getManager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Friend'));

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]'              => 'Buddy',
            'registration[email]'                 => 'Buddy@timereich.org',
            'registration[plainPassword][first]'  => 'Buddy',
            'registration[plainPassword][second]' => 'Buddy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('form#login')->count() > 0);
        $this->assertTrue(!!static::$kernel->getContainer()->get('doctrine')->getManager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Buddy'));

    }
    public function testRegisterFail()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]' => 'Guy',
            // 'registration[email]'    => 'Guy@timereich.org',
            // 'registration[plainPassword][first]'  => 'Guy',
            // 'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            // 'registration[username]' => 'Guy',
            'registration[email]' => 'Guy@timereich.org',
            // 'registration[plainPassword][first]'  => 'Guy',
            // 'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            // 'registration[username]' => 'Guy',
            // 'registration[email]' => 'Guy@timereich.org',
            'registration[plainPassword][first]' => 'Guy',
            // 'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            // 'registration[username]' => 'Guy',
            // 'registration[email]' => 'Guy@timereich.org',
            // 'registration[plainPassword][first]'  => 'Guy',
            'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]' => 'Guy',
            'registration[email]'    => 'Guy@timereich.org',
            // 'registration[plainPassword][first]'  => 'Guy',
            // 'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]'             => 'Guy',
            // 'registration[email]' => 'Guy@timereich.org',
            'registration[plainPassword][first]' => 'Guy',
            // 'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]'              => 'Guy',
            // 'registration[email]' => 'Guy@timereich.org',
            // 'registration[plainPassword][first]'  => 'Guy',
            'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            // 'registration[username]' => 'Guy',
            'registration[email]'                => 'Guy@timereich.org',
            'registration[plainPassword][first]' => 'Guy',
            // 'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value is not valid.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            // 'registration[username]' => 'Guy',
            'registration[email]'                 => 'Guy@timereich.org',
            // 'registration[plainPassword][first]'  => 'Guy',
            'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value is not valid.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            // 'registration[username]' => 'Guy',
            // 'registration[email]' => 'Guy@timereich.org',
            'registration[plainPassword][first]'  => 'Guy',
            'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]'             => 'Guy',
            'registration[email]'                => 'Guy@timereich.org',
            'registration[plainPassword][first]' => 'Guy',
            // 'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value is not valid.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]'              => 'Guy',
            'registration[email]'                 => 'Guy@timereich.org',
            // 'registration[plainPassword][first]'  => 'Guy',
            'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value is not valid.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]'              => 'Guy',
            // 'registration[email]' => 'Guy@timereich.org',
            'registration[plainPassword][first]'  => 'Guy',
            'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            // 'registration[username]' => 'Guy',
            // 'registration[email]' => 'Guy@timereich.org',
            // 'registration[plainPassword][first]'  => 'Guy',
            // 'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value should not be blank.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy'));

        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            // 'registration[username]' => 'Guy',
            'registration[email]'                 => 'Guy@timereich.org',
            'registration[plainPassword][first]'  => 'Guy',
            'registration[plainPassword][second]' => 'Guy',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('form#login')->count() > 0);
        $this->assertTrue(!!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Guy@timereich.org'));
    }

    public function testAbsurdPassword()
    {
        $password = str_repeat('Absurd', 4000);
        $crawler  = $this->client->request('GET', $this->register);
        $form     = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]'              => 'Absurd',
            'registration[email]'                 => 'Absurd@timereich.org',
            'registration[plainPassword][first]'  => $password,
            'registration[plainPassword][second]' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('This value is too long. It should have 4096 characters or less.', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(!static::$kernel->getcontainer()->get('doctrine')->getmanager()->getRepository('LthrtUserBundle:User')->findOneByUsername('Absurd'));
    }

    /**
     * @dataProvider goodUserProvider
     */

    public function testLogoutGoodUser(
        $username,
        $password
    ) {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $user = static::$kernel->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
        $this->assertEquals('User', $user->username);
        $this->assertEquals('user@timereich.org', $user->email);
        $this->assertContains('ROLE_USER', $user->role);

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->logout);
        $this->assertFalse(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
    }

    /**
     * @dataProvider goodAdminProvider
     */

    public function testLogoutGoodAdmin(
        $username,
        $password
    ) {
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues([
            '_username' => $username,
            '_password' => $password,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $user = static::$kernel->getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
        $this->assertEquals('Admin', $user->username);
        $this->assertEquals('admin@timereich.org', $user->email);
        $this->assertContains('ROLE_ADMIN', $user->role);

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->logout);
        $this->assertFalse(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
    }

    /**
     * @dataProvider goodAdminProvider
     */

    public function testDuplicateRegister()
    {
        $crawler = $this->client->request('GET', $this->register);
        $form    = $crawler->filter('#register')->form();
        $form->setValues([
            'registration[username]'              => 'User',
            'registration[email]'                 => 'user@timereich.org',
            'registration[plainPassword][first]'  => 'User',
            'registration[plainPassword][second]' => 'User',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertContains('Username already taken', $this->client->getResponse()->getContent());
        $this->assertContains('Email already taken', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
