<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ecommerce\EcommerceBundle\Entity\Produits;
use  Ecommerce\EcommerceBundle\Entity\Media;
use Ecommerce\EcommerceBundle\Form\Type\ProduitsType;
use Ecommerce\EcommerceBundle\Form\Type\ProduitsFilterType;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Produits controller.
 *
 */
class ProduitsAdminController extends Controller
{
    /**
     * Lists all Produits entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new ProduitsFilterType());
        if (!is_null($response = $this->saveFilter($form, 'produits', 'admin_produits'))) {
            return $response;
        }
        $qb = $em->getRepository('EcommerceBundle:Produits')->createQueryBuilder('p');
        $paginator = $this->filter($form, $qb, 'produits');
                return $this->render('EcommerceBundle:Produits:index.html.twig', array(
            'form'      => $form->createView(),
            'paginator' => $paginator,
        ));
    }

    /**
     * Finds and displays a Produits entity.
     *
     */
    public function showAction(Produits $produits)
    {
        $deleteForm = $this->createDeleteForm($produits->getId(), 'admin_produits_delete');

        return $this->render('EcommerceBundle:Produits:show.html.twig', array(
            'produits' => $produits,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to create a new Produits entity.
     *
     */
    public function newAction()
    {
        $produits = new Produits();
        $media = new Media();
        $produits->getImages()->add($media);





        $form = $this->createForm(new ProduitsType(), $produits);

        return $this->render('EcommerceBundle:Produits:new.html.twig', array(
            'produits' => $produits,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a new Produits entity.
     *
     */
    public function createAction(Request $request)
    {
        $produits = new Produits();
        $form = $this->createForm(new ProduitsType(), $produits);
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $images = $produits->getImages();



            foreach ($images as $image) {
              $produits->removeImage($image);
               $em->persist($image);
               $em->flush();
               $produits->addImage($image);
            }



            $em->persist($produits);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_produits_show', array('id' => $produits->getId())));
        }

        return $this->render('EcommerceBundle:Produits:new.html.twig', array(
            'produits' => $produits,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Produits entity.
     *
     */
    public function editAction(Produits $produits)
    {
        // $editForm = $this->createForm(new ProduitsType(), $produits, array(
        //     'action' => $this->generateUrl('admin_produits_update', array('id' => $produits->getId())),
        //     'method' => 'PUT',
        // ));
        $editForm = $this->createForm(new ProduitsType(), $produits );

        $deleteForm = $this->createDeleteForm($produits->getId(), 'admin_produits_delete');

        return $this->render('EcommerceBundle:Produits:edit.html.twig', array(
            'produits' => $produits,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Produits entity.
     *
     */
    public function updateAction(Produits $produits, Request $request)
    {
        $editForm = $this->createForm(new ProduitsType(), $produits, array(
            'action' => $this->generateUrl('admin_produits_update', array('id' => $produits->getId())),
            'method' => 'PUT',
        ));
        if ($editForm->handleRequest($request)->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->generateUrl('admin_produits_edit', array('id' => $produits->getId())));
        }
        $deleteForm = $this->createDeleteForm($produits->getId(), 'admin_produits_delete');

        return $this->render('EcommerceBundle:Produits:edit.html.twig', array(
            'produits' => $produits,
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
        $this->setOrder('produits', $field, $type);

        return $this->redirect($this->generateUrl('admin_produits'));
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
     * Deletes a Produits entity.
     *
     */
    public function deleteAction(Produits $produits, Request $request)
    {
        $form = $this->createDeleteForm($produits->getId(), 'admin_produits_delete');
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($produits);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_produits'));
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
