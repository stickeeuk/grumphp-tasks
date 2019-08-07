<?php

namespace Stickee\GrumPHP;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Yarn task
 */
class YarnTask extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'yarn';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'script' => null,
            'working_directory' => './',
        ]);

        $resolver->addAllowedTypes('script', ['string']);
        $resolver->addAllowedTypes('working_directory', ['string']);

        return $resolver;
    }

    /**
     * @param ContextInterface $context
     * @return bool
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * @param ContextInterface $context
     * @return \GrumPHP\Runner\TaskResultInterface|TaskResult
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();
        $arguments = $this->processBuilder->createArgumentsForCommand('yarn');
        $arguments->addRequiredArgument('%s', $config['script']);
        $process = $this->processBuilder->buildProcess($arguments);
        $process->setWorkingDirectory(realpath($config['working_directory']));
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
