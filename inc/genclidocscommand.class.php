<?php

use CJDevStudios\RSTGen\Components\Table\HeaderRow;
use CJDevStudios\RSTGen\Components\Table\Row;
use CJDevStudios\RSTGen\Components\Table\Table;
use Glpi\Console\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * @since 1.0.0
 */
final class PluginDevGenCLIDocsCommand extends AbstractCommand {

   protected function configure() {
      parent::configure();

      $this->setName('dev:docs:generate:cli');
      $this->setDescription(__('Generate documentation for CLI commands'));
      $this->addOption('namespace', 'ns', InputOption::VALUE_REQUIRED, 'Command namespace', 'glpi');
      $this->addOption('file', 'o', InputOption::VALUE_OPTIONAL, 'Output file', null);
   }

   protected function execute(InputInterface $input, OutputInterface $output) {
      // Require the autoloader
      require_once Plugin::getPhpDir('dev') . '/vendor/autoload.php';

      $cli = $this->getApplication();
      $commands = $cli->all($input->getOption('namespace'));

      usort($commands, static function ($a, $b) {
         return strcmp($a->getName(), $b->getName());
      });

      // Remove duplicate commands based on their name
      $commands = array_reduce($commands, static function ($carry, $command) {
         $name = $command->getName();
         if (!isset($carry[$name])) {
            $carry[$name] = $command;
         }
         return $carry;
      }, []);

      $o = '';

      $o .= "GLPI command-line interface\n";
      $o .= "===========================\n\n";
      $o .= "GLPI includes a CLI tool to help you to manage your GLPI instance.\n";
      $o .= "This interface is provided by the `bin/console` script which can be run from the root of your GLPI directory.\n\n";
      $o .= "Each command may have zero or more arguments or options.\n";
      $o .= "Arguments are positional pieces of information while options are not and are prefixed by one or two hyphens\n\n";

      foreach ($commands as $command) {
         $name = $command->getName();
         $description = $command->getDescription();
         $help = $command->getHelp();
         $aliases = $command->getAliases();
         $usages = $command->getUsages();

         $definition = $command->getDefinition();

         $name_length = strlen($name);

         $o .= $name."\n";
         $o .= str_repeat("-", $name_length)."\n\n";
         if (count($aliases)) {
            $o .= 'Aliases: `' . implode(', ', $aliases) . "`\n\n";
         } else {
            $o .= "Aliases: `None`\n\n";
         }

         $o .= "Description\n***********\n\n";
         $o .= $description."\n\n";

         $args = $definition->getArguments();
         $arg_count = count($args);
         $opts = $definition->getOptions();
         $opt_count = count($opts);

         if ($arg_count || $opt_count) {
            $o .= "Arguments/Options\n*****************\n\n";

            if ($arg_count) {
               $o .= "Arguments (in order):\n\n";

               $args_table = new Table();
               $args_table->addHeaderRow(new HeaderRow([
                  'name' => 'Name',
                  'description' => 'Description',
                  'required' => 'Required',
                  'default' => 'Default',
               ]));
               foreach ($args as $arg) {
                  $args_table->addBodyRow(new Row([
                     'name' => $arg->getName(),
                     'description' => $arg->getDescription(),
                     'required' => $arg->isRequired() ? 'Yes' : 'No',
                     'default' => $arg->getDefault(),
                  ]));
               }
               $o .= $args_table->render();
               $o .= "\n\n";
            } else {
               $o .= "There are no arguments for this command\n\n";
            }

            if ($opt_count) {
               $o .= "Options:\n\n";

               $opts_table = new Table();
               $opts_table->addHeaderRow(new HeaderRow([
                  'name' => 'Name',
                  'shortcut' => 'Shortcut',
                  'description' => 'Description',
                  'required' => 'Required',
                  'default' => 'Default',
                  'array' => 'Array',
                  'negatable' => 'Negatable',
               ]));
               foreach ($opts as $opt) {
                  $opt_name = $opt->getName();
                  if ($opt_name) {
                      $opt_name = '--' . $opt_name;
                  }
                   $opt_shortcut = $opt->getShortcut();
                   if ($opt_shortcut) {
                       $opt_shortcut = '-' . $opt_shortcut;
                   }
                  $opts_table->addBodyRow(new Row([
                     'name' => $opt_name,
                     'shortcut' => $opt_shortcut,
                     'description' => $opt->getDescription(),
                     'required' => $opt->isValueRequired() ? 'Yes' : 'No',
                     'default' => $opt->getDefault(),
                     'array' => $opt->isArray() ? 'Yes' : 'No',
                     'negatable' => $opt->isValueOptional() ? 'Yes' : 'No',
                  ]));
               }
               $o .= $opts_table->render();
               $o .= "\n\n";
            } else {
               $o .= "There are no options for this command\n\n";
            }
         } else {
            $o .= "\n\n";
         }

         if (!empty($help)) {
            $o .= "Help\n****\n\n";
            $o .= $help . "\n\n";
         }

         if (count($usages)) {
            $o .= "Usage\n*****\n\n";
            foreach ($usages as $usage) {
               $o .= ' - '.$usage . "\n";
            }
         }

         $o .= "\n";
      }

      if ($file = $input->getOption('file')) {
         $overwrite = false;

         if (file_exists($file)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Output file exists. Overwrite?', false);
            if ($helper->ask($input, $output, $question)) {
               $overwrite = true;
            } else {
               $output->writeln('Aborted');
               return Command::SUCCESS;
            }
         }
         if (file_put_contents($file, $o)) {
            $output->writeln('File ' . ($overwrite ? 'rewritten' : 'created') . ': ' . $file);
            return Command::SUCCESS;
         }
      } else {
         $output->writeln($o."\n\n");
         return Command::SUCCESS;
      }

      return Command::SUCCESS; // Success
   }
}
