<?php

namespace Dso\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class User
 *
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 *
 * @package Dso\UserBundle\Entity
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
}
