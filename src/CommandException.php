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
 * Command Exception
 */
class CommandException extends \Exception
{
    /**
     * @var CommandResult
     */
    private $result;

    public function __construct($result, $message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->result = $result;
    }

    /**
     * @return CommandResult
     */
    public function getResult()
    {
        return $this->result;
    }
}
