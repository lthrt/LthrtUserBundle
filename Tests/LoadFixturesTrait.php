<?php

namespace Lthrt\UserBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

trait LoadFixturesTrait
{
    public static function loadFixtures(EntityManager $em)
    {
        static::generateSchema($em);

        //Create a new loader
        $loader = new Loader();

        //Load from the directory
        $loader->loadFromDirectory(dirname(__DIR__) . '/DataFixtures/ORM');

        //Put in the temp db
        $purger   = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures(), true);
    }

    public static function generateSchema(EntityManager $em)
    {
        // Get the metadata of the application to create the schema.
        $metadata = static::getMetadata($em);
        if (!empty($metadata)) {
            // Create SchemaTool
            $tool = new SchemaTool($em);
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
        } else {
            throw new Doctrine\DBAL\Schema\SchemaException('No Metadata Classes to process.');
        }
    }

    public static function getMetadata(EntityManager $em)
    {
        return $em->getMetadataFactory()->getAllMetadata();
    }
}
