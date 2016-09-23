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
use Arachne\EventDispatcher\DI\EventDispatcherExtension;
use Kdyby\Events\DI\EventsExtension;
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

        $extension = $this->getExtension('Arachne\DIHelpers\DI\ResolversExtension');
        $extension->add(self::TAG_FILTER_IN, 'Arachne\EntityLoader\FilterInInterface');
        $extension->add(self::TAG_FILTER_OUT, 'Arachne\EntityLoader\FilterOutInterface');

        foreach ($this->filters as $class => $type) {
            $builder->addDefinition($this->prefix('filterIn.'.$type))
                ->setClass($class)
                ->addTag(self::TAG_FILTER_IN, $type);
        }

        $builder->addDefinition($this->prefix('entityLoader'))
            ->setClass('Arachne\EntityLoader\EntityLoader');

        $builder->addDefinition($this->prefix('entityUnloader'))
            ->setClass('Arachne\EntityLoader\EntityUnloader');

        $builder->addDefinition($this->prefix('application.parameterFinder'))
            ->setClass('Arachne\EntityLoader\Application\ParameterFinder');

        $builder->addDefinition($this->prefix('application.requestEntityLoader'))
            ->setClass('Arachne\EntityLoader\Application\RequestEntityLoader');

        $builder->addDefinition($this->prefix('application.requestEntityUnloader'))
            ->setClass('Arachne\EntityLoader\Application\RequestEntityUnloader');

        if ($this->getExtension('Arachne\EventDispatcher\DI\EventDispatcherExtension', false)) {
            $builder->addDefinition($this->prefix('application.applicationSubscriber'))
                ->setClass('Arachne\EntityLoader\Application\ApplicationSubscriber')
                ->addTag(EventDispatcherExtension::TAG_SUBSCRIBER);
        } elseif ($this->getExtension('Kdyby\Events\DI\EventsExtension', false)) {
            $builder->addDefinition($this->prefix('application.applicationListener'))
                ->setClass('Arachne\EntityLoader\Application\ApplicationListener')
                ->addTag(EventsExtension::TAG_SUBSCRIBER);
        } else {
            throw new AssertionException('Arachne/EntityLoader requires either Arachne/EventDispatcher or Kdyby/Events to be installed.');
        }
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        $extension = $this->getExtension('Arachne\DIHelpers\DI\ResolversExtension');

        $builder->getDefinition($this->prefix('entityLoader'))
            ->setArguments(
                [
                    'filterInResolver' => '@'.$extension->get(self::TAG_FILTER_IN),
                ]
            );

        $builder->getDefinition($this->prefix('entityUnloader'))
            ->setArguments(
                [
                    'filterOutResolver' => '@'.$extension->get(self::TAG_FILTER_OUT),
                ]
            );
    }
}
