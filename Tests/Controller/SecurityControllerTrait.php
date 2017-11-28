<?php

namespace Lthrt\UserBundle\Tests\Controller;

trait SecurityControllerTrait
{
    public function badCredentialsProvider()
    {
        return [
            [
                '_username' => 'admin',
                '_password' => 'admin',
            ],
            [
                '_username' => 'admin',
                '_password' => 'admin',
            ],
            [
                '_username' => 'user',
                '_password' => 'user',
            ],
            [
                '_username' => 'User',
                '_password' => 'user',
            ],
        ];
    }

    public function goodAdminProvider()
    {
        return [
            [
                '_username' => 'Admin',
                '_password' => 'Admin',
            ],
        ];
    }

    public function goodUserProvider()
    {
        return [
            [
                '_username' => 'User',
                '_password' => 'User',
            ],
        ];
    }

    public function inactiveProvider()
    {
        return [
            [
                '_username' => 'Inactive',
                '_password' => 'Inactive',
            ],
        ];
    }

    public function badRoleProvider()
    {
        return [
            [
                '_username' => 'BadRole',
                '_password' => 'BadRole',
            ],
        ];
    }

    public function noRoleProvider()
    {
        return [
            [
                '_username' => 'NoRole',
                '_password' => 'NoRole',
            ],
        ];
    }
}
