<?php

declare(strict_types=1);

namespace PrestaShop\Module\ProductLabel\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\DeleteActionTrait;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductLabelGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    use DeleteActionTrait;

    const GRID_ID = 'product_label';

    protected function getName()
    {
        return 'Product label';
    }

    public function getId(): string
    {
        return self::GRID_ID;
    }

    protected function getColumns(): ColumnCollection
    {
        return (new ColumnCollection())
            ->add((new DataColumn('id'))->setName('ID')->setOptions(['field' => 'id']))
            ->add((new DataColumn('name'))->setName('Name')->setOptions(['field' => 'name']))
            ->add((new DataColumn('color'))->setName('Color')->setOptions(['field' => 'color']))
            ->add((new DataColumn('visible'))->setName('Visible')->setOptions(['field' => 'visible']))
            ->add(
                (new ActionColumn('actions'))
                    ->setName($this->trans('Actions', [], 'Admin.Global'))
                    ->setOptions([
                        'actions' => (new RowActionCollection())
                            ->add(
                                (new LinkRowAction('edit'))
                                    ->setName('Edit')
                                    ->setIcon('edit')
                                    ->setOptions([
                                        'route' => 'admin_product_label_edit',
                                        'route_param_name' => 'id',
                                        'route_param_field' => 'id',
                                    ])
                            )
                            ->add($this->buildDeleteAction('admin_product_label_delete', 'id', 'id'))
                    ])
            );
    }

    protected function getFilters(): FilterCollection
    {
        return (new FilterCollection())
            ->add((new Filter('name', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'style' => 'width: 80%'
                            
                        ],
                    ])
                    ->setAssociatedColumn('name')
            )->add((new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'product_label_admin',
                    ])
                    ->setAssociatedColumn('actions')
            );
    }

    protected function getGridActions(): GridActionCollection
    {
        return new GridActionCollection();
    }
}
