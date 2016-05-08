<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Arachne\EventDispatcher\ApplicationEvents;
use Arachne\EventDispatcher\Event\ApplicationRequestEvent;
use Nette\Application\Application;
use Nette\Application\BadRequestException;
use Nette\Application\InvalidPresenterException;
use Nette\Application\Request;
use Nette\Http\IResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationSubscriber implements EventSubscriberInterface
{
    /**
     * @var RequestEntityLoader
     */
    private $loader;

    public function __construct(RequestEntityLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ApplicationEvents::REQUEST => 'requestHandler',
        ];
    }

    /**
     * @throws BadRequestException
     */
    public function requestHandler(ApplicationRequestEvent $event)
    {
        try {
            $this->loader->filterIn($event->getRequest());
        } catch (InvalidPresenterException $e) {
            throw new BadRequestException('Request has invalid presenter.', IResponse::S404_NOT_FOUND, $e);
        } catch (UnexpectedValueException $e) {
            throw new BadRequestException('Request has invalid parameter.', IResponse::S404_NOT_FOUND, $e);
        }
    }
}
