<?php declare(strict_types = 1);
/**
 * This file is part of the Xec package.
 *
 * (c) Armando Lüscher <armando@noplanman.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NPM\Xec\Tests;

use NPM\Xec\Command;
use NPM\Xec\CommandException;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function testConstructor()
    {
        $c = new Command('ls');
        $this->assertAttributeEquals('ls', 'command', $c);
    }

    public function testExecuteFail()
    {
        $this->expectException(CommandException::class);
        $c = new Command('invalid-application');
        $c->execute();
    }

    public function testExecute()
    {
        $c = new Command('echo');
        $r = $c->execute(['hello']);
        $this->assertEquals("hello\n", $r->stdout);

        $c = new Command('printf');
        $r = $c->execute(['hello']);
        $this->assertEquals('hello', $r->stdout);

        $c = new Command('cat');
        // cat needs a timeout, otherwise it becomes infinite.
        $r = $c->execute([], 'hello', 1);
        $this->assertEquals('hello', $r->stdout);
    }

    public function testExecuteTimeout()
    {
        $c = new Command('sleep');

        $r = $c->execute(['1'], null, 2);
        $this->assertFalse($r->killed);

        $r = $c->execute(['3'], null, 2);
        $this->assertTrue($r->killed);
    }

    public function testExecuteWithOutput()
    {
        $c = new Command('printf');
        $c->echoStdout(true);
        ob_start();
        $c->execute(['hello']);
        $stdout = ob_get_clean();
        $this->assertEquals('hello', $stdout);

        $c = new Command('sh');
        $c->throwExceptionOnError(false);
        $c->echoStderr(true);
        ob_start();
        $c->execute([__DIR__ . '/stderr.sh', 'fail_1']);
        $stderr = ob_get_clean();
        $this->assertEquals('fail_1', $stderr);
    }

    public function testGetRaw()
    {
        $c = new Command('echo');

        $c->execute(['hello']);
        $this->assertEquals("echo 'hello'", $c->getRaw());

        $c->execute(['hello world']);
        $this->assertEquals("echo 'hello world'", $c->getRaw());
    }

    public function testGetRawWithArgs()
    {
        $this->markTestSkipped();
        /*$c = new Command('echo');

        $c->execute(['hello' => 'world']);
        $this->assertEquals("echo hello='world'", $c->getRaw());*/
    }
}
