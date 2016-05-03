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
use Kdyby\Events\Subscriber;
use Nette\Application\Application;
use Nette\Application\BadRequestException;
use Nette\Application\InvalidPresenterException;
use Nette\Application\Request;
use Nette\Http\IResponse;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationListener implements Subscriber
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
    public function getSubscribedEvents()
    {
        return [
            'Nette\Application\Application::onRequest' => 'requestHandler',
        ];
    }

    /**
     * @param Application $application
     * @param Request $request
     * @throws BadRequestException
     */
    public function requestHandler(Application $application, Request $request)
    {
        try {
            $this->loader->filterIn($request);
        } catch (InvalidPresenterException $e) {
            throw new BadRequestException('Request has invalid presenter.', IResponse::S404_NOT_FOUND, $e);
        } catch (UnexpectedValueException $e) {
            throw new BadRequestException('Request has invalid parameter.', IResponse::S404_NOT_FOUND, $e);
        }
    }
}
