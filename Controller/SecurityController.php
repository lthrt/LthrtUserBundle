<?php

namespace Lthrt\UserBundle\Controller;

use Lthrt\UserBundle\Entity\User;
use Lthrt\UserBundle\Form\RegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */

    public function loginAction(
        Request             $request,
        AuthenticationUtils $authUtils
    ) {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction(
        Request                      $request,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password       = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->salt     = sha1(random_int(1, 999999) . md5(random_int(1, 999999)) . random_int(1, 999999));

            if (!$user->username) {
                $user->username = $user->email;
            }

            $role = $this->getDoctrine()
                ->getManager()
                ->getRepository('LthrtUserBundle:Role')
                ->findOneByRole('ROLE_USER');
            $user->addRole($role);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'security/register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
