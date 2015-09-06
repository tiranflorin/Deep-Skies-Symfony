<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class SkylistEntryTest
 *
 * @package Dso\ObservationsLogBundle\Tests\Services
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class SkylistEntryTest extends WebTestCase
{

    /** @var  Container */
    protected $container;

    /**
     * Set up function
     */
    public function setUp()
    {
        $client = $this->createClient();
        $this->container = $client->getContainer();
    }
}
