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
        $locationDetails = $this->retrieveObservingSite($request, $user);
        $this->em->persist($locationDetails);
        $this->em->flush();

        $username = strtolower($user->getUsername());
        $username = str_replace(' ', '', $username);
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

    public function retrieveObservingSite(Request $request, UserInterface $user) {
        $name = $request->request->get('name');
        $tz = $request->request->get('timezone');
        $lat = $request->request->get('latitude');
        $long = $request->request->get('longitude');
        $dateTime = $request->request->get('datetime');
        $observingSite = new ObservingSite();
        $defaultTime = new \DateTime('now', new \DateTimeZone('UTC'));

        // Prepare the simplest flow - new clean observing site
        $observingSite
            ->setUserId($user->getId())
            ->setName($request->request->get('name', 'Cluj-Napoca'))
            ->setLatitude($request->request->get('latitude', '43.234'))
            ->setLongitude($request->request->get('longitude', '22.234'))
            ->setTimeZone($request->request->get('timezone', 'UTC'))
            ->setDatetime($request->request->get('datetime', $defaultTime->format('Y-m-dH:i:s'))
            );

        // Update the site if we already found it in the db.
        if (null !==  $user->getCurrentObservingSiteId()) {
            $currentObservingSite = $this->em->find('Dso\UserBundle\Entity\ObservingSite', $user->getCurrentObservingSiteId());
            if (!$currentObservingSite instanceof ObservingSite) {
                return $observingSite;
            }

            $observingSite = $currentObservingSite;
            if ($name != $observingSite->getName()) {
                $observingSite->setName($name);
            }
            if ($lat != $observingSite->getLatitude()) {
                $observingSite->setLatitude($lat);
            }
            if ($long != $observingSite->getLongitude()) {
                $observingSite->setLongitude($long);
            }
            if ($tz != $observingSite->getTimeZone()) {
                $observingSite->setTimeZone($tz);
            }
            if ($dateTime != $observingSite->getDateTime()) {
                $observingSite->setDateTime($dateTime);
            }
        }

        return $observingSite;
    }
}
