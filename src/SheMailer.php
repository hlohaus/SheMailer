<?php declare(strict_types=1);

namespace She\Mailer;

use She\Mailer\Components\ConfigListener;
use She\Mailer\Components\RedirectingListener;
use Shopware\Core\Framework\Plugin;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SheMailer extends Plugin implements CompilerPassInterface
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass($this);
    }

    public function process(ContainerBuilder $container)
    {
        if ($container->has('core_mailer')) {
            $container->getDefinition('core_mailer')->addMethodCall('registerPlugin', [
                new Reference(ConfigListener::class)
            ])->addMethodCall('registerPlugin', [
                new Reference(RedirectingListener::class)
            ]);
        }
    }
}
