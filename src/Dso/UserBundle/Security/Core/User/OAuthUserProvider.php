<?php
namespace Dso\UserBundle\Security\Core\User;

use Dso\UserBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OAuthUserProvider
 * @package AppBundle\Security\Core\User
 */
class OAuthUserProvider extends BaseClass
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $socialID = $response->getUsername();
        /** @var User $user */
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $socialID));
        $email = $response->getEmail();
        //check if the user already has the corresponding social account
        if (null === $user) {
            //check if the user has a normal account
            $user = $this->userManager->findUserByEmail($email);

            if (null === $user || !$user instanceof UserInterface) {
                //if the user does not have a normal account, set it up:
                $user = $this->userManager->createUser();
                $user->setUsername($email);
                $user->setEmail($email);
                $user->setPlainPassword(md5(uniqid()));
                $user->setEnabled(true);
            }
            //then set its corresponding social id
            $service = $response->getResourceOwner()->getName();
            switch ($service) {
                case 'facebook':
                    $user->setFacebookID($socialID);
                    break;
            }
            $this->userManager->updateUser($user);
        } else {
            //and then login the user
            $checker = new UserChecker();
            $checker->checkPreAuth($user);
        }

        return $user;
    }
}
