<?php

namespace Webman\RedisQueue\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Webman\Console\Util;


class MakeConsumerCommand extends Command
{
    protected static $defaultName = 'redis:consumer';
    protected static $defaultDescription = 'Make redis-queue consumer';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Consumer name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $output->writeln("Make consumer $name");

        $class = Util::nameToClass($name);
        $queue = Util::classToName($name);

        $file = app_path() . "/queue/redis/$class.php";
        $this->createConsumer($class, $queue, $file);

        return self::SUCCESS;
    }

    /**
     * @param $class
     * @param $queue
     * @param $file
     * @return void
     */
    protected function createConsumer($class, $queue, $file)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $controller_content = <<<EOF
<?php

namespace app\\queue\\redis;

use Webman\\RedisQueue\\Consumer;

class $class implements Consumer
{
    // 要消费的队列名
    public \$queue = '$queue';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public \$connection = 'default';

    // 消费
    public function consume(\$data)
    {
        // 无需反序列化
        var_export(\$data);
    }
}

EOF;
        file_put_contents($file, $controller_content);
    }

}
