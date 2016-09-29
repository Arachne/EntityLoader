<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\DI;

use Arachne\EventDispatcher\DI\EventDispatcherExtension;
use Arachne\ServiceCollections\DI\ServiceCollectionsExtension;
use Nette\DI\CompilerExtension;
use Nette\Utils\AssertionException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoaderExtension extends CompilerExtension
{
    /**
     * EntityLoader uses filters with this tag to convert http parameters to application parameters.
     * The classes handled by the filter should be passed as the tag attributes.
     */
    const TAG_FILTER_IN = 'arachne.entityLoader.filterIn';

    /**
     * EntityUnloader uses filters with this tag to convert application parameters to http parameters.
     * The classes handled by the filter should be passed as the tag attributes.
     */
    const TAG_FILTER_OUT = 'arachne.entityLoader.filterOut';

    private $filters = [
        'Arachne\EntityLoader\FilterIn\ArrayFilterIn' => 'array',
        'Arachne\EntityLoader\FilterIn\BooleanFilterIn' => 'bool',
        'Arachne\EntityLoader\FilterIn\FloatFilterIn' => 'float',
        'Arachne\EntityLoader\FilterIn\IntegerFilterIn' => 'int',
        'Arachne\EntityLoader\FilterIn\MixedFilterIn' => 'mixed',
        'Arachne\EntityLoader\FilterIn\StringFilterIn' => 'string',
    ];

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        /* @var $serviceCollectionsExtension ServiceCollectionsExtension */
        $serviceCollectionsExtension = $this->getExtension('Arachne\ServiceCollections\DI\ServiceCollectionsExtension');

        $filterInResolver = $serviceCollectionsExtension->getCollection(
            ServiceCollectionsExtension::TYPE_RESOLVER,
            self::TAG_FILTER_IN,
            'Arachne\EntityLoader\FilterInInterface'
        );

        $filterOutResolver = $serviceCollectionsExtension->getCollection(
            ServiceCollectionsExtension::TYPE_RESOLVER,
            self::TAG_FILTER_OUT,
            'Arachne\EntityLoader\FilterOutInterface'
        );

        foreach ($this->filters as $class => $type) {
            $builder->addDefinition($this->prefix('filterIn.'.$type))
                ->setClass($class)
                ->addTag(self::TAG_FILTER_IN, $type);
        }

        $builder->addDefinition($this->prefix('entityLoader'))
            ->setClass('Arachne\EntityLoader\EntityLoader')
            ->setArguments(
                [
                    'filterInResolver' => '@'.$filterInResolver,
                ]
            );

        $builder->addDefinition($this->prefix('entityUnloader'))
            ->setClass('Arachne\EntityLoader\EntityUnloader')
            ->setArguments(
                [
                    'filterOutResolver' => '@'.$filterOutResolver,
                ]
            );

        $builder->addDefinition($this->prefix('application.parameterFinder'))
            ->setClass('Arachne\EntityLoader\Application\ParameterFinder');

        $builder->addDefinition($this->prefix('application.requestEntityLoader'))
            ->setClass('Arachne\EntityLoader\Application\RequestEntityLoader');

        $builder->addDefinition($this->prefix('application.requestEntityUnloader'))
            ->setClass('Arachne\EntityLoader\Application\RequestEntityUnloader');

        $builder->addDefinition($this->prefix('application.applicationSubscriber'))
            ->setClass('Arachne\EntityLoader\Application\ApplicationSubscriber')
            ->addTag(EventDispatcherExtension::TAG_SUBSCRIBER);
    }

    /**
     * @param string $class
     *
     * @return CompilerExtension
     */
    private function getExtension($class)
    {
        $extensions = $this->compiler->getExtensions($class);

        if (!$extensions) {
            throw new AssertionException(
                sprintf('Extension "%s" requires "%s" to be installed.', get_class($this), $class)
            );
        }

        return reset($extensions);
    }
}
