<?php

declare(strict_types=1);

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Contributte\Events\Bridges\Application\Event\ApplicationEvents;
use Contributte\Events\Bridges\Application\Event\RequestEvent;
use Nette\Application\BadRequestException;
use Nette\Application\InvalidPresenterException;
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
    public static function getSubscribedEvents(): array
    {
        return [
            ApplicationEvents::ON_REQUEST => 'requestHandler',
        ];
    }

    /**
     * @throws BadRequestException
     */
    public function requestHandler(RequestEvent $event): void
    {
        try {
            $this->loader->filterIn($event->getRequest());
        } catch (InvalidPresenterException | UnexpectedValueException $e) {
            throw new BadRequestException('Request has invalid presenter.', IResponse::S404_NOT_FOUND, $e);
        }
    }
}
