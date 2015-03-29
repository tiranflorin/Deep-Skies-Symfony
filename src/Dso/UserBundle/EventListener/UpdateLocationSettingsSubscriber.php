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
            'update.time'     => array('onUpdateTime', 2)
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
        $user = $this->userManager->findUserByEmail($locationDetails->getEmail());
        $user->setLatitude($locationDetails->getLatitude())
            ->setLongitude($locationDetails->getLongitude());
        $this->userManager->updateUser($user);
    }

    /**
     * Set time details for the user.
     *
     * @param UpdateLocationSettingsEvent $event
     */
    public function onUpdateTime(UpdateLocationSettingsEvent $event)
    {
        $locationDetails = $event->getLocationDetails();
        /** @var User $user */
        $user = $this->userManager->findUserByEmail($locationDetails->getEmail());
        $user->setTimeZone($locationDetails->getTimeZone())
            ->setDatetime($locationDetails->getDateTime());
        $this->userManager->updateUser($user);
    }
}
