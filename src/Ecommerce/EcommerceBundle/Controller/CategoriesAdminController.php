<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ecommerce\EcommerceBundle\Entity\Categories;
use Ecommerce\EcommerceBundle\Form\Type\CategoriesType;
use Ecommerce\EcommerceBundle\Form\Type\CategoriesFilterType;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\QueryBuilder;
 

/**
 * Categories controller.
 *
 */
class CategoriesAdminController extends Controller
{
    /**
     * Lists all Categories entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new CategoriesFilterType());
        if (!is_null($response = $this->saveFilter($form, 'categories', 'admin_categories'))) {
            return $response;
        }
        $qb = $em->getRepository('EcommerceBundle:Categories')->createQueryBuilder('c');
        $paginator = $this->filter($form, $qb, 'categories');
                return $this->render('EcommerceBundle:Categories:index.html.twig', array(
            'form'      => $form->createView(),
            'paginator' => $paginator,
        ));
    }

    /**
     * Finds and displays a Categories entity.
     *
     */
    public function showAction(Categories $categories)
    {
        $deleteForm = $this->createDeleteForm($categories->getId(), 'admin_categories_delete');

        return $this->render('EcommerceBundle:Categories:show.html.twig', array(
            'categories' => $categories,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to create a new Categories entity.
     *
     */
    public function newAction()
    {
        $categories = new Categories();
        $form = $this->createForm(new CategoriesType(), $categories);

        return $this->render('EcommerceBundle:Categories:new.html.twig', array(
            'categories' => $categories,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a new Categories entity.
     *
     */
    public function createAction(Request $request)
    {
        $categories = new Categories();
        $form = $this->createForm(new CategoriesType(), $categories);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($categories);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_categories_show', array('id' => $categories->getId())));
        }

        return $this->render('EcommerceBundle:Categories:new.html.twig', array(
            'categories' => $categories,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Categories entity.
     *
     */
    public function editAction(Categories $categories)
    {
        $editForm = $this->createForm(new CategoriesType(), $categories, array(
            'action' => $this->generateUrl('admin_categories_update', array('id' => $categories->getId())),
            'method' => 'PUT',
        ));
        $deleteForm = $this->createDeleteForm($categories->getId(), 'admin_categories_delete');

        return $this->render('EcommerceBundle:Categories:edit.html.twig', array(
            'categories' => $categories,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Categories entity.
     *
     */
    public function updateAction(Categories $categories, Request $request)
    {
        $editForm = $this->createForm(new CategoriesType(), $categories, array(
            'action' => $this->generateUrl('admin_categories_update', array('id' => $categories->getId())),
            'method' => 'PUT',
        ));
        if ($editForm->handleRequest($request)->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->generateUrl('admin_categories_edit', array('id' => $categories->getId())));
        }
        $deleteForm = $this->createDeleteForm($categories->getId(), 'admin_categories_delete');

        return $this->render('EcommerceBundle:Categories:edit.html.twig', array(
            'categories' => $categories,
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
        $this->setOrder('categories', $field, $type);

        return $this->redirect($this->generateUrl('admin_categories'));
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
     * Deletes a Categories entity.
     *
     */
    public function deleteAction(Categories $categories, Request $request)
    {
        $form = $this->createDeleteForm($categories->getId(), 'admin_categories_delete');
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($categories);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_categories'));
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
