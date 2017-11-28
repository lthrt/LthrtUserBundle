<?php

namespace Lthrt\UserBundle\Listener;

use Doctrine\Common\Persistence\ObjectManager;
use Lthrt\UserBundle\Entity\LoginData;
use Lthrt\UserBundle\Entity\User as LthrtUser;
use Lthrt\UserBundle\Services\LoginDataRotator;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $em;
    private $stack;
    private $loginDataRotator;

    public function __construct(
        ObjectManager    $em,
                         $stack,
        LoginDataRotator $loginDataRotator = null
    ) {
        $this->em               = $em;
        $this->stack            = $stack;
        $this->loginDataRotator = $loginDataRotator;
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $data           = new LoginData();
        $username       = $this->stack->getCurrentRequest()->request->get('_username');
        $data->username = $username;
        $user           = $this->em->getRepository('LthrtUserBundle:User')->findOneByUsername($username);
        if ($user) {
            $data->user = $user;
        }
        $data->ip      = $this->stack->getCurrentRequest()->getClientIp();
        $data->updated = (new \DateTime());
        $data->success = false;
        $this->em->persist($data);
        $this->em->flush();
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof LthrtUser) {
            $data           = new LoginData();
            $data->user     = $event->getAuthenticationToken()->getUser();
            $username       = $this->stack->getCurrentRequest()->request->get('_username');
            $data->username = $username;
            $data->ip       = $this->stack->getCurrentRequest()->getClientIp();
            $data->updated  = (new \DateTime());
            $data->success  = true;
            $this->em->persist($data);
            $this->em->flush();
            $this->loginDataRotator->purgeOlder($user);
        }
    }
}
