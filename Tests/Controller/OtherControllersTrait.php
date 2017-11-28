<?php

namespace Lthrt\UserBundle\Tests\Controller;

trait OtherControllersTrait
{
    public function loginInfo()
    {
        return [
            '_username' => 'Admin',
            '_password' => 'Admin',

        ];
    }

    public function getOtherControllersList()
    {
        return [
            [
                'controller' => 'group',
                'repocall'   => 'findOneByName',
                'field'      => [
                    'lthrt_userbundle_group[name]',
                    'lthrt_userbundle_group[description]',
                ],
            ],
            [
                'controller' => 'role',
                'repocall'   => 'findOneByRole',
                'field'      => [
                    'lthrt_userbundle_role[role]',
                    'lthrt_userbundle_role[description]',
                ],
            ],
            [
                'controller' => 'user',
                'repocall'   => 'findOneByUsername',
                'field'      => [
                    'lthrt_userbundle_user[username]',
                    'lthrt_userbundle_user[email]',
                ],
            ],
        ];
    }
}
