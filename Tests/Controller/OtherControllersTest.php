<?php

namespace Lthrt\UserBundle\Tests\Controller;

use Lthrt\UserBundle\Tests\TestWithFixtures;

class OtherControllersTest extends TestWithFixtures
{
    use OtherControllersTrait;

    private $delete;
    private $edit;
    private $index;
    private $login;
    private $new;
    private $show;

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
        $form->setValues($this->loginInfo());
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(static::$kernel->getContainer()->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
    }

    /**
     * @dataProvider getOtherControllersList
     */

    public function testGets(
        $controller,
        $repocall
    ) {
        $item = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:' . ucfirst($controller))
            ->$repocall('Delete');

        $this->new = static::$kernel->getContainer()
            ->get('router')
            ->generate($controller . '_new');

        $this->edit = static::$kernel->getContainer()
            ->get('router')
            ->generate($controller . '_edit', [$controller => $item->id]);

        $this->index = static::$kernel->getContainer()
            ->get('router')
            ->generate($controller . '_index');

        $this->show = static::$kernel->getContainer()
            ->get('router')
            ->generate($controller . '_show', [$controller => $item->id]);

        $crawler = $this->client->request('GET', $this->index);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->request('GET', $this->new);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('form[name="lthrt_userbundle_' . $controller . '"]')->count() > 0);

        $crawler = $this->client->request('GET', $this->show);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->request('GET', $this->edit);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('form[name="lthrt_userbundle_' . $controller . '"]')->count() > 0);
    }

    /**
     * @dataProvider getOtherControllersList
     */

    public function testPosts(
        $controller,
        $repocall,
        $fields
    ) {
        $new    = 'Test';
        $edit   = 'Modified';
        $delete = 'Delete';
        $item   = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:' . ucfirst($controller))
            ->$repocall($delete);

        $field = strtolower(str_replace('findOneBy', '', $repocall));
        $check = $item->$field;

        $this->new = static::$kernel->getContainer()
            ->get('router')
            ->generate($controller . '_new');

        $this->delete = static::$kernel->getContainer()
            ->get('router')
            ->generate($controller . '_show', [$controller => $item->id]);

        $crawler = $this->client->request('GET', $this->new);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('form[name="lthrt_userbundle_' . $controller . '"]')->count() > 0);

        $form = $crawler->filter('form[name="lthrt_userbundle_' . $controller . '"]')->form();
        foreach ($fields as $field) {
            $required[$field] = (strpos($field, 'email') !== false) ? $new . "@timeriech.org" : $new;
        }

        $form->setValues($required);
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $newTest = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:' . ucfirst($controller))
            ->$repocall($new);

        $this->assertNotNull($newTest);

        $crawler = $this->client->request('GET', $this->delete);
        $this->assertTrue($crawler->filter('form[name="lthrt_userbundle_' . $controller . '_delete"]')->count() > 0);
        $form    = $crawler->filter('form[name="lthrt_userbundle_' . $controller . '_delete"]')->form();
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains((ucFirst($controller) . "s list"), $this->client->getResponse()->getContent());
        $this->assertNotContains($check, $this->client->getResponse()->getContent());
        $deleteTest = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:' . ucfirst($controller))
            ->$repocall($delete);
        $this->assertNull($deleteTest);

        $this->edit = static::$kernel->getContainer()
            ->get('router')
            ->generate($controller . '_edit', [$controller => $newTest->id]);

        $crawler = $this->client->request('GET', $this->edit);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('form[name="lthrt_userbundle_' . $controller . '"]')->count() > 0);

        $form = $crawler->filter('form[name="lthrt_userbundle_' . $controller . '"]')->form();
        foreach ($fields as $field) {
            $required[$field] = (strpos($field, 'email') !== false) ? $edit . "@timeriech.org" : $edit;
        }

        $form->setValues($required);
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $editTest = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository('LthrtUserBundle:' . ucfirst($controller))
            ->$repocall($edit);
        $this->assertNotNull($editTest);
    }
}
