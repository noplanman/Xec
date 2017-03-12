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

class CommandExceptionTest extends TestCase
{
    public function testCommandErrorOutputAndThrowException()
    {
        $c = new Command('sh');
        $c->throwExceptionOnError(false);
        $r = $c->execute([__DIR__ . '/stderr.sh', 'fail_1']);
        $this->assertEquals('fail_1', $r->stderr);

        try {
            $c->throwExceptionOnError(true);
            $c->execute([__DIR__ . '/stderr.sh', 'fail_2']);
            $this->assertTrue(false);
        } catch (CommandException $e) {
            $this->assertEquals('fail_2', $e->getResult()->stderr);
        }
    }

    public function testCommandExitCodeAndThrowException()
    {
        $c = new Command('exit');
        $c->throwExceptionOnError(false);
        $r = $c->execute([3]);
        $this->assertEquals(3, $r->exitcode);

        try {
            $c->throwExceptionOnError(true);
            $c->execute([5]);
            $this->assertTrue(false);
        } catch (CommandException $e) {
            $this->assertEquals(5, $e->getResult()->exitcode);
        }
    }
}
