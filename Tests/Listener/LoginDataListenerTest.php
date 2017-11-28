<?php

namespace Lthrt\UserBundle\Tests\DataFixtures;

use Lthrt\UserBundle\Tests\TestWithFixtures;

// ...and rotator test

class LoginDataListenerTest extends TestWithFixtures
{
    private $login;
    private $logout;
    private $loginDataLength;

    public function tearDown()
    {
        // parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();
        $this->login           = static::$kernel->getContainer()->get('router')->generate('login');
        $this->logout          = static::$kernel->getContainer()->get('router')->generate('logout');
        $this->loginDataLength = static::$kernel->getContainer()->getParameter('lthrt_user.login_data_length');
    }

    public function testLoginRepeatedly()
    {
        $this->client->followRedirects();
        $i = 0;
        while (++$i < (2 * $this->loginDataLength)) {
            sleep(1);
            $crawler = $this->client->request('GET', $this->login);
            $form    = $crawler->filter('#login')->form();
            $form->setValues($this->loginInfo());
            $crawler = $this->client->submit($form);
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));

            $crawler = $this->client->request('GET', $this->logout);
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $this->assertFalse(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
        }
        sleep(1);
        $form = $crawler->filter('#login')->form();
        $form->setValues($this->loginInfo());
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));

        $loginDataCount = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:LoginData')
            ->createQueryBuilder('data')
            ->select('COUNT(data)')
            ->getQuery()
            ->getSingleScalarResult();
        $this->assertLessThanOrEqual($this->loginDataLength, $loginDataCount);
    }

    public function testBadLogin()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->login);
        $form    = $crawler->filter('#login')->form();
        $form->setValues($this->badLoginInfo());
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Bad credentials.', $this->client->getResponse()->getContent());
        $qb = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:LoginData')
            ->createQueryBuilder('data');
        $qb->andWhere($qb->expr()->eq('data.username', ':admin'))
            ->setParameter('admin', 'Admin');

        $loginData = $qb->getQuery()->getResult();
        $this->assertCount(1, $loginData);
        $this->assertFalse(current($loginData)->success);

    }

    private function loginInfo()
    {
        return [
            '_username' => 'Admin',
            '_password' => 'Admin',

        ];
    }

    private function badLoginInfo()
    {
        return [
            '_username' => 'Admin',
            '_password' => 'admin',

        ];
    }
}
