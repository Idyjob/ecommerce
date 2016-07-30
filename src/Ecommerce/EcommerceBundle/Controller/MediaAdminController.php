<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ecommerce\EcommerceBundle\Entity\Media;
use Ecommerce\EcommerceBundle\Form\Type\MediaType;
use Ecommerce\EcommerceBundle\Form\Type\MediaFilterType;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Media controller.
 *
 */
class MediaAdminController extends Controller
{
    /**
     * Lists all Media entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new MediaFilterType());
        if (!is_null($response = $this->saveFilter($form, 'media', 'admin_medias'))) {
            return $response;
        }
        $qb = $em->getRepository('EcommerceBundle:Media')->createQueryBuilder('m');
        $paginator = $this->filter($form, $qb, 'media');
                return $this->render('EcommerceBundle:Media:index.html.twig', array(
            'form'      => $form->createView(),
            'paginator' => $paginator,
        ));
    }

    /**
     * Finds and displays a Media entity.
     *
     */
    public function showAction(Media $media)
    {
        $deleteForm = $this->createDeleteForm($media->getId(), 'admin_medias_delete');

        return $this->render('EcommerceBundle:Media:show.html.twig', array(
            'media' => $media,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to create a new Media entity.
     *
     */
    public function newAction()
    {
        $media = new Media();
        $form = $this->createForm(new MediaType(), $media);

        return $this->render('EcommerceBundle:Media:new.html.twig', array(
            'media' => $media,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a new Media entity.
     *
     */
    public function createAction(Request $request)
    {
        $media = new Media();
        $form = $this->createForm(new MediaType(), $media);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($media);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_medias_show', array('id' => $media->getId())));
        }

        return $this->render('EcommerceBundle:Media:new.html.twig', array(
            'media' => $media,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Media entity.
     *
     */
    public function editAction(Media $media)
    {
        $editForm = $this->createForm(new MediaType(), $media, array(
            'action' => $this->generateUrl('admin_medias_update', array('id' => $media->getId())),
            'method' => 'PUT',
        ));
        $deleteForm = $this->createDeleteForm($media->getId(), 'admin_medias_delete');

        return $this->render('EcommerceBundle:Media:edit.html.twig', array(
            'media' => $media,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Media entity.
     *
     */
    public function updateAction(Media $media, Request $request)
    {
        $editForm = $this->createForm(new MediaType(), $media, array(
            'action' => $this->generateUrl('admin_medias_update', array('id' => $media->getId())),
            'method' => 'PUT',
        ));
        if ($editForm->handleRequest($request)->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->generateUrl('admin_medias_edit', array('id' => $media->getId())));
        }
        $deleteForm = $this->createDeleteForm($media->getId(), 'admin_medias_delete');

        return $this->render('EcommerceBundle:Media:edit.html.twig', array(
            'media' => $media,
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
        $this->setOrder('media', $field, $type);

        return $this->redirect($this->generateUrl('admin_medias'));
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
     * Deletes a Media entity.
     *
     */
    public function deleteAction(Media $media, Request $request)
    {
        $form = $this->createDeleteForm($media->getId(), 'admin_medias_delete');
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($media);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_medias'));
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
