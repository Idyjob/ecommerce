<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ecommerce\EcommerceBundle\Entity\Tva;
use Ecommerce\EcommerceBundle\Form\Type\TvaType;
use Ecommerce\EcommerceBundle\Form\Type\TvaFilterType;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Tva controller.
 *
 */
class TvaAdminController extends Controller
{
    /**
     * Lists all Tva entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new TvaFilterType());
        if (!is_null($response = $this->saveFilter($form, 'tva', 'admin_tva'))) {
            return $response;
        }
        $qb = $em->getRepository('EcommerceBundle:Tva')->createQueryBuilder('t');
        $paginator = $this->filter($form, $qb, 'tva');
                return $this->render('EcommerceBundle:Tva:index.html.twig', array(
            'form'      => $form->createView(),
            'paginator' => $paginator,
        ));
    }

    /**
     * Finds and displays a Tva entity.
     *
     */
    public function showAction(Tva $tva)
    {
        $deleteForm = $this->createDeleteForm($tva->getId(), 'admin_tva_delete');

        return $this->render('EcommerceBundle:Tva:show.html.twig', array(
            'tva' => $tva,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to create a new Tva entity.
     *
     */
    public function newAction()
    {
        $tva = new Tva();
        $form = $this->createForm(new TvaType(), $tva);

        return $this->render('EcommerceBundle:Tva:new.html.twig', array(
            'tva' => $tva,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a new Tva entity.
     *
     */
    public function createAction(Request $request)
    {
        $tva = new Tva();
        $form = $this->createForm(new TvaType(), $tva);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tva);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_tva_show', array('id' => $tva->getId())));
        }

        return $this->render('EcommerceBundle:Tva:new.html.twig', array(
            'tva' => $tva,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Tva entity.
     *
     */
    public function editAction(Tva $tva)
    {
        $editForm = $this->createForm(new TvaType(), $tva, array(
            'action' => $this->generateUrl('admin_tva_update', array('id' => $tva->getId())),
            'method' => 'PUT',
        ));
        $deleteForm = $this->createDeleteForm($tva->getId(), 'admin_tva_delete');

        return $this->render('EcommerceBundle:Tva:edit.html.twig', array(
            'tva' => $tva,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Tva entity.
     *
     */
    public function updateAction(Tva $tva, Request $request)
    {
        $editForm = $this->createForm(new TvaType(), $tva, array(
            'action' => $this->generateUrl('admin_tva_update', array('id' => $tva->getId())),
            'method' => 'PUT',
        ));
        if ($editForm->handleRequest($request)->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->generateUrl('admin_tva_edit', array('id' => $tva->getId())));
        }
        $deleteForm = $this->createDeleteForm($tva->getId(), 'admin_tva_delete');

        return $this->render('EcommerceBundle:Tva:edit.html.twig', array(
            'tva' => $tva,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * Save order.
     *
     */
    public function sortAction($field, $type)
    {
        $this->setOrder('tva', $field, $type);

        return $this->redirect($this->generateUrl('admin_tva'));
    }

    /**
     * @param string $name  session name
     * @param string $field field name
     * @param string $type  sort type ("ASC"/"DESC")
     */
    protected function setOrder($name, $field, $type = 'ASC')
    {
        $this->getRequest()->getSession()->set('sort.' . $name, array('field' => $field, 'type' => $type));
    }

    /**
     * @param  string $name
     * @return array
     */
    protected function getOrder($name)
    {
        $session = $this->getRequest()->getSession();

        return $session->has('sort.' . $name) ? $session->get('sort.' . $name) : null;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $name
     */
    protected function addQueryBuilderSort(QueryBuilder $qb, $name)
    {
        $alias = current($qb->getDQLPart('from'))->getAlias();
        if (is_array($order = $this->getOrder($name))) {
            $qb->orderBy($alias . '.' . $order['field'], $order['type']);
        }
    }

    /**
     * Save filters
     *
     * @param  FormInterface $form
     * @param  string        $name   route/entity name
     * @param  string        $route  route name, if different from entity name
     * @param  array         $params possible route parameters
     * @return Response
     */
    protected function saveFilter(FormInterface $form, $name, $route = null, array $params = null)
    {
        $request = $this->getRequest();
        $url = $this->generateUrl($route ?: $name, is_null($params) ? array() : $params);
        if ($request->query->has('submit-filter') && $form->handleRequest($request)->isValid()) {
            $request->getSession()->set('filter.' . $name, $request->query->get($form->getName()));

            return $this->redirect($url);
        } elseif ($request->query->has('reset-filter')) {
            $request->getSession()->set('filter.' . $name, null);

            return $this->redirect($url);
        }
    }

    /**
     * Filter form
     *
     * @param  FormInterface                                       $form
     * @param  QueryBuilder                                        $qb
     * @param  string                                              $name
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    protected function filter(FormInterface $form, QueryBuilder $qb, $name)
    {
        if (!is_null($values = $this->getFilter($name))) {
            if ($form->submit($values)->isValid()) {
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);
            }
        }

        // possible sorting
        $this->addQueryBuilderSort($qb, $name);
        return $this->get('knp_paginator')->paginate($qb, $this->getRequest()->query->get('page', 1), 20);
    }

    /**
     * Get filters from session
     *
     * @param  string $name
     * @return array
     */
    protected function getFilter($name)
    {
        return $this->getRequest()->getSession()->get('filter.' . $name);
    }

    /**
     * Deletes a Tva entity.
     *
     */
    public function deleteAction(Tva $tva, Request $request)
    {
        $form = $this->createDeleteForm($tva->getId(), 'admin_tva_delete');
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tva);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_tva'));
    }

    /**
     * Create Delete form
     *
     * @param integer                       $id
     * @param string                        $route
     * @return \Symfony\Component\Form\Form
     */
    protected function createDeleteForm($id, $route)
    {
        return $this->createFormBuilder(null, array('attr' => array('id' => 'delete')))
            ->setAction($this->generateUrl($route, array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

}
