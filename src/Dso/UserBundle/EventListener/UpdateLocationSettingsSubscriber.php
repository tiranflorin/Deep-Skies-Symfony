<?php

namespace Dso\UserBundle\EventListener;

use Dso\UserBundle\Entity\User;
use Dso\UserBundle\Event\UpdateLocationSettingsEvent;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UpdateLocationSettingsSubscriber
 *
 * @package Dso\UserBundle\EventListener
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class UpdateLocationSettingsSubscriber implements EventSubscriberInterface
{
    /** @var  UserManager */
    private $userManager;

    public static function getSubscribedEvents()
    {
        return array(
            'update.location' => array('onUpdateLocation', 1),
        );
    }

    /**
     * @param $userManager
     *
     * @return $this
     */
    public function setUserManager($userManager)
    {
        $this->userManager = $userManager;

        return $this;
    }

    /**
     * Set location details for the user.
     *
     * @param UpdateLocationSettingsEvent $event
     */
    public function onUpdateLocation(UpdateLocationSettingsEvent $event)
    {
        $locationDetails = $event->getLocationDetails();
        /** @var User $user */
        $user = $this->userManager->findUserBy(array('id' => $locationDetails->getUserId()));
        $user->setCurrentObservingSiteId($locationDetails->getId());
        $this->userManager->updateUser($user);
    }
}
