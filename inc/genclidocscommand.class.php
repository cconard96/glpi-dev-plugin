<?php

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
      $cli = $this->getApplication();
      $commands = $cli->all($input->getOption('namespace'));

      usort($commands, static function ($a, $b) {
         return strcmp($a->getName(), $b->getName());
      });

      ob_start();

      echo "GLPI command-line interface\n";
      echo "===========================\n\n";
      echo "GLPI includes a CLI tool to help you to manage your GLPI instance.\n";
      echo "This interface is provided by the `bin/console` script which can be run from the root of your GLPI directory.\n\n";
      echo "Each command may have zero or more arguments or options.\n";
      echo "Arguments are positional pieces of information while options are not and are prefixed by one or two hyphens\n\n";

      foreach ($commands as $command) {
         $name = $command->getName();
         $description = $command->getDescription();
         $help = $command->getHelp();
         $aliases = $command->getAliases();
         $usages = $command->getUsages();

         $definition = $command->getDefinition();

         $name_length = strlen($name);

         echo $name."\n";
         echo str_repeat("-", $name_length)."\n\n";
         if (count($aliases)) {
            echo 'Aliases: `' . implode(', ', $aliases) . "`\n\n";
         } else {
            echo "Aliases: `None`\n\n";
         }

         echo "Description\n***********\n\n";
         echo $description."\n\n";

         $args = $definition->getArguments();
         $arg_count = count($args);
         $opts = $definition->getOptions();
         $opt_count = count($opts);

         if ($arg_count || $opt_count) {
            echo "Arguments/Options\n****************\n\n";

            if ($arg_count) {
               echo "Arguments (in order):\n\n";
               foreach ($args as $arg) {
                  $arg_name = $arg->getName();
                  $arg_description = $arg->getDescription();
                  $arg_default = $arg->getDefault();
                  $arg_is_required = $arg->isRequired();

                  echo "- ``$arg_name`` " . ($arg_is_required ? '(required) ' : '') . "- $arg_description (" . ($arg_default ? 'default = '.$arg_default : 'no default') .")\n";
               }
               echo "\n";
            } else {
               echo "There are no arguments for this command\n\n";
            }

            if ($opt_count) {
               echo "Options:\n\n";
               foreach ($opts as $opt) {
                  $opt_shortcut = $opt->getShortcut();
                  $opt_name = $opt->getName();
                  $opt_description = $opt->getDescription();
                  $opt_default = $opt->getDefault();
                  $opt_is_required = $opt->isValueRequired();

                  $opt_names = (!empty($opt_shortcut) ? "``-$opt_shortcut``, " : '')."``--$opt_name``";
                  echo "- $opt_names " . ($opt_is_required ? '(required) ' : '') . "- $opt_description (" . ($opt_default ? 'default = '.$opt_default : 'no default') .")\n";
               }
               echo "\n";
            } else {
               echo "There are no options for this command\n\n";
            }
         } else {
            echo "\n\n";
         }

         if (!empty($help)) {
            echo "Help\n****\n\n";
            echo $help . "\n\n";
         }

         if (count($usages)) {
            echo "Usage\n*****\n\n";
            foreach ($usages as $usage) {
               echo ' - '.$usage . "\n";
            }
         }

         echo "\n";
      }

      $o = ob_get_clean();

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
      }

      return Command::SUCCESS; // Success
   }
}
