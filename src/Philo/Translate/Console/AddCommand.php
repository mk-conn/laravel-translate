<?php namespace Philo\Translate\Console;

use Illuminate\Console\Command;
use Philo\Translate\TranslateManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class AddCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translate:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new translation';

    /**
     * The translation manager
     *
     * @var Philo\Translate\TranslateManager
     */
    protected $manager;

    /**
     * Return example usages
     *
     * @var array
     */
    protected $examples = [];

    /**
     * Create a new command instance.
     *
     * @param TranslateManager $manager
     */
    public function __construct(TranslateManager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $bench = $this->input->getOption('bench');
        $htmlentities = !$this->input->getOption('no-entities');
        $group = $this->input->getArgument('group');
        $line = $this->input->getArgument('line');

        if ($bench) {
            $this->manager->workbench($bench);
        }

        foreach ($this->manager->getLanguages() as $language) {
            if (is_null($translation = $this->ask("Translate '$line' in " . strtoupper($language) . ": "))) {
                continue;
            }

            $this->manager->setLanguage($language)
                          ->addLine($group, $line, $translation, $htmlentities);
            $this->createExample($group, $line, $translation);
        }

        $this->info('Translation added!');
        $this->showExamples();
    }

    /**
     * Generate example for easy copy paste
     *
     * @param  string $file
     * @param  string $line
     * @param  string $translation
     *
     * @return void
     */
    protected function createExample($file, $line, $translation)
    {
        if ($variables = $this->manager->getTranslationVariables($translation)) {
            $variables = '"' . implode('" => "", "', $variables) . '" => ""';
            array_push($this->examples, "{{{ trans('$file.$line', [$variables]) }}}");
        } else {
            array_push($this->examples, "{{{ trans('$file.$line') }}}");
        }
    }

    /**
     * Return examples to the console
     *
     * @return void
     */
    protected function showExamples()
    {
        if (empty($this->examples)) {
            return;
        }

        foreach (array_unique($this->examples) as $e) {
            $this->comment($e);
        }

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['group', InputArgument::REQUIRED, 'Language group'],
            ['line', InputArgument::REQUIRED, 'Line name'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['bench', null, InputOption::VALUE_OPTIONAL, 'Run command in workbench'],
            ['no-entities', null, InputOption::VALUE_NONE, 'Add translation without converting characters to entities'],
        ];
    }

}
