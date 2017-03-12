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
 * External command wrapper
 */
class Command
{
    /** @var string Path to executable. */
    protected $command;

    /** @var resource The running process. */
    protected $process;

    /** @var array Arguments for command. */
    protected $args = [];

    /** @var bool If stdout should be output. */
    protected $echo_stdout = false;

    /** @var bool If stderr should be output. */
    protected $echo_stderr = false;

    /** @var bool If an exception should be thrown on error. */
    protected $throw_on_error = true;

    /**
     * @param string $command Path to executable.
     */
    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * Execute command.
     *
     * @param string[]    $args    Command line arguments.
     * @param string|null $input   Input data.
     * @param int         $timeout Timeout to kill the command.
     *
     * @return CommandResult
     * @throws CommandException
     */
    public function execute(array $args = [], $input = null, $timeout = 0): CommandResult
    {
        (!$timeout || $timeout < 0) && $timeout = 0;

        $this->args = $args;
        $cmd        = $this->getRaw();

        $this->process = proc_open($cmd, [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);
        if (!is_resource($this->process)) {
            throw new CommandException(null, 'Failed to execute "' . $cmd . '"');
        }

        if (null !== $input) {
            fwrite($pipes[0], $input);
        }

        // Set the output streams to non-blocking.
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        // Output buffer.
        $stdout = '';
        $stderr = '';

        // Assume a timeout.
        $killed = true;

        $start = time();
        do {
            // Wait until we have output or the timer expired.
            $read  = [$pipes[1], $pipes[2]];
            $other = [];
            stream_select($read, $other, $other, $timeout ? 0 : null);

            // Get the status of the process before we read from the stream,
            // this way we can't lose the last bit of output if the process dies between these functions.
            $status = $this->getStatus();

            // Read the contents from the buffer.
            // This function will always return immediately as the stream is non-blocking.
            $stdout_ = stream_get_contents($pipes[1]);
            $stdout .= $stdout_;
            $this->echo_stdout && print $stdout_;

            $stderr .= stream_get_contents($pipes[2]);

            // Break from this loop if the process exited before the timeout.
            if (!$status['running']) {
                $killed = false;
                break;
            }
        } while (!$timeout || $start + $timeout > time());

        $this->echo_stderr && print $stderr;

        // Must be called while the process is still alive!
        $status = $this->getStatus(true);

        // Kill the process in case the timeout expired and it's still running.
        // If the process already exited this won't do anything.
        proc_terminate($this->process, 9);

        // Close all streams.
        fclose($pipes[0]); // stdin
        fclose($pipes[1]); // stdout
        fclose($pipes[2]); // stderr

        proc_close($this->process);

        $result = new CommandResult($stdout, $stderr, $status['exitcode'], $killed);

        if ($this->throw_on_error && (!empty($stderr) || $result->exitcode > 0)) {
            throw new CommandException($result);
        }

        return $result;
    }

    /**
     * Instead of just using proc_get_status, use this instead to ensure a correct exit code.
     *
     * @param bool $reset If the cached exitcode should be reset.
     *
     * @return array
     */
    protected function getStatus($reset = false): array
    {
        static $exitcode;

        $status = proc_get_status($this->process);

        // proc_get_status will only pull valid exitcode one
        // time after process has ended, so cache the exitcode
        // if the process is finished and $exitcode is uninitialized.
        if ($exitcode === null && $status['running'] === false) {
            $exitcode = $status['exitcode'];
        }
        $status['exitcode'] = $exitcode ?? -1;

        $reset && $exitcode = null;

        return $status;
    }

    /**
     * Get the raw command to execute, including arguments.
     *
     * @return string
     */
    public function getRaw(): string
    {
        $cmd = escapeshellcmd($this->command);
        foreach ($this->args as $name => $arg) {
            $arg = escapeshellarg((string) $arg);
            if (is_string($name)) {
                $arg = $name . '=' . $arg;
            }
            $cmd .= ' ' . $arg;
        }

        return $cmd;
    }

    /**
     * If stdout should be output.
     *
     * @param bool $echo_stdout
     *
     * @return Command
     */
    public function echoStdout(bool $echo_stdout = true): Command
    {
        $this->echo_stdout = $echo_stdout;

        return $this;
    }

    /**
     * If stderr should be output.
     *
     * @param bool $echo_stderr
     *
     * @return Command
     */
    public function echoStderr(bool $echo_stderr = true): Command
    {
        $this->echo_stderr = $echo_stderr;

        return $this;
    }

    /**
     * Throw exception on output error.
     *
     * @param bool $throw_on_error
     *
     * @return \NPM\Xec\Command
     */
    public function throwExceptionOnError(bool $throw_on_error = true): Command
    {
        $this->throw_on_error = $throw_on_error;

        return $this;
    }
}
