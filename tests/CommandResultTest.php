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

use NPM\Xec\CommandResult;
use PHPUnit\Framework\TestCase;

class CommandResultTest extends TestCase
{
    public function testConstruct()
    {
        $r = new CommandResult('out', 'err', 3, false);

        $this->assertSame('out', $r->stdout);
        $this->assertSame('err', $r->stderr);
        $this->assertSame(3, $r->exitcode);
        $this->assertFalse($r->killed);
    }
}
