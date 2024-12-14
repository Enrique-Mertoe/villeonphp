<?php
/**
 * Process.php
 * @package    Villeon\Manager\Process
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */

namespace Villeon\Manager\Process;

use Closure;

/**
 *
 */
class Process
{
    /**
     * @var string $command
     */
    private string $command;

    /**
     * @var array $eventsHandlers ;
     */
    private array $eventsHandlers = [];
    /**
     * @var resource|null
     */
    private $process;
    /**
     * @var array $files
     */
    private array $files;
    /**
     * @var array $status
     */
    private array $status;
    /**
     * @var array $pipes
     */
    private array $pipes = [];

    /**
     * @var WindowPipes|UnixPipes $pipesHandler
     */
    private WindowPipes|UnixPipes $pipesHandler;

    /**
     *
     */
    public const ERR = "err";
    /**
     *
     */
    public const OUT = "out";

    /**
     * @param string $command
     */
    public function __construct(string $command)
    {
        $this->command = $command;

        if ('\\' == DIRECTORY_SEPARATOR) {
            $this->pipesHandler = WindowPipes::instance();
        } else {
            $this->pipesHandler = UnixPipes::instance();
        }

    }

    /**
     * @return int|mixed
     */
    public function isActive(): mixed
    {
        $this->update();
        return $this->status["running"] ?? 0;
    }

    /**
     * @return void
     */
    public function start(): void
    {
        $this->initiate();
        while ($this->isActive()) {
            usleep(500000);
        }
        $this->stop();
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        if (is_resource($this->process))
            print_r("process ended with " . proc_close($this->process));
        foreach ($this->pipesHandler->pipes as $pipe) {
            if (is_resource($pipe))
                fclose($pipe);
        }
    }

    /**
     * @param $stdin
     * @return true|void
     */
    private function checkTrapped($stdin)
    {

        $read = [$stdin];
        $write = $except = null;
        $changed = stream_select($read, $write, $except, 0, 100000);
        if ($changed > 0) {
            print_r("oop");
            $input = fgets($stdin);
            if (trim($input) === "") {
                echo "Ctrl+C detected. Stopping process.\n";
                return true;
            }
        }
    }

    /**
     * @return void
     */
    private function update(): void
    {
        $this->status = proc_get_status($this->process);
        $result = $this->pipesHandler->read_pipes();

        $pipes = [1 => Process::OUT, 2 => Process::ERR];
        foreach ($result as $type => $res) {
            if ($res) {
                $res = explode("\n", $res);
                $this->handleEvent("update", 0, $res);
            }
        }
    }

    /**
     * Registers an event handler for a specific event in the process lifecycle.
     *
     * <pre>
     * $process->on("update", function(string $type, array $data) {
     *     // Handle the event here.
     * });
     * </pre>
     *
     * @param string $event The name of the event to listen for. Possible values are:
     * <table>
     *     <tr valign="top">
     *         <th width=50>Event_Name</th>
     *         <th>Description</th>
     *     </tr>
     *     <tr>
     *         <td cols-span=4><code>update</code></td>
     *         <td>Triggered when the process outputs incremental updates. This typically represents partial outputs or intermediate results.
     *         The callback receives:
     *         <ul>
     *             <li><code>string $type</code>: The type of output (e.g., "stdout" or "stderr").</li>
     *             <li><code>array $data</code>: An array of output lines or messages from the process.</li>
     *         </ul>
     *         </td>
     *     </tr>
     *     <tr>
     *         <td><code>start</code></td>
     *         <td>Triggered when the process starts. No parameters are passed to the callback.</td>
     *     </tr>
     *     <tr>
     *         <td><code>exit</code></td>
     *         <td>Triggered when the process exits. The callback receives:
     *         <ul>
     *             <li><code>int $exitCode</code>: The exit code returned by the process.</li>
     *         </ul>
     *         </td>
     *     </tr>
     *     <tr>
     *         <td><code>error</code></td>
     *         <td>Triggered when an error occurs during the process execution. The callback receives:
     *         <ul>
     *             <li><code>string $errorMessage</code>: A description of the error.</li>
     *         </ul>
     *         </td>
     *     </tr>
     * </table>
     *
     * @param Closure $controller A callback function to handle the event. The function signature depends on the event type:
     * <ul>
     *     <li>For <code>update</code>: <code>function(string $type, array $data): void</code></li>
     *     <li>For <code>start</code>: <code>function(): void</code></li>
     *     <li>For <code>exit</code>: <code>function(int $exitCode): void</code></li>
     *     <li>For <code>error</code>: <code>function(string $errorMessage): void</code></li>
     * </ul>
     *
     * @return void
     */

    public function on(string $event, Closure $controller): void
    {
        $this->eventsHandlers[$event][] = $controller;
    }

    /**
     * @param string $name
     * @param ...$args
     * @return void
     */
    private function handleEvent(string $name, ...$args): void
    {
        foreach ($this->eventsHandlers[$name] ?? [] as $event) {
            $event(...$args);
        }
    }

    /**
     * @return void
     */
    private function initiate(): void
    {
        if ($this->process)
            return;
        $descriptors = $this->descriptors();
        $command = $this->pipesHandler->prepareCommand($this->command);
        $this->process = proc_open($command, $descriptors, $this->pipesHandler->pipes);
    }

    /**
     * @return array
     */
    private function descriptors(): array
    {
        return $this->pipesHandler->getDescriptors();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->stop();
    }


}
