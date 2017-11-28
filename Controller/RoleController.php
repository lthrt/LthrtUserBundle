<?php

namespace Lthrt\UserBundle\Controller;

use Lthrt\UserBundle\Entity\Role;
use Lthrt\UserBundle\Form\RoleType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Role controller.
 *
 * @Route("role")
 */
class RoleController extends Controller
{

    /**
     * Deletes a role entity.
     *
     * @Route("/{role}", name="role_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Role $role)
    {
        $form = $this->createDeleteForm($role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($role);
            $em->flush();
        }

        return $this->redirectToRoute('role_index');
    }

    /**
     * Displays a form to edit an existing role entity.
     *
     * @Route("/{role}/edit", name="role_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Role $role)
    {
        $deleteForm = $this->createDeleteForm($role);
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('role_edit', array('role' => $role->getId()));
        }

        return $this->render('role/edit.html.twig', array(
            'role' => $role,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Lists all role entities.
     *
     * @Route("/", name="role_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $roles = $em->getRepository('LthrtUserBundle:Role')->findAll();

        return $this->render('role/index.html.twig', array(
            'roles' => $roles,
        ));
    }

    /**
     * Creates a new role entity.
     *
     * @Route("/new", name="role_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($role);
            $em->flush();

            return $this->redirectToRoute('role_show', array('role' => $role->getId()));
        }

        return $this->render('role/new.html.twig', array(
            'role' => $role,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a role entity.
     *
     * @Route("/{role}", name="role_show")
     * @Method("GET")
     */
    public function showAction(Role $role)
    {
        $deleteForm = $this->createDeleteForm($role);

        return $this->render('role/show.html.twig', array(
            'role' => $role,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to delete a role entity.
     *
     * @param Role $role The role entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Role $role)
    {
        return $this->container->get('form.factory')->createNamed(
            'lthrt_userbundle_role_delete',
            'Symfony\Component\Form\Extension\Core\Type\FormType',
            null,
            [
                'action' => $this->generateUrl('role_delete', ['role' => $role->getId()]),
                'method' => 'DELETE',
            ]
        );
    }

}
