<?php

class ProcessHandler
{
    private array $pipes = [];
    private $process = null;
    private array $fileHandles = [];
    private array $lockHandles = []; // Lock handles for synchronization
    private array $readBytes = [];   // Tracks the number of bytes read from each stream
    private const TIMEOUT_PRECISION = 0.1;
    private Closure $callback;

    public function __construct()
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
        $this->lockHandles = $lockHandles;
        print_r($this->fileHandles);
    }

    /**
     * Handles reading and optionally closing streams.
     */
    public function handleStreams(bool $blocking = false, bool $close = false): array
    {
        $this->unblock();
        $writableStreams = $this->write();
        $readData = [];
//        print_r($this->fileHandles);
        foreach ($this->fileHandles as $type => $fileHandle) {
//            print_r($this->fileHandles);
            $data = stream_get_contents($fileHandle, -1, 0);
            print_r($data);

            if ($close) {
                // Clean up resources if closing the streams
                ftruncate($fileHandle, 0);
                fclose($fileHandle);
                flock($this->lockHandles[$type], LOCK_UN);
                fclose($this->lockHandles[$type]);
                unset($this->fileHandles[$type], $this->lockHandles[$type], $this->readBytes[$type]);
            }
        }

        return $readData;
    }

    /**
     * Simulates unblocking the process (dummy implementation for now).
     */
    private function unblock(): void
    {
        foreach ($this->pipes as $pipe) {
            stream_set_blocking($pipe, 0);
        }
        echo "Process unblocked.\n";
    }

    /**
     * Simulates writing data to streams (dummy implementation for now).
     */
    private function write(): ?array
    {
        if (!isset($this->pipes[0])) {
            return null;
        }
        $stdin = $this->pipes[0];

        $w = [$stdin];
        if (false === @stream_select($r, $w, $e, 0, 0)) {

            return null;
        }
        foreach ($w  as $stdin){
            $written = fwrite($stdin, "");
        }
        return [];
    }

    public function isRunning()
    {
        $this->updateStatus();
    }

    public function start(callable $callback): bool
    {
        if ($this->isRunning()) {
            throw new RuntimeException("Running");
        }
        $this->callback = $callback;
        $command = PHP_BINARY . " -S localhost:5000 " . __DIR__ . "/s.php";
        $descriptorspec = [
            ['pipe', 'r'],
            ['file', 'NUL', 'w'],
            ['file', 'NUL', 'w'],
        ];

        $this->process = proc_open($command, $descriptorspec, $this->pipes);
print_r($this->pipes);
        if (is_resource($this->process)) {
//            foreach ([1, 2] as $type) { // Handle stdout and stderr
//                $this->fileHandles[$type] = $this->pipes[$type];
//                $this->readBytes[$type] = 0;
//            }
            return true;
        }

        echo "Failed to start process.\n";
        return false;
    }

    private function updateStatus()
    {
        $this->handleStreams();
    }

    public function update()
    {
        $this->updateStatus();
    }
}


$handler = new ProcessHandler();

$handler->start(function ($ty, $b) {
//    print_r($b);
});
while (true) {
    $handler->update();
    usleep(500000);
}