<?php

namespace Lthrt\UserBundle\Controller;

use Lthrt\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

/**
 * User controller.
 *
 * @Route("user")
 */
class UserController extends Controller
{
    /**
     * Lists all user entities.
     *
     * @Route("/", name="user_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('LthrtUserBundle:User')->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Creates a new user entity.
     *
     * @Route("/new", name="user_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = new User();

        $encoder             = new BCryptPasswordEncoder(13);
        $user->salt          = sha1(random_int(1, 999999) . md5(random_int(1, 999999)) . random_int(1, 999999));
        $user->plainPassword = 'passwd';
        $user->password      = $encoder->encodePassword($user->plainPassword, $user->salt);
        if (!$user->username) {
            $user->username = $user->email;
        }

        $form = $this->createForm('Lthrt\UserBundle\Form\UserType', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_show', ['user' => $user->getId()]);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a user entity.
     *
     * @Route("/{user}", name="user_show")
     * @Method("GET")
     */
    public function showAction(User $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        return $this->render('user/show.html.twig', [
            'user'        => $user,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/{user}/edit", name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(
        Request $request,
        User    $user
    ) {
        $deleteForm = $this->createDeleteForm($user);
        $form       = $this->createForm('Lthrt\UserBundle\Form\UserType', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', ['user' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user'        => $user,
            'form'        => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a user entity.
     *
     * @Route("/{user}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(
        Request $request,
        User    $user
    ) {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * Creates a form to delete a user entity.
     *
     * @param User $user The user entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->container->get('form.factory')->createNamed(
            'lthrt_userbundle_user_delete',
            'Symfony\Component\Form\Extension\Core\Type\FormType',
            null,
            [
                'action' => $this->generateUrl('user_delete', ['user' => $user->getId()]),
                'method' => 'DELETE',
            ]
        );
    }
}
