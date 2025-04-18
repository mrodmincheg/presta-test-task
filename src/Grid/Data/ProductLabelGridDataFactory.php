<?php

declare(strict_types=1);

namespace PrestaShop\Module\ProductLabel\Grid\Data;

use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\Module\ProductLabel\Entity\ProductLabel;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\Module\ProductLabel\Repository\ProductLabelRepository;


class ProductLabelGridDataFactory implements GridDataFactoryInterface
{
    private ProductLabelRepository $productLabelRepository;

    public function __construct(ProductLabelRepository $productLabelRepository)
    {
        $this->productLabelRepository = $productLabelRepository;
    }

    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ProductLabel[] $labels */
        $labels = $this->productLabelRepository->findForGrid($searchCriteria);
        $all = $this->productLabelRepository->countAll($searchCriteria);

        $records = [];
        foreach ($labels as $label) {
            $records[] = [
                'id' => $label->getId(),
                'name' => $label->getName(),
                'color' => $label->getColor(),
                'visible' => $label->getVisible() ? 'Yes' : 'No',
            ];
        }

        return new GridData(new RecordCollection($records), $all);
    }
}
