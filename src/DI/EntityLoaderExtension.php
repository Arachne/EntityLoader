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

/**
 * @author Jáchym Toušek <enumag@gmail.com>
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

		$converterResolver = $this->getExtension('Arachne\DIHelpers\DI\DIHelpersExtension')
			->addResolver(self::TAG_CONVERTER, 'Arachne\EntityLoader\ConverterInterface');

		$builder->addDefinition($this->prefix('entityLoader'))
			->setClass('Arachne\EntityLoader\EntityLoader')
			->setArguments([
				'converterResolver' => '@' . $converterResolver,
			]);

		$builder->addDefinition($this->prefix('application.parameterFinder'))
			->setClass('Arachne\EntityLoader\Application\ParameterFinder');

		$builder->addDefinition($this->prefix('application.requestEntityLoader'))
			->setClass('Arachne\EntityLoader\Application\RequestEntityLoader');
	}

}
