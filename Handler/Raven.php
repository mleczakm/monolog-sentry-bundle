<?php

declare(strict_types=1);

namespace Dziki\MonologSentryBundle\Handler;

use Monolog\Handler\RavenHandler;

class Raven extends RavenHandler
{
    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records): void
    {
        $level = $this->level;

        // filter records based on their level
        $records = array_filter(
            $records,
            function ($record) use ($level) {
                return $record['level'] >= $level;
            }
        );

        if (!$records) {
            return;
        }

        // the record with the highest severity is the "main" one
        $record = array_reduce(
            $records,
            function ($highest, $record) {
                if ($record['level'] > $highest['level']) {
                    return $record;
                }

                return $highest;
            }
        );

        // the other ones are added as a context item
        $logs = [];
        foreach ($records as $r) {
            $log = $this->processRecord($r);

            $crumb = [
                'category' => $log['channel'],
                'message' => $log['message'],
                'level' => strtolower($log['level_name']),
                'timestamp' => (float)$log['datetime']->format('U.u'),
            ];

            if (array_key_exists('context', $log)) {
                if ($log['channel'] === 'request' && array_key_exists('route_parameters', $log['context'])) {
                    $crumb['data']['route'] = $log['context']['route_parameters']['_route'];
                    $crumb['data']['controller'] = $log['context']['route_parameters']['_controller'];
                    $crumb['data']['uri'] = $log['context']['request_uri'];
                }

                if ($log['channel'] === 'security' && array_key_exists('user', $log['context'])) {
                    $crumb['data']['user'] = $log['context']['user']['username'];
                }
            }

            $this->ravenClient->breadcrumbs->record($crumb);
        }

        if ($logs) {
            $record['context']['logs'] = (string)$this->getBatchFormatter()->formatBatch($logs);
        }

        $this->handle($record);
    }
}
