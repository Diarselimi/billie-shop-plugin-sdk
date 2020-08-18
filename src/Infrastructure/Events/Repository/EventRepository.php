<?php

declare(strict_types=1);

namespace App\Infrastructure\Events\Repository;

use App\DomainEvent\AnalyticsEvent\AbstractAnalyticsEvent;
use League\Flysystem\FilesystemInterface;

final class EventRepository
{
    /**
     * @var FilesystemInterface
     */
    private $storage;

    /**
     * @var AbstractAnalyticsEvent[]
     */
    private $events;

    public function __construct(FilesystemInterface $eventsUploadsStorage)
    {
        $this->storage = $eventsUploadsStorage;
        $this->events = [];
    }

    public function add(AbstractAnalyticsEvent $event): void
    {
        $this->events[] = $event;
    }

    public function flush(): void
    {
        foreach ($this->events as $event) {
            $this->persistEvent($event->getEventType(), $event->toArray());
        }
    }

    private function persistEvent(string $domain, array $value): void
    {
        $json = \GuzzleHttp\json_encode($value);
        $path = sprintf('%s/%s.json', $domain, sha1($json.microtime()));
        $this->storage->put($path, $json);
    }
}
