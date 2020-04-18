<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Tests\Unit\Handler;

use Dziki\MonologSentryBundle\Handler\Raven;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Raven_Client;

/**
 * @covers \Dziki\MonologSentryBundle\Handler\Raven
 */
class RavenTest extends TestCase
{
    public function setUp(): void
    {
        if (!class_exists('Raven_Client')) {
            $this->markTestSkipped('sentry/sentry not installed');
        }
    }

    public function testConstruct()
    {
        $handler = new Raven($this->getRavenClient());
        $this->assertInstanceOf(Raven::class, $handler);
    }

    protected function getRavenClient()
    {
        $dsn = 'http://43f6017361224d098402974103bfc53d:a6a0538fc2934ba2bed32e08741b2cd3@marca.python.live.cheggnet.com:9000/1';

        return new MockRavenClient($dsn);
    }

    public function testDebug()
    {
        $ravenClient = $this->getRavenClient();
        $handler = $this->getHandler($ravenClient);

        $record = $this->getRecord(Logger::DEBUG, 'A test debug message');
        $handler->handle($record);

        $this->assertEquals($ravenClient::DEBUG, $ravenClient->lastData['level']);
        $this->assertContains($record['message'], $ravenClient->lastData['message']);
    }

    protected function getHandler($ravenClient)
    {
        $handler = new Raven($ravenClient);

        return $handler;
    }

    /**
     * @param int    $level
     * @param string $message
     * @param array  $context
     * @param string $channel
     * @param array  $extra
     *
     * @return array Record
     *
     * @throws \Exception
     */
    protected function getRecord($level = Logger::WARNING, $message = 'test', array $context = [], $channel = 'test', $extra = []): array
    {
        return [
            'message' => (string) $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => $channel,
            'datetime' => new \DateTimeImmutable(),
            'extra' => $extra,
        ];
    }

    public function testWarning()
    {
        $ravenClient = $this->getRavenClient();
        $handler = $this->getHandler($ravenClient);

        $record = $this->getRecord(Logger::WARNING, 'A test warning message');
        $handler->handle($record);

        $this->assertEquals($ravenClient::WARNING, $ravenClient->lastData['level']);
        $this->assertContains($record['message'], $ravenClient->lastData['message']);
    }

    public function testTag()
    {
        $ravenClient = $this->getRavenClient();
        $handler = $this->getHandler($ravenClient);

        $tags = [1, 2, 'foo'];
        $record = $this->getRecord(Logger::INFO, 'test', ['tags' => $tags]);
        $handler->handle($record);

        $this->assertEquals($tags, $ravenClient->lastData['tags']);
    }

    public function testExtraParameters()
    {
        $ravenClient = $this->getRavenClient();
        $handler = $this->getHandler($ravenClient);

        $checksum = '098f6bcd4621d373cade4e832627b4f6';
        $release = '05a671c66aefea124cc08b76ea6d30bb';
        $eventId = '31423';
        $record = $this->getRecord(
            Logger::INFO,
            'test',
            ['checksum' => $checksum, 'release' => $release, 'event_id' => $eventId]
        );
        $handler->handle($record);

        $this->assertEquals($checksum, $ravenClient->lastData['checksum']);
        $this->assertEquals($release, $ravenClient->lastData['release']);
        $this->assertEquals(
            $eventId,
            $ravenClient->lastData['event_id'] ?? $ravenClient->lastData['extra']['context']['event_id']
        );
    }

    public function testFingerprint()
    {
        $ravenClient = $this->getRavenClient();
        $handler = $this->getHandler($ravenClient);

        $fingerprint = ['{{ default }}', 'other value'];
        $record = $this->getRecord(Logger::INFO, 'test', ['fingerprint' => $fingerprint]);
        $handler->handle($record);

        $this->assertEquals($fingerprint, $ravenClient->lastData['fingerprint']);
    }

    public function testUserContext()
    {
        $ravenClient = $this->getRavenClient();
        $handler = $this->getHandler($ravenClient);

        $recordWithNoContext = $this->getRecord(Logger::ERROR, 'test with default user context');
        // set user context 'externally'

        $user = [
            'id' => '123',
            'email' => 'test@test.com',
        ];

        $recordWithContext = $this->getRecord(
            Logger::ERROR,
            'test',
            [
                'user' => $user,
                'tags' => ['another_tag' => 'null_value'],
                'something' => 'anything',
                'exception' => new \Exception('test exception'),
                'logger' => 'logger',
            ],
            'any',
            [
                'tags' => ['tag_name' => 'value'],
                'some_additional_data_key' => 'some_additional_data',
            ]
        );

        // handle with null context
        $ravenClient->user_context(null);
        $handler->handle($recordWithContext);

        $this->assertEquals($user, $ravenClient->lastData['user']);

        $ravenClient->user_context(['id' => 'test_user_id']);
        // handle context
        $handler->handle($recordWithContext);
        $this->assertEquals($user, $ravenClient->lastData['user']);

        // check to see if its reset
        $handler->handle($recordWithNoContext);
        $this->assertInternalType('array', $ravenClient->context->user);
        $this->assertSame('test_user_id', $ravenClient->context->user['id']);
    }

    public function testException()
    {
        $ravenClient = $this->getRavenClient();
        $handler = $this->getHandler($ravenClient);

        try {
            $this->methodThatThrowsAnException();
        } catch (\Exception $e) {
            $record = $this->getRecord(Logger::ERROR, $e->getMessage(), ['context' => ['exception' => $e]]);
            $handler->handle($record);
        }

        $this->assertEquals('[test] '.$record['message'], $ravenClient->lastData['message']);
    }

    private function methodThatThrowsAnException()
    {
        throw new \Exception('This is an exception');
    }

    public function testHandleBatch()
    {
        $records = $this->getMultipleRecords();
        $records[] = $this->getRecord(Logger::WARNING, 'warning');
        $records[] = $this->getRecord(Logger::WARNING, 'warning');

        $logFormatter = $this->createMock('Monolog\\Formatter\\FormatterInterface');

        $formatter = $this->createMock('Monolog\\Formatter\\FormatterInterface');
        $formatter->expects($this->once())->method('format')->with(
            $this->callback(
                function ($record) {
                    return 400 == $record['level'];
                }
            )
        )
        ;

        $handler = $this->getHandler($this->getRavenClient());
        $handler->setBatchFormatter($logFormatter);
        $handler->setFormatter($formatter);
        $handler->handleBatch($records);

        $handler->handleBatch([]);
    }

    protected function getMultipleRecords(): array
    {
        return [
            $this->getRecord(Logger::DEBUG, 'debug message 1'),
            $this->getRecord(Logger::DEBUG, 'debug message 2'),
            $this->getRecord(Logger::INFO, 'information'),
            $this->getRecord(Logger::WARNING, 'warning'),
            $this->getRecord(Logger::ERROR, 'error'),
        ];
    }

    /**
     * @test
     */
    public function doNothingOnEmptyBatch(): void
    {
        $logFormatter = $this->createMock(FormatterInterface::class);
        $logFormatter->expects($this->never())
                     ->method('format')
        ;

        $logFormatter->expects($this->never())
                     ->method('formatBatch')
        ;

        $handler = $this->getHandler($this->getRavenClient());
        $handler->setBatchFormatter($logFormatter);
        $handler->setFormatter($logFormatter);
        $handler->handleBatch([]);
    }

    /**
     * @test
     */
    public function addContextsIfProvided(): void
    {
        $logFormatter = $this->createMock(FormatterInterface::class);
        $logFormatter->expects($this->once())
                     ->method('format')
                     ->willReturnArgument(0)
        ;

        $ravenClient = $this->getRavenClient();

        $handler = $this->getHandler($ravenClient);
        $handler->setBatchFormatter($logFormatter);
        $handler->setFormatter($logFormatter);

        $record = $this->getRecord();
        $record['contexts'] = ['browser_context'];
        $handler->handle($record);

        $this->assertSame(['browser_context'], $ravenClient->lastData['contexts']);
    }

    public function testHandleBatchDoNothingIfRecordsAreBelowLevel()
    {
        $records = [
            $this->getRecord(Logger::DEBUG, 'debug message 1'),
            $this->getRecord(Logger::DEBUG, 'debug message 2'),
            $this->getRecord(Logger::INFO, 'information'),
        ];

        $handler = $this->getMockBuilder('Monolog\Handler\RavenHandler')
                        ->setMethods(['handle'])
                        ->setConstructorArgs([$this->getRavenClient()])
                        ->getMock()
        ;
        $handler->expects($this->never())->method('handle');
        $handler->setLevel(Logger::ERROR);
        $handler->handleBatch($records);
    }

    public function testHandleBatchPicksProperMessage()
    {
        $records = [
            $this->getRecord(Logger::DEBUG, 'debug message 1'),
            $this->getRecord(Logger::DEBUG, 'debug message 2'),
            $this->getRecord(Logger::INFO, 'information 1'),
            $this->getRecord(Logger::ERROR, 'error 1'),
            $this->getRecord(Logger::WARNING, 'warning'),
            $this->getRecord(Logger::ERROR, 'error 2'),
            $this->getRecord(Logger::INFO, 'information 2'),
        ];

        $logFormatter = $this->createMock('Monolog\\Formatter\\FormatterInterface');

        $formatter = $this->createMock('Monolog\\Formatter\\FormatterInterface');
        $formatter->expects($this->once())->method('format')->with(
            $this->callback(
                function ($record) use ($records) {
                    return 'error 1' == $record['message'];
                }
            )
        )
        ;

        $handler = $this->getHandler($this->getRavenClient());
        $handler->setBatchFormatter($logFormatter);
        $handler->setFormatter($formatter);
        $handler->handleBatch($records);
    }

    public function testGetSetBatchFormatter()
    {
        $ravenClient = $this->getRavenClient();
        $handler = $this->getHandler($ravenClient);

        $handler->setBatchFormatter($formatter = new LineFormatter());
        $this->assertSame($formatter, $handler->getBatchFormatter());
    }

    public function testRelease()
    {
        $ravenClient = $this->getRavenClient();
        $handler = $this->getHandler($ravenClient);
        $release = 'v42.42.42';
        $handler->setRelease($release);
        $record = $this->getRecord(Logger::INFO, 'test');
        $handler->handle($record);

        $this->assertEquals($release, $ravenClient->lastData['release']);

        $localRelease = 'v41.41.41';
        $record = $this->getRecord(Logger::INFO, 'test', ['release' => $localRelease]);
        $handler->handle($record);
        $this->assertEquals($localRelease, $ravenClient->lastData['release']);
    }

    public function testHandleBatchBreadcrumbsSecurityAndRouting(): void
    {
        $records = $this->getMultipleRecords();
        $records[] = $this->getRecord(
            Logger::WARNING,
            'warning',
            ['route_parameters' => [
                '_route' => 'foo_bar_route',
                '_controller' => 'Foo\Bar\Controller',
            ],
                'request_uri' => 'foo.bar',
            ],
            'request'
        );
        $records[] = $this->getRecord(Logger::WARNING, 'warning', ['user' => ['username' => 'foobar']], 'security');

        $logFormatter = $this->createMock('Monolog\\Formatter\\FormatterInterface');

        $formatter = $this->createMock('Monolog\\Formatter\\FormatterInterface');
        $formatter->expects($this->once())->method('format')->with(
            $this->callback(
                function ($record) {
                    return 400 == $record['level'];
                }
            )
        )
        ;

        $handler = $this->getHandler($this->getRavenClient());
        $handler->setBatchFormatter($logFormatter);
        $handler->setFormatter($formatter);
        $handler->handleBatch($records);
    }

    protected function getIdentityFormatter(): FormatterInterface
    {
        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->expects($this->any())
                  ->method('format')
                  ->will(
                      $this->returnCallback(
                          function ($record) {
                              return $record['message'];
                          }
                      )
                  )
        ;

        return $formatter;
    }
}

class MockRavenClient extends Raven_Client
{
    public $lastData;
    public $lastStack;

    public function capture($data, $stack = null, $vars = null)
    {
        $data = array_merge($this->get_user_data(), $data);
        $this->lastData = $data;
        $this->lastStack = $stack;
    }
}
