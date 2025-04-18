<?php

declare(strict_types=1);

namespace PrestaShop\Module\ProductLabel\Form\Modifier;

use PrestaShopBundle\Form\FormBuilderModifier;
use Symfony\Component\Form\FormBuilderInterface;
use PrestaShop\Module\ProductLabel\Repository\ProductLabelRepository;
use PrestaShop\PrestaShop\Core\Form\ConfigurableFormChoiceProviderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductFormModifier
{
    private FormBuilderModifier $formBuilderModifier;

    private ProductLabelRepository $productLabelRepository;

    private ConfigurableFormChoiceProviderInterface $labelChoice;

    private TranslatorInterface $translator;


    public function __construct(
        FormBuilderModifier $formBuilderModifier,
        ProductLabelRepository $productLabelRepository,
        ConfigurableFormChoiceProviderInterface $labelChoice,
        TranslatorInterface $translator
    ) {
        $this->formBuilderModifier = $formBuilderModifier;
        $this->productLabelRepository = $productLabelRepository;
        $this->labelChoice = $labelChoice;
        $this->translator = $translator;
    }


    public function modify(
        int $productId,
        FormBuilderInterface $productFormBuilder
    ): void {

        $allLabels = $this->productLabelRepository->findAll();
        $allSelected = $this->productLabelRepository->findByProductId($productId);

        $choices = $this->labelChoice->getChoices($allLabels);
        $data = $this->labelChoice->getChoices($allSelected);

        $seoTabFormBuilder = $productFormBuilder->get('description');
        $this->formBuilderModifier->addAfter(
            $seoTabFormBuilder,
            'description',
            'product_labels',
            ChoiceType::class,
            [
                'multiple' => true,
                'required' => false,
                'label' => $this->translator->trans('Product Labels', [], 'Modules.Productlabel.Admin'),
                'label_attr' => [
                    'class' => 'font-weight-bold',
                ],
                'choices' => $choices,
                'data' => $data,
                'attr' => [
                    'data-toggle' => 'select2',
                    'data-theme' => 'classic'
                ],
            ]
        );
    }
}
