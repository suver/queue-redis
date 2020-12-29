<?php
class Producer {
    protected $redis;

    public function __construct() {
        sleep(5);
        $this->redis = new Redis();
        $this->redis->connect(
            'redis',
            6379
        );
    }

    public function __destruct() {
        $this->redis->close();
    }

    protected function push($stream, $message) {
        return $this->redis->xAdd($stream, '*', $message);
    }

    public function run() {
        for ($user_id=1; $user_id <= 1000; $user_id++) {
            for ($command_index=1; $command_index <= 10; $command_index++) {
                try {
                    print 'Send push: ' . "command-{$user_id}-{$command_index} = ";
                    print $this->push('event-user', ['message' => "command-{$user_id}-{$command_index}"]) . "\n";
                } catch (\Exception $e) {
                    var_dump($e);
                }
            }
        }
    }
}

$p = new Producer();
$p->run();