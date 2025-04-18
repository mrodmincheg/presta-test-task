<?php

declare(strict_types=1);

namespace PrestaShop\Module\ProductLabel\Form;

use PrestaShop\PrestaShop\Core\Form\ConfigurableFormChoiceProviderInterface;
use PrestaShop\Module\ProductLabel\Entity\ProductLabel;

class LabelChoice implements ConfigurableFormChoiceProviderInterface
{
    public function getChoices(array $options)
    {
        $data = [];

        /** @var ProductLabel[] $options */
        foreach ($options as $label) {
            $data[$label->getName()] = $label->getId();
        }
        return $data;
    }
}
