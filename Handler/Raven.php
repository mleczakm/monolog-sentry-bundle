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
    
    /**
     * Fix until https://github.com/Seldaek/monolog/pull/1180 will be merged
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $previousUserContext = false;
        $options = array();
        $options['level'] = $this->logLevels[$record['level']];
        $options['tags'] = array();
        if (!empty($record['extra']['tags'])) {
            $options['tags'] = array_merge($options['tags'], $record['extra']['tags']);
            unset($record['extra']['tags']);
        }
        if (!empty($record['context']['tags'])) {
            $options['tags'] = array_merge($options['tags'], $record['context']['tags']);
            unset($record['context']['tags']);
        }
        if (!empty($record['context']['fingerprint'])) {
            $options['fingerprint'] = $record['context']['fingerprint'];
            unset($record['context']['fingerprint']);
        }
        if (!empty($record['context']['logger'])) {
            $options['logger'] = $record['context']['logger'];
            unset($record['context']['logger']);
        } else {
            $options['logger'] = $record['channel'];
        }
        foreach ($this->getExtraParameters() as $key) {
            foreach (array('extra', 'context') as $source) {
                if (!empty($record[$source][$key])) {
                    $options[$key] = $record[$source][$key];
                    unset($record[$source][$key]);
                }
            }
        }
        if (!empty($record['context'])) {
            $options['extra']['context'] = $record['context'];
            if (!empty($record['context']['user'])) {
                $previousUserContext = $this->ravenClient->context->user;
                $this->ravenClient->user_context($record['context']['user']);
                unset($options['extra']['context']['user']);
            }
        }
        if (!empty($record['contexts'])) {
            $options['contexts'] = $record['contexts'];
        }
        if (!empty($record['extra'])) {
            $options['extra']['extra'] = $record['extra'];
        }
        if (!empty($this->release) && !isset($options['release'])) {
            $options['release'] = $this->release;
        }
        if (isset($record['context']['exception']) && ($record['context']['exception'] instanceof \Exception || (PHP_VERSION_ID >= 70000 && $record['context']['exception'] instanceof \Throwable))) {
            $options['message'] = $record['formatted'];
            $this->ravenClient->captureException($record['context']['exception'], $options);
        } else {
            $this->ravenClient->captureMessage($record['formatted'], array(), $options);
        }
        if ($previousUserContext !== false) {
            $this->ravenClient->user_context($previousUserContext);
        }
    }
}
