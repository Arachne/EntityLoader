<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\DI;

use Arachne\DIHelpers\DI\DIHelpersExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;

/**
 * @author Jáchym Toušek
 */
class EntityLoaderExtension extends CompilerExtension
{

	const TAG_CONVERTER = 'arachne.entityLoader.converter';

	private static $converters = [
		'Arachne\EntityLoader\Converter\ArrayConverter' => 'array',
		'Arachne\EntityLoader\Converter\BooleanConverter' => 'bool',
		'Arachne\EntityLoader\Converter\FloatConverter' => 'float',
		'Arachne\EntityLoader\Converter\IntegerConverter' => 'int',
		'Arachne\EntityLoader\Converter\MixedConverter' => 'mixed',
		'Arachne\EntityLoader\Converter\StringConverter' => 'string',
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		foreach (self::$converters as $class => $type) {
			$builder->addDefinition($this->prefix('converter.' . $type))
				->setClass($class)
				->addTag(self::TAG_CONVERTER, $type);
		}

		$builder->addDefinition($this->prefix('converterResolver'))
			->addTag(DIHelpersExtension::TAG_RESOLVER, self::TAG_CONVERTER)
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('entityLoader'))
			->setClass('Arachne\EntityLoader\EntityLoader')
			->setArguments([
				'converterResolver' => $this->prefix('@converterResolver'),
			]);

		$builder->addDefinition($this->prefix('application.parameterFinder'))
			->setClass('Arachne\EntityLoader\Application\ParameterFinder');

		$builder->addDefinition($this->prefix('application.requestEntityLoader'))
			->setClass('Arachne\EntityLoader\Application\RequestEntityLoader');
	}

}
