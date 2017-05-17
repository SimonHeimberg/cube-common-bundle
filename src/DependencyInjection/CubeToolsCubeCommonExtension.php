<?php

namespace CubeTools\CubeCommonBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CubeToolsCubeCommonExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['FOSUserBundle']) && isset($bundles['DoctrineBundle'])) {
            $fosUserConfigs = $container->getExtensionConfig('fos_user');
            $userClass = null;
            foreach ($fosUserConfigs as $config) { // read user_class from fos_user
                if (isset($config['user_class'])) {
                    $userClass = $config['user_class'];
                }
            }
            if (null !== $userClass) { // then write user class
                $interface = 'Symfony\Component\Security\Core\User\UserInterface';
                $config = array('orm' => array('resolve_target_entities' => array($interface => $userClass)));
                $container->prependExtensionConfig('doctrine', $config);
            }
        }
    }
}
