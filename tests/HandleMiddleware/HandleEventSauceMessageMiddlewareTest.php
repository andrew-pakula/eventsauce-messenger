<?php

declare(strict_types=1);

namespace Tests\HandleMiddleware;

use Andreo\EventSauce\Messenger\Middleware\HandleEventSauceMessageMiddleware;
use Andreo\EventSauce\Messenger\Stamp\MessageStamp;
use EventSauce\EventSourcing\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Middleware\StackMiddleware;

final class HandleEventSauceMessageMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function should_handle_eventsauce_message(): void
    {
        $message = new DummyMessage();
        $headers = ['_foo' => 'foo'];

        $handler = $this->createPartialMock(FakeHandler::class, ['__invoke']);

        $middleware = new HandleEventSauceMessageMiddleware(
            new HandlersLocator([
                $message::class => [$handler],
            ])
        );

        $handler
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback(static fn ($subject) => $subject instanceof Message && $subject->event() === $message)
            );

        $envelope = Envelope::wrap($message, [new MessageStamp($headers)]);
        $middleware->handle($envelope, new StackMiddleware());
    }
}
