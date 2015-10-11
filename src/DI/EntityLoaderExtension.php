<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\DI;

use Arachne\DIHelpers\CompilerExtension;
use Kdyby\Events\DI\EventsExtension;
use Nette\Utils\AssertionException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoaderExtension extends CompilerExtension
{

	const TAG_FILTER_IN = 'arachne.entityLoader.filterIn';
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

		if ($extension = $this->getExtension('Arachne\DIHelpers\DI\ResolversExtension', false)) {
			$extension->add(self::TAG_FILTER_IN, 'Arachne\EntityLoader\FilterInInterface');
			$extension->add(self::TAG_FILTER_OUT, 'Arachne\EntityLoader\FilterOutInterface');
		} elseif ($extension = $this->getExtension('Arachne\DIHelpers\DI\DIHelpersExtension', false)) {
			$extension->addResolver(self::TAG_FILTER_IN, 'Arachne\EntityLoader\FilterInInterface');
			$extension->addResolver(self::TAG_FILTER_OUT, 'Arachne\EntityLoader\FilterOutInterface');
		} else {
			throw new AssertionException('Cannot add resolvers because arachne/di-helpers is not properly installed.');
		}

		foreach ($this->filters as $class => $type) {
			$builder->addDefinition($this->prefix('filterIn.' . $type))
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

		$builder->addDefinition($this->prefix('application.applicationListener'))
			->setClass('Arachne\EntityLoader\Application\ApplicationListener')
			->addTag(EventsExtension::TAG_SUBSCRIBER);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		if ($extension = $this->getExtension('Arachne\DIHelpers\DI\ResolversExtension', false)) {
			$filterInResolver = $extension->get(self::TAG_FILTER_IN);
			$filterOutResolver = $extension->get(self::TAG_FILTER_OUT);
		} elseif ($extension = $this->getExtension('Arachne\DIHelpers\DI\DIHelpersExtension', false)) {
			$filterInResolver = $extension->getResolver(self::TAG_FILTER_IN);
			$filterOutResolver = $extension->getResolver(self::TAG_FILTER_OUT);
		}

		$builder->getDefinition($this->prefix('entityLoader'))
			->setArguments([
				'filterInResolver' => '@' . $filterInResolver,
			]);

		$builder->getDefinition($this->prefix('entityUnloader'))
			->setArguments([
				'filterOutResolver' => '@' . $filterOutResolver,
			]);
	}

}
