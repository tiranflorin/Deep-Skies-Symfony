<?php

namespace Dso\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DsoUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
