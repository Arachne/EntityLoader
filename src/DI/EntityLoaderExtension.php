<?php

declare(strict_types=1);

namespace Arachne\EntityLoader\DI;

use Arachne\EntityLoader\Application\FilterRequestParametersSubscriber;
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
use Arachne\EntityLoader\Routing\RouterWrapper;
use Arachne\ServiceCollections\DI\ServiceCollectionsExtension;
use Nette\Application\IRouter;
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

    /**
     * @var mixed[]
     */
    public $defaults = [
        'envelopes' => false,
    ];

    /**
     * @var string[]
     */
    private $filters = [
        ArrayFilterIn::class => 'array',
        BooleanFilterIn::class => 'bool',
        FloatFilterIn::class => 'float',
        IntegerFilterIn::class => 'int',
        MixedFilterIn::class => 'mixed',
        StringFilterIn::class => 'string',
    ];

    public function loadConfiguration(): void
    {
        $this->validateConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        /** @var ServiceCollectionsExtension $serviceCollectionsExtension */
        $serviceCollectionsExtension = $this->getExtension(ServiceCollectionsExtension::class);

        $filterInIterator = $serviceCollectionsExtension->getCollection(
            ServiceCollectionsExtension::TYPE_ITERATOR,
            self::TAG_FILTER_IN,
            FilterInInterface::class
        );

        $filterOutIterator = $serviceCollectionsExtension->getCollection(
            ServiceCollectionsExtension::TYPE_ITERATOR,
            self::TAG_FILTER_OUT,
            FilterOutInterface::class
        );

        foreach ($this->filters as $class => $type) {
            $builder->addDefinition($this->prefix('filterIn.'.$type))
                ->setType($class)
                ->addTag(self::TAG_FILTER_IN);
        }

        $builder->addDefinition($this->prefix('entityLoader'))
            ->setType(EntityLoader::class)
            ->setArguments(
                [
                    'filterInIterator' => '@'.$filterInIterator,
                ]
            );

        $builder->addDefinition($this->prefix('entityUnloader'))
            ->setType(EntityUnloader::class)
            ->setArguments(
                [
                    'filterOutIterator' => '@'.$filterOutIterator,
                ]
            );

        $builder->addDefinition($this->prefix('application.parameterFinder'))
            ->setType(ParameterFinder::class);

        $builder->addDefinition($this->prefix('application.requestEntityLoader'))
            ->setType(RequestEntityLoader::class);

        $builder->addDefinition($this->prefix('application.requestEntityUnloader'))
            ->setType(RequestEntityUnloader::class);

        $builder->addDefinition($this->prefix('application.filterRequestParametersSubscriber'))
            ->setType(FilterRequestParametersSubscriber::class);
    }

    public function beforeCompile(): void
    {
        $builder = $this->getContainerBuilder();

        $router = $builder->getByType(IRouter::class);

        if ($router !== null) {
            $routerDefinition = $builder->getDefinition($router);

            if ($routerDefinition->getClass() !== RouterWrapper::class) {
                $routerDefinition->setAutowired(false);

                $builder->addDefinition($this->prefix('router'))
                    ->setType(RouterWrapper::class)
                    ->setArguments(
                        [
                            'router' => '@'.$router,
                            'envelopes' => $this->config['envelopes'],
                        ]
                    );
            }
        }
    }

    private function getExtension(string $class): CompilerExtension
    {
        $extensions = $this->compiler->getExtensions($class);

        if ($extensions === []) {
            throw new AssertionException(
                sprintf('Extension "%s" requires "%s" to be installed.', get_class($this), $class)
            );
        }

        return reset($extensions);
    }
}
