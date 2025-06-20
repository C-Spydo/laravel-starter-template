<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;

class JsonFormatter extends BaseJsonFormatter
{
    public function __construct()
    {
        parent::__construct();
        $this->includeStacktraces = true;
    }

    protected function normalize(mixed $data, int $depth = 0): mixed
    {
        if ($depth > $this->maxNormalizeDepth) {
            return 'Over ' . $this->maxNormalizeDepth . ' levels deep, aborting normalization';
        }

        if (is_array($data)) {
            $normalized = [];

            $count = 1;
            foreach ($data as $key => $value) {
                if ($count++ > $this->maxNormalizeItemCount) {
                    $normalized['...'] = 'Over ' . $this->maxNormalizeItemCount . ' items (' . count($data) . ' total), aborting normalization';
                    break;
                }

                $normalized[$key] = $this->normalize($value, $depth + 1);
            }

            return $normalized;
        }

        if (is_object($data)) {
            if ($data instanceof \DateTime) {
                return $data->format($this->dateFormat);
            }

            if ($data instanceof \Throwable) {
                return $this->normalizeException($data, $depth);
            }

            // if the object has a jsonSerialize method, use that
            if (method_exists($data, 'jsonSerialize')) {
                return $this->normalize($data->jsonSerialize(), $depth);
            }

            // if the object has a toArray method, use that
            if (method_exists($data, 'toArray')) {
                return $this->normalize($data->toArray(), $depth);
            }

            // if the object has a __toString method, use that
            if (method_exists($data, '__toString')) {
                return (string) $data;
            }

            return sprintf("[object] (%s: %s)", get_class($data), spl_object_hash($data));
        }

        if (is_resource($data)) {
            return sprintf('[resource] (%s)', get_resource_type($data));
        }

        return $data;
    }

    protected function normalizeException(\Throwable $e, int $depth = 0): array
    {
        $data = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile() . ':' . $e->getLine(),
        ];

        if ($this->includeStacktraces) {
            $trace = $e->getTraceAsString();
            $data['trace'] = $trace;
        }

        if ($e->getPrevious()) {
            $data['previous'] = $this->normalizeException($e->getPrevious(), $depth + 1);
        }

        return $data;
    }
} 