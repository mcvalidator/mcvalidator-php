<?php

use McValidator as MV;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
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

    /**
     * @throws Exception
     */
    public function test1()
    {
        $builder = MV\valid(
            MV\section('filter/replace', 20),
            MV\section('filter/replace', 30),
            MV\section('filter/replace', 40)
        );

        $validator = $builder->build();

        $x = $validator->pump(10);

        $v1 = $x->getOldValue();
        $v2 = $v1->getOldValue();
        $v3 = $v2->getOldValue();
        $v4 = $v3->getOldValue();

        $this->assertTrue($x->get() === 40);
        $this->assertTrue($v1->get() === 30);
        $this->assertTrue($v2->get() === 20);
        $this->assertTrue($v3->get() === 10);
        $this->assertTrue($v4 === null);
    }

    /**
     * @throws Exception
     */
    public function test2()
    {
        $builder = MV\shape_of([
            'a' => MV\valid(
                MV\section('filter/replace', 20),
                MV\section('filter/replace', 30),
                MV\section('filter/replace', 40)
            ),
            // when merging you need to defined the values which will be merge,
            // mcvalidator wont let foreign data to stay on its values.
            'b' => MV\section('rule/is-string')
        ], MV\section('filter/merge', dict(['b' => 'c'])));

        $validator = $builder->build();

        $x = $validator->pump(dict([
            'a' => 10
        ]));

        $a = $x->get()->get('a');
        $b = $x->get()->get('b');

        $this->assertEquals(40, $a);
        $this->assertEquals('c', $b);
    }

    /**
     * @throws Exception
     */
    public function testNested()
    {
        $builder = MV\shape_of([
            'a' => MV\shape_of([
                'b' => MV\shape_of([
                    'c' => MV\list_of(
                        MV\section('filter/replace', 20),
                        MV\section('filter/replace', 30),
                        MV\section('filter/replace', 40)
                    )
                ])
            ])
        ]);

        $validator = $builder->build();

        $x = $validator->pump(dict([
            'a' => dict([
                'b' => dict([
                    'c' => seq(1, 2, 3, 4)
                ])
            ])
        ]));

        $y = $validator->pump(dict([]));

        $a = $x->get();
        $b = $y->get();
        $c = $y->get(true, true, true);

        $d = $a->getOrElse('a/b/c');
        $e = $b->getOrElse('a/b/c');
        $f = $c->getOrElse('a/b/c');

        $this->assertTrue(
            $d->equals(seq(40, 40, 40, 40))
        );

        $this->assertTrue(
            $e === null
        );

        $this->assertTrue(
            $f instanceof \Heterogeny\Seq
        );
    }

    public function testYaml()
    {
        $yml = "- rule/is-string";
        $validator = McValidator\Parser\Yaml::parseSingle($yml);

        $this->assertInstanceOf(MV\Support\ValidBuilder::class, $validator);

        $validator = $validator->build();

        $result = $validator->pump(10);

        $this->assertInstanceOf(MV\Data\InvalidValue::class, $result);
    }

    public function testYaml2()
    {
        $yml = <<<YAML
!shape-of
_:
  filter/merge:
    c: "20"
a:
  - rule/is-string 
  - filter/truncate:
      limit: 10
      end: …
b:
  - filter/to-string
  - rule/is-string

c:
  - rule/is-string
YAML;

        /** @var MV\Support\Builder $validators */
        $validators = McValidator\Parser\Yaml::parseSingle($yml);

        $this->assertInstanceOf(MV\Support\ShapeOfBuilder::class, $validators);

        $validator = $validators->build();

        $this->assertInstanceOf(MV\Contracts\Pipeable::class, $validator);

        $result = $validator->pump(dict([
            'a' => 'verylongword',
            'b' => dict([
                'c' => dict([
                    'd' => dict([
                        'e' => 10
                    ])
                ])
            ])
        ]));

        $hasBError = !$result->getState()->getErrors()->filter(function (McValidator\Data\Error $x) {
            return $x->getField()->getPath() === ['$', 'b'];
        })->isEmpty();

        $this->assertTrue($hasBError);

        $value = $result->get()->all();

        $this->assertEquals(['a' => 'verylongwo…', 'c' => '20'], $value);
    }

    public function testSerialization()
    {
        $yml = <<<YAML
!shape-of
a:
  - rule/is-string 
  - filter/truncate:
      limit: 10
      end: …
b:
  - filter/to-string
  - rule/is-string
c: !shape-of
  d: !shape-of
    e: !shape-of
      f: rule/is-string
g: filter/to-int
h: filter/to-int
YAML;

        $validators = McValidator\Parser\Yaml::parseSingle($yml);

        $this->assertInstanceOf(MV\Support\ShapeOfBuilder::class, $validators);

        $validator = $validators->build();

        $serializedValidator = serialize($validator);

        $validator = unserialize($serializedValidator);

        $this->assertInstanceOf(MV\Contracts\Pipeable::class, $validator);

        /** @var MV\Data\Value $result */
        $result = $validator->pump(dict([
            'a' => 'verylongword',
            'b' => dict([
                'c' => dict([
                    'd' => dict([
                        'e' => 10
                    ])
                ])
            ]),
            'c' => dict([
                'd' => dict([
                    'e' => dict([
                        'f' => 10
                    ])
                ])
            ]),
            'g' => '10',
            'h' => 'hhhhhh'
        ]));

        $state = $result->getState();

        $newState = $state->ignoreErrors(['c']);

        $hasBError = $newState->hasError(['b']);
        $hasFError = $newState->hasError(['f']);
        $hasHError = $newState->hasError(['h']);

        $this->assertTrue($hasBError);
        $this->assertTrue($hasHError);

        // f must not be present in errors because it belongs to
        // [c, d, e]
        $this->assertFalse($hasFError);

        $value = $result->get()->all();

        $this->assertArraySubset(['a' => 'verylongwo…', 'g' => 10], $value);
    }

    public function testMore(): void
    {
        $yml = <<<YAML
!shape-of
a: !shape-of
  b: filter/to-int
c: !list-of
  - !shape-of
      d: filter/to-int
e: !list-of
  - filter/to-int
f: !shape-of
  g: !shape-of
    h: !shape-of
      i: !shape-of
        j: filter/to-int
YAML;

        /** @var MV\Support\Builder $validators */
        $validators = MV\Parser\Yaml::parseSingle($yml);

        $validator = $validators->build();

        $result = $validator->pump(dict([
            'a' => dict([
                'b' => 'hhhhhhhhhh'
            ]),
            'c' => seq(dict([
                'd' => 'hhhhhhhhhhhh'
            ])),
            'e' => seq('hhhhhhhhhhhh'),
            'f' => dict([
                'g' => dict([
                    'h' => dict([
                        'i' => dict([
                            'j' => 'hhhhhhhhhh'
                        ])
                    ])
                ])
            ])
        ]));

        $state = $result->getState();

        $hasAError = $state->hasError(['a', 'b']);
        $hasCError = $state->hasError(['c', 0, 'd']);
        $hasEError = $state->hasError(['e', 0]);
        $hasFError = $state->hasError(['f', 'g', 'h', 'i', 'j']);

        $hasZError = $state->hasError(['a', 'b', 'z']);

        $this->assertTrue($hasAError);
        $this->assertTrue($hasCError);
        $this->assertTrue($hasEError);
        $this->assertTrue($hasFError);

        $this->assertFalse($hasZError);
    }

    public function testIsFilled(): void
    {
        $builder = MV\shape_of([
            'a' => MV\shape_of([
                'b' => MV\shape_of([
                    'c' => MV\shape_of([
                        'd' => MV\shape_of([
                            'e' => MV\shape_of([
                                'f' => MV\valid(
                                    'rule/is-int',
                                    'rule/is-filled'
                                )
                            ])
                        ])
                    ])
                ])
            ])
        ]);

        $validator = $builder->build();

        $x = $validator->pump(dict([]));

        $this->assertTrue($x->getState()->hasError(['a', 'b', 'c', 'd', 'e', 'f']));
    }

    public function testMultipleErrors(): void
    {
        $builder = MV\shape_of([
            'a' => MV\valid(
                'rule/is-int',
                'rule/is-filled'
            ),
            'b' => MV\valid(
                'rule/is-int',
                'rule/is-filled'
            ),
            'c' => MV\valid(
                'rule/is-int',
                'rule/is-filled'
            ),
            'd' => MV\valid(
                'rule/is-int',
                'rule/is-filled'
            ),
            'e' => MV\valid(
                'rule/is-int',
                'rule/is-filled'
            ),
            'f' => MV\valid(
                'rule/is-int',
                'rule/is-filled'
            )
        ]);

        $validator = $builder->build();

        $x = $validator->pump(dict([
            'f' => 10
        ]));

        $this->assertTrue($x->getState()->hasError(['a']));
        $this->assertTrue($x->getState()->hasError(['b']));
        $this->assertTrue($x->getState()->hasError(['c']));
        $this->assertTrue($x->getState()->hasError(['d']));
        $this->assertTrue($x->getState()->hasError(['e']));
        $this->assertFalse($x->getState()->hasError(['f']));
    }

    public function testToInt(): void
    {
        $builder = MV\shape_of([
            'a' => MV\valid('filter/to-int')
        ]);

        $validator = $builder->build();

        $x = $validator->pump(dict([
            'a' => '1000000'
        ]));

        $y = $x->get();

        $v = $y->get('a');

        $this->assertThat($v, $this->logicalAnd(
            $this->isType('int'),
            $this->greaterThan(0)
        ));
    }
}
