<?php

namespace Dso\PlannerBundle\Services;

use Doctrine\ORM\EntityManager;
use Dso\PlannerBundle\Event\DropTableEvent;
use Dso\UserBundle\Entity\ObservingSite;
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
    /** @var  EntityManager */
    protected $em;

    /** @var  EventDispatcher */
    protected $dispatcher;

    /** @var  CreateVisibleObjectsTable */
    protected $visibleObjectsService;

    public function updateUserLocation(Request $request, UserInterface $user)
    {
        $defaultTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $locationDetails = new ObservingSite();

        $locationDetails
            ->setUserId($user->getId())
            ->setName($request->request->get('name', 'Cluj-Napoca'))
            ->setLatitude($request->request->get('latitude', '43.234'))
            ->setLongitude($request->request->get('longitude', '22.234'))
            ->setTimeZone($request->request->get('timezone', 'UTC'))
            ->setDatetime($request->request->get('datetime', $defaultTime->format('Y-m-dH:i:s')));

        $this->em->persist($locationDetails);
        $this->em->flush();

        $username = $user->getUsername();
        $mySqlService = $this->visibleObjectsService->getMysqlService();
        $oldTableNames = $mySqlService->getConn()->fetchAll("SHOW TABLES LIKE '%temp__custom__$username%'");
        $this->visibleObjectsService->setConfigurationDetails($user->getUsername(), $locationDetails->getLatitude(), $locationDetails->getLongitude(), $locationDetails->getDateTime());
        $this->visibleObjectsService->executeFlow();

        if (!empty($oldTableNames)) {
            $this->dispatcher->dispatch(DropTableEvent::DROP_TABLE, new DropTableEvent($oldTableNames));
        }

        $this->dispatcher->dispatch(UpdateLocationSettingsEvent::UPDATE_LOCATION, new UpdateLocationSettingsEvent($locationDetails));
    }

    /**
     * @param EntityManager $em
     *
     * @return $this
     */
    public function setEm($em) {
        $this->em = $em;

        return $this;
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
