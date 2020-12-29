<?php
class Consumer {
    protected $redis;
    protected $name;

    public function __construct() {
        sleep(2);
        $this->name = $_ENV['HOSTNAME'] ?? 'def' . time();
        print('Consumer name: '. $this->name . "\n");
        $this->redis = new Redis();
        $this->redis->connect(
            'redis',
            6379
        );
        try {
            print $this->redis->xGroup('CREATE', 'event-user', 'group-consumer', 0) . "\n";
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    public function __destruct() {
        $this->redis->close();
    }

    protected function read($stream, $count=1) {
        return $this->redis->xReadGroup('group-consumer', $this->name, [$stream => '>'], $count);
    }

    protected function completed($stream, $id) {
        return $this->redis->xAck($stream, 'group-consumer', $id);
    }

    public function run() {
        while (True) {
            $payload = $this->read('event-user');
            print 'Read payload: ' . var_export($payload, true). "\n";

            if (!$payload) {
                sleep(1);
                continue;
            }

            foreach ($payload as $stream_entries) {
                if ($stream_entries) {
                    foreach ($stream_entries as $id => $message) {
                        print 'competed! ' . $id . ':' . var_export($message, true) . "\n";
                        $this->completed('event-user', [$id]);
                    }
                }
            }
            sleep(1);
        }
    }

}

$p = new Consumer();
$p->run();