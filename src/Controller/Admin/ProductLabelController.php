<?php

declare(strict_types=1);

namespace PrestaShop\Module\ProductLabel\Controller\Admin;

use PrestaShop\Module\ProductLabel\Entity\ProductLabel;
use PrestaShop\Module\ProductLabel\Form\ProductLabelType;
use PrestaShop\Module\ProductLabel\Grid\Definition\Factory\ProductLabelGridDefinitionFactory;
use PrestaShop\Module\ProductLabel\Grid\Filter\LabelFilter;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\GridDefinitionFactoryInterface;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PrestaShop\PrestaShop\Core\Grid\GridFactoryInterface;
use PrestaShopBundle\Service\Grid\ResponseBuilder;

class ProductLabelController extends FrameworkBundleAdminController
{
    public function index(LabelFilter $labelFilter, Request $request): Response
    {   
        if ($request->isMethod('POST')) {
            return $this->forward(
                'PrestaShop\Module\ProductLabel\Controller\Admin\ProductLabelController::searchGrid'
            );
        }
       
        /** @var GridFactoryInterface $gridFactory */
        $gridFactory = $this->get('productlabel.grid.product_label_grid_factory');
        $grid = $gridFactory->getGrid($labelFilter);
        $gridView = $this->presentGrid($grid);

        $toolbarButtons = [
            'add' => [
                'href' => $this->generateUrl('admin_product_label_create'),
                'desc' => $this->trans('Add new label', 'Admin.Actions', []),
                'icon' => 'add_circle',
            ],
        ];


        return $this->render('@Modules/productlabel/views/templates/admin/label/index.html.twig', [
            'grid' => $gridView,
            'layoutHeaderToolbarBtn' => $toolbarButtons
        ]);
    }

    public function searchGrid(Request $request)
    {
        /** @var GridDefinitionFactoryInterface $definitionFactory */
        $definitionFactory = $this->get('productlabel.grid.product_label_definition_factory');

        $filterId = ProductLabelGridDefinitionFactory::GRID_ID;

        /** @var ResponseBuilder $responseBuilder */
        $responseBuilder = $this->get('prestashop.bundle.grid.response_builder');

        $response = $responseBuilder->buildSearchResponse(
            $definitionFactory,
            $request,
            $filterId,
            'product_label_admin'
        );

        return $response;
    }

    public function edit(Request $request, ?int $id = null): Response
    {
        $em = $this->getDoctrine()->getManager();

        $label = $id
            ? $em->getRepository(ProductLabel::class)->find($id)
            : new ProductLabel();

        $form = $this->createForm(ProductLabelType::class, $label);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($label);
            $em->flush();
    
            $this->addFlash('success', $this->trans('Label saved.', 'Admin.Notifications.Success'));
    
            return $this->redirectToRoute('admin_product_label_edit', ['id' => $label->getId()]);
        }
    
        return $this->render('@Modules/productlabel/views/templates/admin/label/edit.html.twig', [
            'label' => $label,
            'form' => $form->createView()
        ]);
    }

    public function delete(ProductLabel $productLabel): Response
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($productLabel);
        $em->flush();

        $this->addFlash('success', $this->trans('Label deleted successfully.', 'Admin.Notifications.Success'));

        return $this->redirectToRoute('product_label_admin');
    }
}
