<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\DI;

use Nette\DI\CompilerExtension;

/**
 * @author Jáchym Toušek
 */
class EntityLoaderExtension extends CompilerExtension
{

	const TAG_CONVERTER = 'arachne.entityLoader.converter';

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('parameterFinder'))
			->setClass('Arachne\EntityLoader\ParameterFinder');

		$builder->addDefinition($this->prefix('converterLoader'))
			->setClass('Arachne\EntityLoader\IConverterLoader')
			->setFactory('Arachne\EntityLoader\ServiceConverterLoader');

		$builder->addDefinition($this->prefix('loader'))
			->setClass('Arachne\EntityLoader\EntityLoader');
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$services = [];
		foreach ($builder->findByTag(self::TAG_CONVERTER) as $name => $types) {
			foreach ((array) $types as $type) {
				$services[$type] = $name;
			}
		}

		$builder->getDefinition($this->prefix('converterLoader'))
			->setArguments([ $services ]);
	}

}
