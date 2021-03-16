<?php

declare(strict_types=1);

namespace App\Support;

use EightPoints\Bundle\GuzzleBundle\PluginInterface;
use GuzzleRetry\GuzzleRetryMiddleware;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GuzzleRetryPlugin extends Bundle implements PluginInterface
{
    /**
     * The name of this plugin. It will be used as the configuration key.
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'retry';
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $pluginNode
     *
     * @return void
     */
    public function addConfiguration(ArrayNodeDefinition $pluginNode): void
    {
        $pluginNode
            ->canBeEnabled()
            ->children()
            ->scalarNode('retry_enabled')
            ->defaultFalse()
            ->end()
            ->scalarNode('max_retry_attempts')
            ->defaultValue(10)
            ->end()
            ->scalarNode('retry_only_if_retry_after_header')
            ->defaultFalse()
            ->end()
            ->arrayNode('retry_on_status')
            ->defaultValue([503, 429])
            ->scalarPrototype()
            ->end()
            ->end()
            ->scalarNode('default_retry_multiplier')
            ->defaultValue(1.5)
            ->end()
            ->scalarNode('retry_on_timeout')
            ->defaultFalse()
            ->end()
            ->scalarNode('expose_retry_header')
            ->defaultFalse()
            ->end()
            ->scalarNode('retry_header')
            ->defaultValue('X-Retry-Counter')
            ->end()
            ->end();
    }

    /**
     * Load this plugin: define services, load service definition files, etc.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    /**
     * Add configuration nodes for this plugin to the provided node.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param string           $clientName
     * @param Definition       $handler
     *
     * @return void
     */
    public function loadForClient(
        array $config,
        ContainerBuilder $container,
        string $clientName,
        Definition $handler
    ): void {
        if (!$config['retry_enabled']) {
            return;
        }

        $middleware = new Definition(GuzzleRetryMiddleware::class);
        $middleware->setFactory([GuzzleRetryMiddleware::class, 'factory']);
        $middleware->setArguments([
            [
                'max_retry_attempts' => $config['max_retry_attempts'],
                'retry_only_if_retry_after_header' => $config['retry_only_if_retry_after_header'],
                'retry_on_status' => $config['retry_on_status'],
                'default_retry_multiplier' => $config['default_retry_multiplier'],
                'retry_on_timeout' => $config['retry_on_timeout'],
                'expose_retry_header' => $config['expose_retry_header'],
                'retry_header' => $config['retry_header'],
            ],
        ]);

        $middleware->setPublic(true);

        $middlewareServiceName = sprintf('guzzle_retry_plugin.middleware.retry.%s', $clientName);
        $container->setDefinition($middlewareServiceName, $middleware);

        $middlewareExpression = new Expression(sprintf('service("%s")', $middlewareServiceName));
        $handler->addMethodCall('push', [$middlewareExpression]);
    }
}
