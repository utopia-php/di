<?php

namespace Utopia\DI;

use PHPUnit\Framework\TestCase;

class HookTest extends TestCase
{
    public function testDefaultActionIsNoop(): void
    {
        $hook = new Hook();

        $this->assertNull(($hook->getAction())());
    }

    public function testActionCanBeSet(): void
    {
        $hook = new Hook();
        $called = false;

        $hook->action(function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertSame('ok', ($hook->getAction())());
        $this->assertTrue($called);
    }

    public function testGroupsParamsAndInjections(): void
    {
        $hook = new Hook();

        $hook->groups(['jobs'])
            ->param('userId', 'validator', 'default', true, ['di'])
            ->inject('workerId');

        $this->assertSame(['jobs'], $hook->getGroups());

        $params = $hook->getParams();
        $this->assertArrayHasKey('userId', $params);
        $this->assertSame('validator', $params['userId']['validator']);
        $this->assertSame('default', $params['userId']['default']);
        $this->assertTrue($params['userId']['optional']);
        $this->assertSame(['di'], $params['userId']['injections']);
        $this->assertSame(0, $params['userId']['order']);

        $hook->setParamValue('userId', '123');
        $params = $hook->getParams();
        $this->assertSame('123', $params['userId']['value']);

        $injections = $hook->getInjections();
        $this->assertCount(1, $injections);
        $this->assertSame('workerId', $injections[0]['name']);
        $this->assertSame(1, $injections[0]['order']);
    }

    public function testExplicitOrderAdvancesImplicitOrder(): void
    {
        $hook = new Hook();

        $hook->param('first', null, null, false, [], 2)
            ->inject('second', 1)
            ->param('third');

        $params = $hook->getParams();
        $this->assertSame(2, $params['first']['order']);
        $this->assertSame(3, $params['third']['order']);

        $injections = $hook->getInjections();
        $this->assertSame(1, $injections[0]['order']);
    }
}
