<?php

class ProcessStartFailedException extends Exception
{
}


class Process
{
    private $command;
    private $process;
    private $pipes;
    private $isRunning = false;
    private array $fileHandles = [];

    public function __construct(string $command)
    {
        $this->command = $command;
        $this->setup();
    }

    public function setup()
    {

        $pipes = [
            1 => 'out',
            2 => 'err',
        ];

        $tmpDir = sys_get_temp_dir();
        $maxAttempts = 256; // Limit attempts to prevent infinite loops
        $lockHandles = [];
        $fileHandles = [];
        $files = [];

        for ($i = 0; $i < $maxAttempts; $i++) {
            foreach ($pipes as $pipeName => $fileExtension) {
                $file = sprintf('%s/sf_proc_%02X.%s', $tmpDir, $i, $fileExtension);
                $lockFile = $file . '.lock';

                // Try to open and lock the lock file
                $lockHandle = fopen($lockFile, 'w');
                if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
                    // If locking fails, continue to the next file
                    fclose($lockHandle);
                    continue 2;
                }

                // Release any previously held lock for this pipe
                if (isset($lockHandles[$pipeName])) {
                    flock($lockHandles[$pipeName], LOCK_UN);
                    fclose($lockHandles[$pipeName]);
                }

                $lockHandles[$pipeName] = $lockHandle;

                // Prepare the output file for this pipe
                if (!($fileHandle = fopen($file, 'w'))) {
                    // If file can't be opened, clean up and retry
                    flock($lockHandles[$pipeName], LOCK_UN);
                    fclose($lockHandles[$pipeName]);
                    unset($lockHandles[$pipeName]);
                    continue 2;
                }
                fclose($fileHandle); // Close write handle to prepare for reading
                $fileHandle = fopen($file, 'r'); // Reopen for reading

                // Store file handles and file paths
                $fileHandles[$pipeName] = $fileHandle;
                $files[$pipeName] = $file;
            }
            break;
        }

        // Ensure the loop terminated due to success, not maxAttempts
        if (count($fileHandles) !== count($pipes)) {
            throw new RuntimeException("Failed to initialize process pipes after $maxAttempts attempts.");
        }

        $this->fileHandles = $fileHandles;
//        $this->lockHandles = $lockHandles;
//        print_r($this->fileHandles);
    }


    public function start(?callable $callback = null, array $env = []): void
    {
        if ($this->isRunning) {
            throw new RuntimeException("The process is already running.");
        }

        // Prepare the environment variables
        $env = array_merge($_ENV, $env);

        $descriptorSpec =
            [
                ['pipe', 'r'],
                ['file', 'NUL', 'w'],
                ['file', 'NUL', 'w'],
            ];

        $this->process = proc_open($this->command, $descriptorSpec, $this->pipes, null, $env);

        if (!is_resource($this->process)) {
            throw new ProcessStartFailedException("Failed to start the process.");
        }

        $this->isRunning = true;

        // Asynchronously read from STDOUT and STDERR
        stream_set_blocking($this->pipes[0], false);
        stream_set_blocking($this->pipes[0], false);

        // Handle callback if provided
        if ($callback) {
            while (true) {
                $this->update();
                $stdout = stream_get_contents($this->pipes[0]);
                $stderr = stream_get_contents($this->pipes[0]);

                if ($stdout) {
                    $callback('out', $stdout);
                }

                if ($stderr) {
                    $callback('err', $stderr);
                }

                usleep(100000); // Prevent CPU spinning
            }
        }
    }

    /**
     * Checks if the process is still running.
     *
     * @return bool
     */
    private function isRunning(): bool
    {
        $status = proc_get_status($this->process);
        $this->isRunning = $status['running'];
        return $this->isRunning;
    }

    /**
     * Waits for the process to terminate.
     *
     * @return int The exit code of the process
     */
    public function wait(): int
    {
        if (!$this->process) {
            throw new RuntimeException("The process has not been started.");
        }

        $status = proc_get_status($this->process);
        while ($status['running']) {
            usleep(100000); // Prevent CPU spinning
            $status = proc_get_status($this->process);
        }

        $this->closePipes();
        return $status['exitcode'];
    }

    /**
     * Closes the pipes and the process.
     */
    private function closePipes(): void
    {
        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }

        proc_close($this->process);
        $this->isRunning = false;
    }

    private function update()
    {
        foreach ($this->fileHandles as $f) {
            $data = stream_get_contents($f,);
            print_r($data);
        }
    }
}


try {
    $command = PHP_BINARY . " -S localhost:5001 " . __DIR__ . "/s.php";
    $process = new Process($command);
    $process->start(function ($type, $output) {
        if ($type === 'out') {
            echo "STDOUT: $output";
        } else {
            echo "STDERR: $output";
        }
    });

    $exitCode = $process->wait();
    echo "Process exited with code $exitCode\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
