<?php
namespace Pages\PagesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pages\PagesBundle\Entity\Pages;
use Pages\PagesBundle\Form\Type\PagesType;
use Pages\PagesBundle\Form\Type\PagesFilterType;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Pages controller.
 *
 */
class PagesAdminController extends Controller
{
    /**
     * Lists all Pages entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new PagesFilterType());
        if (!is_null($response = $this->saveFilter($form, 'pages', 'admin_pages'))) {
            return $response;
        }
        $qb = $em->getRepository('PagesBundle:Pages')->createQueryBuilder('p');
        $paginator = $this->filter($form, $qb, 'pages');
                return $this->render('PagesBundle:Pages:index.html.twig', array(
            'form'      => $form->createView(),
            'paginator' => $paginator,
        ));
    }

    /**
     * Finds and displays a Pages entity.
     *
     */
    public function showAction(Pages $pages)
    {
        $deleteForm = $this->createDeleteForm($pages->getId(), 'admin_pages_delete');

        return $this->render('PagesBundle:Pages:show.html.twig', array(
            'pages' => $pages,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to create a new Pages entity.
     *
     */
    public function newAction()
    {
        $pages = new Pages();
        $form = $this->createForm(new PagesType(), $pages);

        return $this->render('PagesBundle:Pages:new.html.twig', array(
            'pages' => $pages,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a new Pages entity.
     *
     */
    public function createAction(Request $request)
    {
        $pages = new Pages();
        $form = $this->createForm(new PagesType(), $pages);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($pages);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_pages_show', array('id' => $pages->getId())));
        }

        return $this->render('PagesBundle:Pages:new.html.twig', array(
            'pages' => $pages,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Pages entity.
     *
     */
    public function editAction(Pages $pages)
    {
        $editForm = $this->createForm(new PagesType(), $pages, array(
            'action' => $this->generateUrl('admin_pages_update', array('id' => $pages->getId())),
            'method' => 'PUT',
        ));
        $deleteForm = $this->createDeleteForm($pages->getId(), 'admin_pages_delete');

        return $this->render('PagesBundle:Pages:edit.html.twig', array(
            'pages' => $pages,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Pages entity.
     *
     */
    public function updateAction(Pages $pages, Request $request)
    {
        $editForm = $this->createForm(new PagesType(), $pages, array(
            'action' => $this->generateUrl('admin_pages_update', array('id' => $pages->getId())),
            'method' => 'PUT',
        ));
        if ($editForm->handleRequest($request)->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->generateUrl('admin_pages_edit', array('id' => $pages->getId())));
        }
        $deleteForm = $this->createDeleteForm($pages->getId(), 'admin_pages_delete');

        return $this->render('PagesBundle:Pages:edit.html.twig', array(
            'pages' => $pages,
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
        $this->setOrder('pages', $field, $type);

        return $this->redirect($this->generateUrl('admin_pages'));
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
     * Deletes a Pages entity.
     *
     */
    public function deleteAction(Pages $pages, Request $request)
    {
        $form = $this->createDeleteForm($pages->getId(), 'admin_pages_delete');
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($pages);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_pages'));
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
