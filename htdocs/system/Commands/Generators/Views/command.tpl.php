<@php

namespace {namespace};

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
<?php if ('generator' === $type) { ?>
use CodeIgniter\CLI\GeneratorTrait;
<?php } ?>

class {class} extends BaseCommand
{
<?php if ('generator' === $type) { ?>
    use GeneratorTrait;

<?php } ?>
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = '{group}';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = '{command}';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = '{command} [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
<?php if ('generator' === $type) { ?>
        $this->component = 'Command';
        $this->directory = 'Commands';
        $this->template  = 'command.tpl.php';

        $this->execute($params);
<?php } else { ?>
        //
<?php } ?>
    }
}
