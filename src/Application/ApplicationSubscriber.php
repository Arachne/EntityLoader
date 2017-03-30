<?php

declare(strict_types=1);

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Nette\Application\BadRequestException;
use Nette\Application\InvalidPresenterException;
use Nette\Http\IResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symplify\SymfonyEventDispatcher\Adapter\Nette\Event\RequestRecievedEvent;

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
            RequestRecievedEvent::NAME => 'requestHandler',
        ];
    }

    /**
     * @throws BadRequestException
     */
    public function requestHandler(RequestRecievedEvent $event): void
    {
        try {
            $this->loader->filterIn($event->getRequest());
        } catch (InvalidPresenterException | UnexpectedValueException $e) {
            throw new BadRequestException('Request has invalid presenter.', IResponse::S404_NOT_FOUND, $e);
        }
    }
}
