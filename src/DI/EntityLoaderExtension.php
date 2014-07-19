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

		$builder->addDefinition($this->prefix('entityLoader'))
			->setClass('Arachne\EntityLoader\EntityLoader');

		$builder->addDefinition($this->prefix('parameterFinder'))
			->setClass('Arachne\EntityLoader\Application\ParameterFinder');

		$builder->addDefinition($this->prefix('requestEntityLoader'))
			->setClass('Arachne\EntityLoader\Application\RequestEntityLoader');
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$services = [];
		foreach ($builder->findByTag(self::TAG_CONVERTER) as $name => $_) {
			$services[] = '@' . $name;
		}

		$builder->getDefinition($this->prefix('entityLoader'))
			->setArguments([ 'converters' => $services ]);
	}

}
