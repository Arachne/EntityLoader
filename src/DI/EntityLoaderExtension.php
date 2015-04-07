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

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EntityLoaderExtension extends CompilerExtension
{

	const TAG_FILTER_IN = 'arachne.entityLoader.filterIn';

	private static $filters = [
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

		$filterInResolver = $this->getExtension('Arachne\DIHelpers\DI\DIHelpersExtension')
			->addResolver(self::TAG_FILTER_IN, 'Arachne\EntityLoader\FilterInInterface');

		foreach (self::$filters as $class => $type) {
			$builder->addDefinition($this->prefix('filterIn.' . $type))
				->setClass($class)
				->addTag(self::TAG_FILTER_IN, $type);
		}

		$builder->addDefinition($this->prefix('entityLoader'))
			->setClass('Arachne\EntityLoader\EntityLoader')
			->setArguments([
				'filterInResolver' => '@' . $filterInResolver,
			]);

		$builder->addDefinition($this->prefix('application.parameterFinder'))
			->setClass('Arachne\EntityLoader\Application\ParameterFinder');

		$builder->addDefinition($this->prefix('application.requestEntityLoader'))
			->setClass('Arachne\EntityLoader\Application\RequestEntityLoader');

		$builder->addDefinition($this->prefix('application.requestEntityLoaderListener'))
			->setClass('Arachne\EntityLoader\Application\RequestEntityLoaderListener')
			->addTag(EventsExtension::TAG_SUBSCRIBER);
	}

}
