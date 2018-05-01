<?php

use McValidator as MV;
use PHPUnit\Framework\TestCase;

class ExamplesTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        // creates a quick section on namespace filter called merge
        // closure sections is not encouraged since it will make
        // serialization slow
        \McValidator\Base::create('filter', 'merge', function (\McValidator\Data\Capsule $capsule) {
            $new = $capsule->getOptions()->getValue();

            return $capsule->newValue(function ($value) use ($new) {
                if ($value === null) {
                    return $new;
                } else if ($value instanceof \Heterogeny\Dict) {
                    return $value->merge($new);
                }

                throw new Exception('Nope');
            });
        });

        // creates a quick section on namespace filter called filter
        // closure sections is not encouraged since it will make
        // serialization slow
        \McValidator\Base::create('filter', 'replace', function (\McValidator\Data\Capsule $capsule) {
            return $capsule->newValue($capsule->getOptions()->getValue());
        });

        // creates a quick section on namespace filter called truncate
        // closure sections is not encouraged since it will make
        // serialization slow
        \McValidator\Base::create('filter', 'truncate', function (\McValidator\Data\Capsule $capsule) {
            $limit = $capsule->getOptions()->getOrElse('limit', 10);
            $end = $capsule->getOptions()->getOrElse('end', '...');

            return $capsule->newValue(function ($str) use ($limit, $end) {
                return MV\Support\Str::limit($str, $limit, $end);
            });
        });
    }

    public function testExample1(): void
    {
        // Builder is important because we can chain validator with them without
        // too much work.
        $builder = MV\valid('rule/is-string');

        // Build the pipe
        $pipe = $builder->build();

        // Pump the value through the pipe
        // Result contains the value and also informations about
        // the runtime, such as errors and messages
        $result = $pipe->pump(10);

        // Gets the runtime state
        $state = $result->getState();

        // Gets the message of the head(first item) of errors
        $this->assertGreaterThan(0, $state->getErrors()->count());

        // We need more!
        $builder2 = MV\shape_of([
            'a' => $builder
        ]);

        $pipe2 = $builder2->build();

        $result2 = $pipe2->pump(dict([
            'a' => 10
        ]));

        // Gets the runtime state
        $state2 = $result2->getState();

        // Gets the message of the head(first item) of errors
        $this->assertGreaterThan(0, $state2->getErrors()->count());

        // Gets the field path of the error!
        $this->assertEquals(
            ['$', 'a'],
            $state2->getErrors()->head()->getPath()
        );

        // Gets the field path of the error!
        $this->assertEquals(
            '$/a',
            $state2->getErrors()->head()->getStringPath('/')
        );
    }
}
