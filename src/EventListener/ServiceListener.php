<?php

namespace App\EventListener;

use App\Entity\Service;
use App\Service\MessageBusService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class ServiceListener
{
    public function __construct(
        private MessageBusService $bus,
        private SluggerInterface $slugger,
    ) {
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $entity = $prePersistEventArgs->getObject();
        if (!$entity instanceof Service) {
            return;
        }

        $entity->setSlug($this->slugger->slug($entity->getName()));
    }

    public function preUpdate(PreUpdateEventArgs $preUpdateEventArgs): void
    {
        $entity = $preUpdateEventArgs->getObject();
        if (!$entity instanceof Service) {
            return;
        }

        $entity->setSlug($this->slugger->slug($entity->getName()));
    }
}
