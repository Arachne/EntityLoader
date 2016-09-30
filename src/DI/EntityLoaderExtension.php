<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\DI;

use Arachne\DIHelpers\CompilerExtension;
use Arachne\DIHelpers\DI\ResolversExtension;
use Arachne\EntityLoader\Application\ApplicationSubscriber;
use Arachne\EntityLoader\Application\ParameterFinder;
use Arachne\EntityLoader\Application\RequestEntityLoader;
use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Arachne\EntityLoader\EntityLoader;
use Arachne\EntityLoader\EntityUnloader;
use Arachne\EntityLoader\FilterIn\ArrayFilterIn;
use Arachne\EntityLoader\FilterIn\BooleanFilterIn;
use Arachne\EntityLoader\FilterIn\FloatFilterIn;
use Arachne\EntityLoader\FilterIn\IntegerFilterIn;
use Arachne\EntityLoader\FilterIn\MixedFilterIn;
use Arachne\EntityLoader\FilterIn\StringFilterIn;
use Arachne\EntityLoader\FilterInInterface;
use Arachne\EntityLoader\FilterOutInterface;
use Arachne\EventDispatcher\DI\EventDispatcherExtension;

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
        ArrayFilterIn::class => 'array',
        BooleanFilterIn::class => 'bool',
        FloatFilterIn::class => 'float',
        IntegerFilterIn::class => 'int',
        MixedFilterIn::class => 'mixed',
        StringFilterIn::class => 'string',
    ];

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        // EventDispatcherExtension is required.
        $this->getExtension(EventDispatcherExtension::class);

        $resolvers = $this->getExtension(ResolversExtension::class);
        $resolvers->add(self::TAG_FILTER_IN, FilterInInterface::class);
        $resolvers->add(self::TAG_FILTER_OUT, FilterOutInterface::class);

        foreach ($this->filters as $class => $type) {
            $builder->addDefinition($this->prefix('filterIn.'.$type))
                ->setClass($class)
                ->addTag(self::TAG_FILTER_IN, $type);
        }

        $builder->addDefinition($this->prefix('entityLoader'))
            ->setClass(EntityLoader::class);

        $builder->addDefinition($this->prefix('entityUnloader'))
            ->setClass(EntityUnloader::class);

        $builder->addDefinition($this->prefix('application.parameterFinder'))
            ->setClass(ParameterFinder::class);

        $builder->addDefinition($this->prefix('application.requestEntityLoader'))
            ->setClass(RequestEntityLoader::class);

        $builder->addDefinition($this->prefix('application.requestEntityUnloader'))
            ->setClass(RequestEntityUnloader::class);

        $builder->addDefinition($this->prefix('application.applicationSubscriber'))
            ->setClass(ApplicationSubscriber::class)
            ->addTag(EventDispatcherExtension::TAG_SUBSCRIBER);
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        $resolvers = $this->getExtension(ResolversExtension::class);

        $builder->getDefinition($this->prefix('entityLoader'))
            ->setArguments(
                [
                    'filterInResolver' => '@'.$resolvers->get(self::TAG_FILTER_IN),
                ]
            );

        $builder->getDefinition($this->prefix('entityUnloader'))
            ->setArguments(
                [
                    'filterOutResolver' => '@'.$resolvers->get(self::TAG_FILTER_OUT),
                ]
            );
    }
}
