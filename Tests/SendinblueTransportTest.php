<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Sendinblue\Tests;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Notifier\Bridge\Sendinblue\SendinblueTransport;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Test\TransportTestCase;
use Symfony\Component\Notifier\Tests\Transport\DummyMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @group legacy
 */
final class SendinblueTransportTest extends TransportTestCase
{
    public static function createTransport(?HttpClientInterface $client = null): SendinblueTransport
    {
        return (new SendinblueTransport('api-key', '0611223344', $client ?? new MockHttpClient()))->setHost('host.test');
    }

    public static function toStringProvider(): iterable
    {
        yield ['sendinblue://host.test?sender=0611223344', self::createTransport()];
    }

    public static function supportedMessagesProvider(): iterable
    {
        yield [new SmsMessage('0611223344', 'Hello!')];
    }

    public static function unsupportedMessagesProvider(): iterable
    {
        yield [new ChatMessage('Hello!')];
        yield [new DummyMessage()];
    }

    public function testSendWithErrorResponseThrowsTransportException()
    {
        $client = new MockHttpClient(new MockResponse(json_encode(['code' => 400, 'message' => 'bad request']), ['http_code' => 400]));

        $transport = self::createTransport($client);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Unable to send the SMS: bad request');

        $transport->send(new SmsMessage('phone', 'testMessage'));
    }
}
