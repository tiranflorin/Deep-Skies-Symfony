<?php

namespace Dso\PlannerBundle\Services;

use Dso\PlannerBundle\Event\DropTableEvent;
use Dso\UserBundle\Entity\LocationDetails;
use Dso\UserBundle\Event\UpdateLocationSettingsEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SettingsManager
 *
 * @package Dso\PlannerBundle\Services
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class SettingsManager
{
    /** @var  EventDispatcher */
    protected $dispatcher;

    /** @var  CreateVisibleObjectsTable */
    protected $visibleObjectsService;

    public function updateUserLocation(Request $request, UserInterface $user)
    {
        $defaultTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $locationDetails = new LocationDetails();

        $locationDetails
            ->setEmail($user->getEmail())
            ->setLatitude($request->request->get('latitude', '43.234'))
            ->setLongitude($request->request->get('longitude', '22.234'))
            ->setTimeZone($request->request->get('timezone', 'UTC'))
            ->setDatetime($request->request->get('datetime', $defaultTime->format('Y-m-dH:i:s')));

        $this->dispatcher->dispatch(UpdateLocationSettingsEvent::UPDATE_LOCATION, new UpdateLocationSettingsEvent($locationDetails));
        $this->dispatcher->dispatch(UpdateLocationSettingsEvent::UPDATE_TIME, new UpdateLocationSettingsEvent($locationDetails));

        $username = $user->getUsername();
        $mySqlService = $this->visibleObjectsService->getMysqlService();
        $oldTableNames = $mySqlService->getConn()->fetchAll("SHOW TABLES LIKE '%temp__custom__$username%'");
        $this->visibleObjectsService->setConfigurationDetails($user->getUsername(), $locationDetails->getLatitude(), $locationDetails->getLongitude(), $locationDetails->getDateTime());
        $this->visibleObjectsService->executeFlow();

        if (!empty($oldTableNames)) {
            $this->dispatcher->dispatch(DropTableEvent::DROP_TABLE, new DropTableEvent($oldTableNames));
        }
    }

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param \Dso\PlannerBundle\Services\CreateVisibleObjectsTable $visibleObjectsService
     */
    public function setVisibleObjectsService($visibleObjectsService)
    {
        $this->visibleObjectsService = $visibleObjectsService;
    }
}
