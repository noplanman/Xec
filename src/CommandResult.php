<?php declare(strict_types = 1);
/**
 * This file is part of the Xec package.
 *
 * (c) Armando Lüscher <armando@noplanman.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NPM\Xec;

/**
 * Command Execution Result
 */
class CommandResult
{
    /** @var string */
    public $stdout;
    /** @var string */
    public $stderr;
    /** @var int */
    public $exitcode;
    /** @var bool */
    public $killed;

    public function __construct($stdout, $stderr, $exitcode, $killed)
    {
        $this->stdout   = $stdout;
        $this->stderr   = $stderr;
        $this->exitcode = $exitcode;
        $this->killed   = $killed;
    }
}
